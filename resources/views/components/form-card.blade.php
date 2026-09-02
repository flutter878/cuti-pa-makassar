{{-- Komponen card untuk form --}}
@props(['title'])

<div class="bg-white rounded-lg shadow-sm border border-gray-100">
    @if(isset($title))
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-800">{{ $title }}</h2>
        </div>
    @endif
    <div class="p-5">
        {{ $slot }}
    </div>
</div>
