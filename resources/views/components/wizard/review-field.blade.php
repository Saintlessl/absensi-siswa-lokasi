@props([
    'label' => '',
    'value' => null,
    'bind' => null,
    'hint' => null,
    'isTextarea' => false,
    'rows' => 3,
])

<div class="w-full">
    @if($label)
        <label class="block text-xs font-semibold text-[#0F172A] mb-1.5">
            {{ $label }}
            @if($hint)
                <span class="font-normal text-slate-400">({{ $hint }})</span>
            @endif
        </label>
    @endif

    @if($isTextarea)
        <div class="w-full p-4 rounded-xl bg-[#E2EDFF] text-[#0F172A] text-sm font-medium min-h-[96px] break-words whitespace-pre-wrap select-text"
             @if($bind) x-text="{{ $bind }} || '-'" @endif>
            @if(!$bind)
                {{ $value ?: '-' }}
            @endif
        </div>
    @else
        <div class="w-full h-12 px-4 rounded-xl bg-[#E2EDFF] text-[#0F172A] text-sm font-medium flex items-center truncate select-text"
             @if($bind) x-text="{{ $bind }} || '-'" @endif>
            @if(!$bind)
                {{ $value ?: '-' }}
            @endif
        </div>
    @endif
</div>
