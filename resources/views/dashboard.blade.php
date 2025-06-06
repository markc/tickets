<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(auth()->user()->isCustomer())
                <!-- Customer Dashboard -->
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-sm font-medium text-gray-500">Total Tickets</h3>
                            <p class="text-3xl font-bold text-gray-900">{{ $stats['total_tickets'] ?? 0 }}</p>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-sm font-medium text-gray-500">Open Tickets</h3>
                            <p class="text-3xl font-bold text-blue-600">{{ $stats['open_tickets'] ?? 0 }}</p>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-sm font-medium text-gray-500">Status Breakdown</h3>
                            <div class="mt-2 space-y-1">
                                @if(isset($stats['by_status']))
                                    @foreach($stats['by_status'] as $status => $count)
                                        <div class="flex justify-between text-sm">
                                            <span>{{ $status }}</span>
                                            <span class="font-medium">{{ $count }}</span>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-4">Welcome, {{ auth()->user()->name }}!</h3>
                        <p class="mb-6">You can create and track your support tickets here.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="border rounded-lg p-4 hover:bg-gray-50">
                                <h4 class="font-medium mb-2">Create New Ticket</h4>
                                <p class="text-sm text-gray-600 mb-4">Submit a new support request</p>
                                <a href="{{ route('tickets.create') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                    Create Ticket
                                </a>
                            </div>
                            
                            <div class="border rounded-lg p-4 hover:bg-gray-50">
                                <h4 class="font-medium mb-2">My Tickets</h4>
                                <p class="text-sm text-gray-600 mb-4">View and track your tickets</p>
                                <a href="{{ route('tickets.index') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                                    View Tickets
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Tickets -->
                @if(isset($stats['recent_tickets']) && $stats['recent_tickets']->count() > 0)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Recent Tickets</h3>
                            <div class="space-y-3">
                                @foreach($stats['recent_tickets'] as $ticket)
                                    <div class="flex items-center justify-between p-3 border rounded-lg">
                                        <div class="flex-1">
                                            <h4 class="font-medium">{{ $ticket->subject }}</h4>
                                            <p class="text-sm text-gray-600">{{ $ticket->office->name }} • {{ $ticket->created_at->format('M j, Y') }}</p>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" 
                                                  style="background-color: {{ $ticket->status->color }}20; color: {{ $ticket->status->color }};">
                                                {{ $ticket->status->name }}
                                            </span>
                                            <a href="{{ route('tickets.show', $ticket->uuid) }}" 
                                               class="text-blue-600 hover:text-blue-900 text-sm">View</a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <!-- Admin/Agent Dashboard -->
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-sm font-medium text-gray-500">Total Tickets</h3>
                            <p class="text-3xl font-bold text-gray-900">{{ $stats['total_tickets'] ?? 0 }}</p>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-sm font-medium text-gray-500">Assigned to Me</h3>
                            <p class="text-3xl font-bold text-blue-600">{{ $stats['assigned_to_me'] ?? 0 }}</p>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-sm font-medium text-gray-500">Unassigned</h3>
                            <p class="text-3xl font-bold text-orange-600">{{ $stats['unassigned_tickets'] ?? 0 }}</p>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-sm font-medium text-gray-500">Quick Actions</h3>
                            <div class="mt-2">
                                <a href="/admin" class="text-blue-600 hover:text-blue-900 text-sm">Admin Panel</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status and Priority Breakdown -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Tickets by Status</h3>
                            <div class="space-y-2">
                                @if(isset($stats['status_stats']))
                                    @foreach($stats['status_stats'] as $status)
                                        <div class="flex items-center justify-between">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" 
                                                  style="background-color: {{ $status->color }}20; color: {{ $status->color }};">
                                                {{ $status->name }}
                                            </span>
                                            <span class="font-medium">{{ $status->tickets_count }}</span>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Tickets by Priority</h3>
                            <div class="space-y-2">
                                @if(isset($stats['priority_stats']))
                                    @foreach($stats['priority_stats'] as $priority)
                                        <div class="flex items-center justify-between">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" 
                                                  style="background-color: {{ $priority->color }}20; color: {{ $priority->color }};">
                                                {{ $priority->name }}
                                            </span>
                                            <span class="font-medium">{{ $priority->tickets_count }}</span>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-4">Welcome, {{ auth()->user()->name }}!</h3>
                        <p class="mb-6">
                            @if(auth()->user()->isAdmin())
                                You have full access to the admin panel to manage tickets, users, and system settings.
                            @else
                                As an agent, you can manage tickets assigned to your departments.
                            @endif
                        </p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="border rounded-lg p-4 hover:bg-gray-50">
                                <h4 class="font-medium mb-2">Admin Panel</h4>
                                <p class="text-sm text-gray-600 mb-4">Access the full administrative interface</p>
                                <a href="/admin" 
                                   class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                    Go to Admin
                                </a>
                            </div>
                            
                            <div class="border rounded-lg p-4 hover:bg-gray-50">
                                <h4 class="font-medium mb-2">Customer Tickets</h4>
                                <p class="text-sm text-gray-600 mb-4">View tickets from customer perspective</p>
                                <a href="{{ route('tickets.index') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                                    Customer View
                                </a>
                            </div>
                            
                            <div class="border rounded-lg p-4 hover:bg-gray-50">
                                <h4 class="font-medium mb-2">Create Test Ticket</h4>
                                <p class="text-sm text-gray-600 mb-4">Test customer ticket creation</p>
                                <a href="{{ route('tickets.create') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-purple-500 text-white rounded-md hover:bg-purple-600">
                                    Create Ticket
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Tickets -->
                @if(isset($stats['recent_tickets']) && $stats['recent_tickets']->count() > 0)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Recent Tickets</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full table-auto">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ticket #</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Office</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($stats['recent_tickets'] as $ticket)
                                            <tr>
                                                <td class="px-4 py-2 text-sm">{{ substr($ticket->uuid, 0, 8) }}</td>
                                                <td class="px-4 py-2 text-sm">{{ $ticket->subject }}</td>
                                                <td class="px-4 py-2 text-sm">{{ $ticket->creator->name }}</td>
                                                <td class="px-4 py-2">
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" 
                                                          style="background-color: {{ $ticket->status->color }}20; color: {{ $ticket->status->color }};">
                                                        {{ $ticket->status->name }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" 
                                                          style="background-color: {{ $ticket->priority->color }}20; color: {{ $ticket->priority->color }};">
                                                        {{ $ticket->priority->name }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-2 text-sm">{{ $ticket->office->name }}</td>
                                                <td class="px-4 py-2">
                                                    <a href="/admin/tickets/{{ $ticket->id }}" 
                                                       class="text-blue-600 hover:text-blue-900 text-sm">Admin View</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
