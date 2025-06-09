<div class="space-y-4">
    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ $response->title }}</h3>
        
        @if($response->category)
            <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100 mb-3">
                {{ $response->category }}
            </div>
        @endif
        
        <div class="prose dark:prose-invert max-w-none">
            <div class="whitespace-pre-wrap text-gray-700 dark:text-gray-300">{{ $response->content }}</div>
        </div>
    </div>
    
    <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg">
        <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-2">Available Variables:</h4>
        <div class="grid grid-cols-2 gap-2 text-xs">
            @foreach(\App\Models\CannedResponse::getAvailableVariables() as $variable => $description)
                <div class="text-blue-800 dark:text-blue-200">
                    <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">{{ $variable }}</code>
                    <span class="text-blue-600 dark:text-blue-400">- {{ $description }}</span>
                </div>
            @endforeach
        </div>
    </div>
    
    <div class="flex justify-between items-center text-sm text-gray-500 dark:text-gray-400">
        <div>
            Created by {{ $response->user->name }} on {{ $response->created_at->format('M j, Y') }}
        </div>
        <div>
            Used {{ $response->usage_count }} times
            @if($response->last_used_at)
                (last used {{ $response->last_used_at->diffForHumans() }})
            @endif
        </div>
    </div>
</div>