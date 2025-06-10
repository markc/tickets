<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-center">
            <div class="text-center">
                <div class="text-base font-bold text-gray-900 dark:text-white mb-1">
                    {{ $time }}
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $day }}, {{ $date }}
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>