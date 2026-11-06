@props([
    'label' => '',
    'name' => '',
    'placeholder' => '',
    'required' => false,
    'hint' => null,
    'model' => null,
    'id' => null,
    'rows' => 3,
])

@php
    $textareaId = $id ?? ($name ?: 'textarea_' . uniqid());
@endphp

<div class="w-full">
    @if($label)
        <label for="{{ $textareaId }}" class="block text-xs font-semibold text-[#0F172A] mb-1.5">
            {{ $label }}
            @if($hint)
                <span class="font-normal text-slate-400">({{ $hint }})</span>
            @endif
            @if($required)
                <span class="text-rose-500 font-normal">*</span>
            @endif
        </label>
    @endif
    <textarea name="{{ $name }}"
              id="{{ $textareaId }}"
              rows="{{ $rows }}"
              placeholder="{{ $placeholder }}"
              @if($required) required @endif
              @if($model) x-model="{{ $model }}" @endif
              {{ $attributes->merge(['class' => 'w-full p-4 rounded-xl bg-[#E2EDFF] border-0 text-[#0F172A] text-sm font-medium placeholder-[#94A3B8] focus:bg-white focus:ring-2 focus:ring-[#003FA9]/30 transition-all outline-none resize-none']) }}>{{ $slot }}</textarea>
</div>
