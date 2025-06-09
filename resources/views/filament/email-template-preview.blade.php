<div class="space-y-4">
    <div class="bg-gray-50 p-4 rounded-lg">
        <h4 class="font-medium text-sm text-gray-700 mb-2">Subject Line:</h4>
        <p class="text-gray-900 font-medium">{{ $subject }}</p>
    </div>
    
    <div class="bg-white border rounded-lg p-4">
        <h4 class="font-medium text-sm text-gray-700 mb-3">Email Content:</h4>
        <div class="prose max-w-none">
            @if($type === 'markdown')
                {!! \Illuminate\Support\Str::markdown($content) !!}
            @elseif($type === 'html')
                {!! $content !!}
            @else
                <pre class="whitespace-pre-wrap font-sans">{{ $content }}</pre>
            @endif
        </div>
    </div>
    
    <div class="bg-blue-50 p-3 rounded-lg">
        <p class="text-xs text-blue-600">
            <strong>Note:</strong> This preview uses sample data. Actual emails will contain real ticket information.
        </p>
    </div>
</div>