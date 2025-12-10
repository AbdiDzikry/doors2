@props(['title', 'value', 'icon', 'route'])

<div class="bg-white rounded-lg shadow-lg p-6 flex items-center justify-between hover:shadow-xl transition-shadow duration-300">
    <div>
        <p class="text-sm font-medium text-gray-500">{{ $title }}</p>
        <p class="text-3xl font-bold text-gray-900">{{ $value }}</p>
        @if(isset($route))
            <a href="{{ $route }}" class="text-sm font-medium text-green-600 hover:text-green-800 mt-2 inline-block">
                View Details &rarr;
            </a>
        @endif
    </div>
    <div class="bg-green-100 rounded-full p-4">
        <i class="{{ $icon }} text-green-600 text-2xl"></i>
    </div>
</div>
