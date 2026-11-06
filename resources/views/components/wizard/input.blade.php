@props([
    'label' => '',
    'name' => '',
    'type' => 'text',
    'placeholder' => '',
    'required' => false,
    'hint' => null,
    'model' => null,
    'value' => null,
    'maxlength' => null,
    'id' => null,
    'readonly' => false,
])

@php
    $inputId = $id ?? ($name ?: 'input_' . uniqid());
@endphp

<div class="w-full">
    @if($label)
        <label for="{{ $inputId }}" class="block text-xs font-semibold text-[#0F172A] mb-1.5">
            {{ $label }}
            @if($hint)
                <span class="font-normal text-slate-400">({{ $hint }})</span>
            @endif
            @if($required)
                <span class="text-rose-500 font-normal">*</span>
            @endif
        </label>
    @endif
    <input type="{{ $type }}"
           name="{{ $name }}"
           id="{{ $inputId }}"
           placeholder="{{ $placeholder }}"
           @if($required) required @endif
           @if($maxlength) maxlength="{{ $maxlength }}" @endif
           @if($model) x-model="{{ $model }}" @endif
           @if($value !== null) value="{{ $value }}" @endif
           @if($readonly) readonly @endif
           {{ $attributes->merge(['class' => 'w-full h-12 px-4 rounded-xl bg-[#E2EDFF] border-0 text-[#0F172A] text-sm font-medium placeholder-[#94A3B8] focus:bg-white focus:ring-2 focus:ring-[#003FA9]/30 transition-all outline-none disabled:opacity-60']) }}>
</div>
