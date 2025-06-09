<div class="flex-shrink-0">
    <img 
        src="{{ $getAvatarUrl() }}" 
        alt="{{ $user->name }}" 
        class="{{ $getSizeClasses() }} rounded-full object-cover border-2 border-gray-200 dark:border-gray-700 {{ $class }}"
        title="{{ $user->name }} ({{ ucfirst($user->role) }})"
        loading="lazy"
    />
</div>