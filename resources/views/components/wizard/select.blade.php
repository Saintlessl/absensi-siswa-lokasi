@props([
    'label' => '',
    'name' => '',
    'placeholder' => 'Pilih...',
    'required' => false,
    'hint' => null,
    'model' => null,
    'id' => null,
    'options' => [],
])

@php
    $selectId = $id ?? ($name ?: 'select_' . uniqid());
@endphp

<div class="w-full">
    @if($label)
        <label for="{{ $selectId }}" class="block text-xs font-semibold text-[#0F172A] mb-1.5">
            {{ $label }}
            @if($hint)
                <span class="font-normal text-slate-400">({{ $hint }})</span>
            @endif
            @if($required)
                <span class="text-rose-500 font-normal">*</span>
            @endif
        </label>
    @endif
    <div class="relative">
        <select name="{{ $name }}"
                id="{{ $selectId }}"
                @if($required) required @endif
                @if($model) x-model="{{ $model }}" @endif
                {{ $attributes->merge(['class' => 'w-full h-12 pl-4 pr-10 rounded-xl bg-[#E2EDFF] border-0 text-[#0F172A] text-sm font-medium appearance-none focus:bg-white focus:ring-2 focus:ring-[#003FA9]/30 transition-all outline-none cursor-pointer']) }}>
            @if($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            {{ $slot }}
        </select>
        {{-- Chevron Down Icon --}}
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-[#0F172A]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </div>
</div>
