@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full border-l-4 border-white bg-slate-800 py-2 pe-4 ps-3 text-start text-base font-bold text-white focus:outline-none'
            : 'block w-full border-l-4 border-transparent py-2 pe-4 ps-3 text-start text-base font-medium text-slate-300 transition hover:border-slate-500 hover:bg-slate-800 hover:text-white focus:outline-none';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
