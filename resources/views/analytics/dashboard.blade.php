<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Analytics Dashboard') }}
            </h2>
            <div class="flex items-center space-x-4">
                <form method="GET" action="{{ route('analytics.dashboard') }}" class="flex items-center">
                    <label for="date_range" class="text-sm font-medium text-gray-700 mr-2">Date Range:</label>
                    <select name="date_range" id="date_range" onchange="this.form.submit()" 
                            class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="7" {{ $dateRange == '7' ? 'selected' : '' }}>Last 7 days</option>
                        <option value="30" {{ $dateRange == '30' ? 'selected' : '' }}>Last 30 days</option>
                        <option value="90" {{ $dateRange == '90' ? 'selected' : '' }}>Last 90 days</option>
                        <option value="365" {{ $dateRange == '365' ? 'selected' : '' }}>Last year</option>
                    </select>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Overview Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Tickets</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($analytics['overview']['total_tickets']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">New Tickets</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($analytics['overview']['new_tickets']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Resolved</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($analytics['overview']['resolved_tickets']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Open Tickets</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($analytics['overview']['open_tickets']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Performance Metrics</h3>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Avg Response Time</span>
                                    <span class="font-medium">{{ $analytics['overview']['avg_response_time'] }}</span>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Avg Resolution Time</span>
                                    <span class="font-medium">{{ $analytics['overview']['avg_resolution_time'] }}</span>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Resolution Rate</span>
                                    <span class="font-medium">{{ $analytics['overview']['resolution_rate'] }}%</span>
                                </div>
                                <div class="mt-1">
                                    <div class="bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $analytics['overview']['resolution_rate'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SLA Compliance -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">SLA Compliance</h3>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Response SLA</span>
                                    <span class="font-medium">{{ $analytics['sla']['response_compliance'] }}%</span>
                                </div>
                                <div class="mt-1">
                                    <div class="bg-gray-200 rounded-full h-2">
                                        <div class="bg-{{ $analytics['sla']['response_compliance'] >= 90 ? 'green' : ($analytics['sla']['response_compliance'] >= 70 ? 'yellow' : 'red') }}-600 h-2 rounded-full" 
                                             style="width: {{ $analytics['sla']['response_compliance'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Resolution SLA</span>
                                    <span class="font-medium">{{ $analytics['sla']['resolution_compliance'] }}%</span>
                                </div>
                                <div class="mt-1">
                                    <div class="bg-gray-200 rounded-full h-2">
                                        <div class="bg-{{ $analytics['sla']['resolution_compliance'] >= 90 ? 'green' : ($analytics['sla']['resolution_compliance'] >= 70 ? 'yellow' : 'red') }}-600 h-2 rounded-full" 
                                             style="width: {{ $analytics['sla']['resolution_compliance'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-xs text-gray-500">
                                <p>Response Breaches: {{ $analytics['sla']['response_breaches'] }}</p>
                                <p>Resolution Breaches: {{ $analytics['sla']['resolution_breaches'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ticket Trend Chart -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Daily Tickets Trend</h3>
                        <div class="h-32">
                            <canvas id="trendChart" width="300" height="120"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Analytics -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Tickets by Status -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Tickets by Status</h3>
                        <div class="space-y-3">
                            @foreach($analytics['tickets']['by_status'] as $status)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 rounded-full mr-3" style="background-color: {{ $status['color'] }}"></div>
                                        <span class="text-sm text-gray-900">{{ $status['status'] }}</span>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">{{ $status['count'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Tickets by Priority -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Tickets by Priority</h3>
                        <div class="space-y-3">
                            @foreach($analytics['tickets']['by_priority'] as $priority)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 rounded-full mr-3" style="background-color: {{ $priority['color'] }}"></div>
                                        <span class="text-sm text-gray-900">{{ $priority['priority'] }}</span>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">{{ $priority['count'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Agent Performance -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Agent Performance</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agent</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Assigned</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resolved</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Replies Sent</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Response Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resolution Rate</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($analytics['agents'] as $agent)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $agent['agent'] }}</div>
                                                <div class="text-sm text-gray-500">{{ $agent['email'] }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $agent['total_assigned'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $agent['resolved_tickets'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $agent['replies_sent'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $agent['avg_response_time'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $agent['resolution_rate'] }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Office Performance -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Department Performance</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Tickets</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">New Tickets</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resolved</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Resolution Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resolution Rate</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($analytics['offices'] as $office)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $office['office'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $office['total_tickets'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $office['new_tickets'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $office['resolved_tickets'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $office['avg_resolution_time'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $office['resolution_rate'] }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Top Customers -->
            @if(count($analytics['tickets']['top_customers']) > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Top Customers (by ticket count)</h3>
                        <div class="space-y-3">
                            @foreach($analytics['tickets']['top_customers'] as $customer)
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $customer['customer'] }}</div>
                                        <div class="text-sm text-gray-500">{{ $customer['email'] }}</div>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $customer['ticket_count'] }} tickets
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Trend Chart
        const ctx = document.getElementById('trendChart').getContext('2d');
        const trendData = @json($analytics['trends']);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: trendData.map(item => item.date),
                datasets: [{
                    label: 'Tickets',
                    data: trendData.map(item => item.tickets),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                elements: {
                    point: {
                        radius: 3
                    }
                }
            }
        });
    </script>
</x-app-layout>