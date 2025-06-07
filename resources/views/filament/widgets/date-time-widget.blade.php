<x-filament-widgets::widget>
    <x-filament::section>
        <div class="text-center space-y-3">
            <!-- Current Date and Time -->
            <div>
                <div class="text-base font-bold text-gray-900 dark:text-white" id="current-time">
                    {{ now()->format('g:i A') }}
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400" id="current-date">
                    {{ now()->format('l, F j, Y') }}
                </div>
            </div>
        </div>
    </x-filament::section>
    
    <script>
        // Update time every second
        function updateTime() {
            const now = new Date();
            
            // Update time in 12-hour format with AM/PM (no seconds)
            const timeElement = document.getElementById('current-time');
            if (timeElement) {
                timeElement.textContent = now.toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });
            }
            
            // Update date
            const dateElement = document.getElementById('current-date');
            if (dateElement) {
                dateElement.textContent = now.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            }
        }
        
        // Initialize and start updates (every 30 seconds since no seconds display)
        updateTime();
        setInterval(updateTime, 30000);
    </script>
</x-filament-widgets::widget>