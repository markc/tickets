<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CannedResponseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = \App\Models\User::where('email', 'admin@example.com')->first();
        if (! $admin) {
            $admin = \App\Models\User::where('role', 'admin')->first();
        }

        if (! $admin) {
            $this->command->warn('No admin user found, skipping canned response seeder');

            return;
        }

        $responses = [
            [
                'title' => 'Welcome & Acknowledgment',
                'category' => 'General',
                'content' => "Hello {{customer_name}},\n\nThank you for contacting {{company_name}} support. I've received your ticket regarding \"{{ticket_subject}}\" and I'm here to help.\n\nI'll review your request and get back to you as soon as possible. If you have any additional information that might help resolve this issue faster, please don't hesitate to share it.\n\nBest regards,\n{{agent_name}}",
                'is_public' => true,
            ],
            [
                'title' => 'Request for More Information',
                'category' => 'General',
                'content' => "Hi {{customer_name}},\n\nThank you for reaching out. To help me better understand and resolve your issue, could you please provide the following information:\n\n• [Specific details needed]\n• [Steps you've already tried]\n• [Any error messages you've seen]\n\nOnce I have this information, I'll be able to provide you with a more targeted solution.\n\nThank you for your patience.\n\nBest regards,\n{{agent_name}}",
                'is_public' => true,
            ],
            [
                'title' => 'Technical Issue - Initial Response',
                'category' => 'Technical',
                'content' => "Hello {{customer_name}},\n\nI understand you're experiencing a technical issue with [specific system/feature]. I'm going to investigate this right away.\n\nIn the meantime, here are some quick troubleshooting steps you can try:\n\n1. [Step 1]\n2. [Step 2]\n3. [Step 3]\n\nIf these steps don't resolve the issue, please let me know and I'll escalate this to our technical team for further investigation.\n\nBest regards,\n{{agent_name}}",
                'is_public' => true,
            ],
            [
                'title' => 'Password Reset Instructions',
                'category' => 'Account',
                'content' => "Hi {{customer_name}},\n\nI can help you reset your password. Here's how to do it:\n\n1. Go to the login page\n2. Click \"Forgot Password?\"\n3. Enter your email address ({{customer_email}})\n4. Check your email for the reset link\n5. Follow the instructions in the email\n\nIf you don't receive the email within 10 minutes, please check your spam folder. If you're still having trouble, let me know and I'll send you a manual reset.\n\nBest regards,\n{{agent_name}}",
                'is_public' => true,
            ],
            [
                'title' => 'Billing Inquiry Response',
                'category' => 'Billing',
                'content' => "Hello {{customer_name}},\n\nThank you for your billing inquiry. I've reviewed your account and can provide the following information:\n\n[Account details/billing information]\n\nIf you have any questions about these charges or need further clarification, please don't hesitate to ask. I'm here to help ensure everything is clear and accurate.\n\nBest regards,\n{{agent_name}}",
                'is_public' => true,
            ],
            [
                'title' => 'Issue Resolved - Follow Up',
                'category' => 'Closing',
                'content' => "Hi {{customer_name}},\n\nI'm pleased to let you know that your issue has been resolved. Here's a summary of what was done:\n\n[Summary of resolution]\n\nPlease test the solution and let me know if everything is working as expected. If you encounter any further issues or have additional questions, don't hesitate to reach out.\n\nThank you for choosing {{company_name}}!\n\nBest regards,\n{{agent_name}}",
                'is_public' => true,
            ],
            [
                'title' => 'Escalation to Senior Team',
                'category' => 'Escalation',
                'content' => "Hello {{customer_name}},\n\nThank you for your patience while I've been working on your case. Due to the complexity of your issue, I'm escalating your ticket to our senior technical team who have specialized expertise in this area.\n\nA senior technician will review your case and contact you within [timeframe] with an update or resolution.\n\nYour ticket reference is: {{ticket_id}}\n\nI apologize for any inconvenience and appreciate your understanding.\n\nBest regards,\n{{agent_name}}",
                'is_public' => true,
            ],
            [
                'title' => 'Refund Processing',
                'category' => 'Billing',
                'content' => "Hi {{customer_name}},\n\nI've processed your refund request as discussed. Here are the details:\n\n• Refund Amount: [amount]\n• Processing Time: 3-5 business days\n• Method: [Original payment method]\n\nYou should see the refund appear in your account within 3-5 business days. If you don't see it after this time, please contact your bank as they may need additional time to process it.\n\nIs there anything else I can help you with today?\n\nBest regards,\n{{agent_name}}",
                'is_public' => true,
            ],
            [
                'title' => 'Feature Request Acknowledgment',
                'category' => 'General',
                'content' => "Hello {{customer_name}},\n\nThank you for your feature request regarding [feature description]. I really appreciate you taking the time to share your feedback with us.\n\nI've forwarded your suggestion to our product development team for consideration in future updates. While I can't provide a specific timeline, all customer feedback is valuable to us and helps shape our product roadmap.\n\nI'll make sure to update you if there are any developments regarding this feature.\n\nThank you for being a valued customer!\n\nBest regards,\n{{agent_name}}",
                'is_public' => true,
            ],
            [
                'title' => 'Internal - Escalation Note',
                'category' => 'Escalation',
                'content' => "INTERNAL NOTE - ESCALATION\n\nTicket {{ticket_id}} requires senior team attention:\n\n• Customer: {{customer_name}} ({{customer_email}})\n• Issue Type: [description]\n• Previous Actions Taken: [list actions]\n• Customer Impact: [severity level]\n• Next Steps Required: [specific actions needed]\n\nPlease prioritize this case and provide update within [timeframe].\n\nAgent: {{agent_name}}\nDate: {{current_date}}",
                'is_public' => false,
            ],
        ];

        foreach ($responses as $response) {
            \App\Models\CannedResponse::create([
                'title' => $response['title'],
                'content' => $response['content'],
                'category' => $response['category'],
                'user_id' => $admin->id,
                'is_public' => $response['is_public'],
                'is_active' => true,
            ]);
        }

        $this->command->info('Created '.count($responses).' canned responses');
    }
}
