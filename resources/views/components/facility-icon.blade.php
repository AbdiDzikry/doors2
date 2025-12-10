@props(['facility'])

@php
    $icon = '';
    switch (strtolower(trim($facility))) {
        case 'projector':
            $icon = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a2 2 0 00-2 2v1H6a2 2 0 00-2 2v7a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2V4a2 2 0 00-2-2zm-2 9a2 2 0 104 0 2 2 0 00-4 0z" clip-rule="evenodd" /></svg>';
            break;
        case 'whiteboard':
            $icon = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 3a1 1 0 011-1h14a1 1 0 011 1v10a1 1 0 01-1 1H3a1 1 0 01-1-1V3zm13 2H5v7h10V5z" clip-rule="evenodd" /></svg>';
            break;
        case 'wifi':
            $icon = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-4.293-9.707a1 1 0 011.414-1.414 3 3 0 014.242 0 1 1 0 01-1.414 1.414 1 1 0 00-1.414 0 1 1 0 01-1.414 0zm2.828 2.828a1 1 0 011.414 0 1 1 0 010 1.414 3 3 0 01-4.242 0 1 1 0 010-1.414 1 1 0 011.414 0zm-4.242 0a1 1 0 010-1.414 5 5 0 017.07 0 1 1 0 11-1.414 1.414A3 3 0 008.586 11a1 1 0 01-1.414-1.414z" clip-rule="evenodd" /></svg>';
            break;
        case 'ac':
        case 'air conditioner':
            $icon = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v.268l2.121 2.121a1 1 0 01-.707 1.707H7.586a1 1 0 01-.707-1.707L9 4.268V4a1 1 0 011-1zm0 14a1 1 0 01-1-1v-.268l-2.121-2.121a1 1 0 01.707-1.707h4.828a1 1 0 01.707 1.707L11 15.732V16a1 1 0 01-1 1zM5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414L11.414 12l3.293 3.293a1 1 0 01-1.414 1.414L10 13.414l-3.293 3.293a1 1 0 01-1.414-1.414L8.586 12 5.293 8.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>';
            break;
        case 'tv':
            $icon = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm2-1a1 1 0 00-1 1v8a1 1 0 001 1h10a1 1 0 001-1V5a1 1 0 00-1-1H5z" /><path d="M6 8a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1z" /></svg>';
            break;
        case 'speaker':
            $icon = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1zM8 5a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1zM5 7a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm-1 3a1 1 0 100 2h12a1 1 0 100-2H4z" clip-rule="evenodd" /></svg>';
            break;
        default:
            $icon = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9a1 1 0 112 0v3a1 1 0 11-2 0v-3zm1-4a1 1 0 00-1 1v.01a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>';
            break;
    }
@endphp

<div class="flex items-center space-x-2 text-sm text-gray-600">
    {!! $icon !!}
    <span>{{ $facility }}</span>
</div>
