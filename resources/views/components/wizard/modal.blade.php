@props([
    'id' => 'wizard-modal',
    'title' => 'Tambah Data',
    'steps' => ['Langkah 1', 'Langkah 2', 'Konfirmasi'],
    'action' => '#',
    'method' => 'POST',
    'formId' => null,
    'enctype' => 'multipart/form-data',
    'maxWidth' => 'max-w-5xl',
    'submitText' => 'Berikutnya',
    'showSubmitIcon' => true,
])

@php
    $actualFormId = $formId ?? ('form-' . $id);
    $totalSteps = count($steps);
@endphp

<div id="{{ $id }}"
     x-data="wizardModalHandler({ totalSteps: {{ $totalSteps }}, modalId: '{{ $id }}', formId: '{{ $actualFormId }}' })"
     x-cloak
     class="fixed inset-0 z-[60] hidden overflow-y-auto"
     aria-labelledby="modal-title-{{ $id }}"
     role="dialog"
     aria-modal="true">

    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
         @click="close()"></div>

    {{-- Modal Positioning Container --}}
    <div class="flex min-h-full items-center justify-center p-3 sm:p-6 text-left">
        {{-- Modal Card --}}
        <div class="relative w-full {{ $maxWidth }} rounded-[28px] bg-[#EFF5FF] p-6 sm:p-8 shadow-2xl border border-blue-100/60 my-auto transform transition-all text-slate-800"
             @keydown.escape.window="close()">

            {{-- Header --}}
            <div class="flex items-start justify-between">
                <div>
                    <h2 id="modal-title-{{ $id }}" class="text-2xl font-bold text-[#0F172A] tracking-tight">
                        {{ $title }}
                    </h2>
                    {{-- Breadcrumb Step Indicator --}}
                    <div class="flex items-center flex-wrap text-sm mt-1.5 font-medium select-none">
                        @foreach($steps as $idx => $stepName)
                            <span :class="step >= {{ $idx + 1 }} ? 'text-[#003FA9] font-bold' : 'text-[#AAC1EA] font-normal'"
                                  class="transition-colors duration-200">
                                {{ $stepName }}
                            </span>
                            @if(!$loop->last)
                                <span class="mx-2 text-[#AAC1EA]">-</span>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Close Button --}}
                <button type="button"
                        @click="close()"
                        class="text-[#94A3B8] hover:text-[#0F172A] p-1.5 rounded-lg transition-colors focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Divider --}}
            <div class="h-px bg-[#D8E6FC] mt-4 mb-6"></div>

            {{-- Main Form Container --}}
            <form action="{{ $action }}"
                  method="{{ $method }}"
                  id="{{ $actualFormId }}"
                  enctype="{{ $enctype }}"
                  @submit.prevent="handleSubmit($event)">
                @csrf

                {{-- Steps Slot Content --}}
                <div class="wizard-steps-container">
                    {{ $slot }}
                </div>

                {{-- Footer Navigation --}}
                <div class="flex items-center justify-between mt-8 pt-4">
                    {{-- Left Back Button --}}
                    <div>
                        <button type="button"
                                x-show="step > 1"
                                x-cloak
                                @click="prevStep()"
                                class="h-11 px-5 rounded-xl bg-[#E2EDFF] hover:bg-[#D4E4FC] text-[#003FA9] font-bold text-sm inline-flex items-center gap-2 transition duration-150">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5z"/>
                            </svg>
                            <span>Kembali</span>
                        </button>
                    </div>

                    {{-- Right Next / Submit Button --}}
                    <div>
                        {{-- Next Button (Steps 1 to N-1) --}}
                        <button type="button"
                                x-show="step < totalSteps"
                                @click="nextStep()"
                                class="h-11 px-6 rounded-xl bg-[#003FA9] hover:bg-[#00348C] text-white font-bold text-sm inline-flex items-center gap-2 shadow-sm transition duration-150">
                            <span>Berikutnya</span>
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/>
                            </svg>
                        </button>

                        {{-- Final Step Submit Button --}}
                        <button type="submit"
                                x-show="step === totalSteps"
                                x-cloak
                                :disabled="isSubmitting"
                                class="h-11 px-6 rounded-xl bg-[#003FA9] hover:bg-[#00348C] text-white font-bold text-sm inline-flex items-center gap-2 shadow-sm transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isSubmitting">{{ $submitText }}</span>
                            <span x-show="isSubmitting" class="inline-flex items-center gap-2">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Menyimpan...
                            </span>
                            @if($showSubmitIcon)
                                <svg x-show="!isSubmitting" class="w-4 h-4" fill="currentColor" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/>
                                </svg>
                            @endif
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
