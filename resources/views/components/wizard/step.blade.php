@props([
    'step' => 1,
])

<div x-show="step === {{ $step }}"
     x-cloak
     class="wizard-step-panel transition-opacity duration-200"
     data-step="{{ $step }}">
    {{ $slot }}
</div>
