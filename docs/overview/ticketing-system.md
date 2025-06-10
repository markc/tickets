### **Prompt for AI Code Generation**

**Project:** Build a Laravel 12 Customer Support System (TIKM) with a Filament 3.3 Admin Panel.

**Objective:** You are an expert Laravel and Filament developer. Your task is to create a complete ticketing support system inspired by the features of the `lara-zeus/thunder` Filament plugin. The application will have a customer-facing frontend for users to submit and manage their tickets, and a comprehensive Filament-based admin panel for support agents and administrators to manage the system.

### **1. Core Concepts & Philosophy**

The system is organized around "Offices," which function as departments or support teams (e.g., "Sales," "Technical Support").

*   **Users:** Can be Customers, Agents, or Admins.
*   **Customers:** Submit tickets to a specific Office. They interact with the system through a simple, non-Filament frontend.
*   **Agents:** Are assigned to one or more Offices. They manage and respond to tickets within their assigned Offices via the Filament admin panel.
*   **Admins:** Have full control over the system, including managing users, offices, and all tickets, via the Filament admin panel.

### **2. Database Schema & Eloquent Models**

Please generate the necessary migrations and Eloquent models with the following relationships:

*   **`User` Model (standard Laravel `users` table):**
    *   Add a `role` column (string or enum: `admin`, `agent`, `customer`).
    *   A User can be the `creator` of many Tickets.
    *   A User (Agent) can be `assigned_to` many Tickets.
    *   A User (Agent) can belong to many `Offices` (many-to-many).

*   **`Office` Model:**
    *   Fields: `name` (string), `description` (text), `is_internal` (boolean, default: false).
    *   An Office can have many `Users` (Agents) assigned to it (many-to-many).
    *   An Office can have many `Tickets`.

*   **`Ticket` Model:**
    *   Fields: `uuid` (for public reference), `subject` (string), `content` (longText).
    *   Relationships:
        *   `belongsTo(User::class, 'creator_id')`
        *   `belongsTo(User::class, 'assigned_to_id')->nullable()`
        *   `belongsTo(Office::class)`
        *   `belongsTo(TicketStatus::class)`
        *   `belongsTo(TicketPriority::class)`
        *   `hasMany(TicketReply::class)`
        *   `hasMany(Attachment::class)` (polymorphic)
        *   `hasMany(TicketTimeline::class)`

*   **`TicketStatus` Model:**
    *   Fields: `name` (string), `color` (string, for UI display, e.g., a hex code or Tailwind color).
    *   Seed with defaults: `Open` (blue), `In Progress` (orange), `On Hold` (yellow), `Closed` (green).

*   **`TicketPriority` Model:**
    *   Fields: `name` (string), `color` (string).
    *   Seed with defaults: `Low` (gray), `Medium` (blue), `High` (red), `Urgent` (purple).

*   **`TicketReply` Model:**
    *   Fields: `content` (longText), `is_internal_note` (boolean, default: false).
    *   Relationships:
        *   `belongsTo(User::class)`
        *   `belongsTo(Ticket::class)`
        *   `hasMany(Attachment::class)` (polymorphic)

*   **`Attachment` Model:**
    *   Fields: `filename` (string), `path` (string), `mime_type` (string).
    *   Polymorphic relationship: `attachable`.

*   **`TicketTimeline` Model:**
    *   Fields: `entry` (text). This will store human-readable logs.
    *   Relationships: `belongsTo(Ticket::class)`, `belongsTo(User::class, 'user_id')->nullable()` (for the user who performed the action).

### **3. Filament Admin Panel (Backend)**

Please create the following Filament Resources and a Dashboard.

*   **Dashboard (`App\Filament\Pages\Dashboard`):**
    *   Create statistical widgets:
        *   `Tickets by Status`: A chart showing counts for each status.
        *   `Tickets by Priority`: A chart showing counts for each priority.
        *   `My Assigned Tickets`: A simple table widget showing tickets assigned to the logged-in agent.
        *   `Overall Ticket Count`: A stat widget showing the total number of open tickets.

*   **`TicketResource`:**
    *   **Table:**
        *   Display columns: `uuid`, `subject`, `office`, `creator`, `assigned_to`, `priority`, `status`, `updated_at`.
        *   Implement advanced filtering for `status`, `priority`, `office`, and `assigned_to`.
        *   Add bulk actions to change status or assign tickets.
    *   **View/Edit Page:**
        *   Use an `Infolist` to display the main ticket details (`subject`, `content`, creator info).
        *   Show a timeline of all replies and timeline entries, ordered chronologically.
        *   Include a form to add a new reply. This form should have a toggle for "Internal Note".
        *   Display all attachments with download links.
        *   In the sidebar or as header actions, allow an agent to:
            *   Change the `TicketStatus`.
            *   Change the `TicketPriority`.
            *   Assign the ticket to another agent within the same `Office`.
            *   Transfer the ticket to a different `Office`.

*   **`OfficeResource`:**
    *   Standard CRUD for managing Offices.
    *   On the edit page, include a `CheckboxList` or `Select` with `relationship` to manage which agents (users with the 'agent' role) are assigned to that office.

*   **`UserResource`:**
    *   Standard CRUD for managing Users.
    *   Allow admins to assign the `role` for each user.

*   **`Settings` Section:**
    *   Create a new navigation group called "Settings".
    *   Inside, create `TicketStatusResource` and `TicketPriorityResource` for full CRUD management of these lookup values.

### **4. Customer Frontend (Non-Filament)**

Use standard Blade views with a simple CSS framework like Tailwind CSS.

*   **Authentication:** Use `laravel/breeze` for user registration and login.
*   **Routes:** Create web routes protected by `auth` middleware.
*   **Create Ticket Page:**
    *   A form with fields: `Subject`, `Office` (a dropdown populated from the `Office` model), `Priority` (dropdown), and `Content` (textarea), and a file input for attachments.
*   **My Tickets Page:**
    *   A table listing all tickets created by the logged-in user.
    *   Columns: `ID`, `Subject`, `Status`, `Last Updated`.
    *   Each row should link to the `View Ticket Page`.
*   **View Ticket Page:**
    *   Displays the original ticket message and all subsequent replies (excluding internal notes).
    *   Shows the ticket's current status and priority.
    *   Includes a form for the user to add a new reply and upload attachments.

### **5. Core Functionality & Business Logic**

*   **Ticket Creation:** When a user submits a ticket, an initial `TicketTimeline` entry should be created: "Ticket created by [User Name]".
*   **Automatic Assignment:** (Optional, but good for a complete system) When a ticket is created, automatically assign it to an agent in the selected Office using a simple round-robin logic.
*   **Timeline Logging:** Create a `TicketTimeline` entry for every significant event:
    *   Reply added (distinguish between public and internal).
    *   Status changed from X to Y.
    *   Priority changed from X to Y.
    *   Ticket assigned to [Agent Name].
    *   Ticket transferred to [Office Name].
*   **Email Notifications:**
    *   **To Customer:** When a ticket is created, and when an agent posts a public reply.
    *   **To Agent:** When a ticket is assigned to them, and when a customer replies to an assigned ticket.
    *   **To Office/Admins:** When a new ticket is created in their office without an assignment.

Please generate all necessary migrations, models, Filament resources, controllers, routes, and Blade views to build this application. Ensure the code is clean, well-commented, and follows Laravel best practices.
