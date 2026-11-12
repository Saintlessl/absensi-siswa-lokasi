@props([
    'name' => 'select_field',
    'label' => null,
    'placeholder' => 'Pilih data...',
    'searchPlaceholder' => 'Ketik untuk mencari nama, kode...',
    'endpoint' => '',
    'multiple' => false,
    'required' => false,
    'disabled' => false,
    'dependsOn' => null,
    'dependsParam' => 'parent_id',
    'selected' => null,
    'perPage' => 15,
    'emptyMessage' => 'Tidak ada data yang sesuai.',
    'hint' => null,
    'id' => null,
    'extraParams' => [],
])

@php
    $componentId = $id ?? 'myskanic_select_' . str_replace(['[', ']'], '', $name) . '_' . uniqid();
    $inputName = $multiple ? (str_ends_with($name, '[]') ? $name : $name . '[]') : $name;
@endphp

<div class="w-full space-y-1.5 myskanic-select-container"
     id="{{ $componentId }}"
     x-data="myskanicSelect({
         name: '{{ $name }}',
         endpoint: '{{ $endpoint }}',
         multiple: {{ $multiple ? 'true' : 'false' }},
         placeholder: '{{ addslashes($placeholder) }}',
         searchPlaceholder: '{{ addslashes($searchPlaceholder) }}',
         perPage: {{ (int) $perPage }},
         disabled: {{ $disabled ? 'true' : 'false' }},
         required: {{ $required ? 'true' : 'false' }},
         dependsOn: '{{ $dependsOn }}',
         dependsParam: '{{ $dependsParam }}',
         emptyMessage: '{{ addslashes($emptyMessage) }}',
         initialSelected: @js($selected),
         extraParams: @js($extraParams)
     })"
     @click.outside="closeDropdown()"
     @keydown.escape.window="closeDropdown()">

    {{-- Form Label --}}
    @if($label)
        <label class="block text-xs font-bold text-slate-700">
            {{ $label }}
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
            @if($hint)
                <span class="font-normal text-slate-400 text-[11px] ml-1">({{ $hint }})</span>
            @endif
        </label>
    @endif

    {{-- Hidden Inputs for Native Laravel Form Submission --}}
    <template x-for="item in selectedItems" :key="item.id">
        <input type="hidden" name="{{ $inputName }}" :value="item.id">
    </template>

    {{-- Fallback empty input if required --}}
    @if($required)
        <input type="hidden"
               name="__validation_{{ str_replace(['[', ']'], '', $name) }}"
               :value="selectedItems.length > 0 ? 'valid' : ''"
               required>
    @endif

    {{-- Custom Select Trigger Box --}}
    <div class="myskanic-select-trigger"
         :class="{ 'active': isOpen, 'opacity-60 cursor-not-allowed': disabled }"
         @click="toggleDropdown()"
         tabindex="0"
         role="combobox"
         :aria-expanded="isOpen">

        {{-- Left: Placeholder / Single Label / Multi-Select Chips --}}
        <div class="myskanic-chips-wrapper">
            {{-- MULTI SELECT: Chips Display --}}
            <template x-if="multiple">
                <div class="flex flex-wrap gap-1.5 items-center w-full">
                    <template x-for="item in selectedItems" :key="item.id">
                        <span class="myskanic-chip">
                            <span class="truncate max-w-[200px]" x-text="item.label"></span>
                            <button type="button"
                                    class="myskanic-chip-remove"
                                    @click.stop="removeItem(item.id)"
                                    title="Hapus">
                                <i class='bx bx-x text-sm leading-none'></i>
                            </button>
                        </span>
                    </template>
                    <template x-if="selectedItems.length === 0">
                        <span class="text-sm text-slate-400 font-medium" x-text="placeholder"></span>
                    </template>
                </div>
            </template>

            {{-- SINGLE SELECT: Text Display --}}
            <template x-if="!multiple">
                <div class="truncate text-sm font-medium"
                     :class="hasSelection ? 'text-slate-900 font-semibold' : 'text-slate-400'"
                     x-text="singleSelectedLabel">
                </div>
            </template>
        </div>

        {{-- Right Action Icons --}}
        <div class="flex items-center gap-1.5 flex-shrink-0 text-slate-400">
            {{-- Counter badge for multi-select --}}
            <template x-if="multiple && selectedItems.length > 0">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#003FA9] text-white"
                      x-text="selectedItems.length + ' dipilih'"></span>
            </template>

            {{-- Clear all button --}}
            <template x-if="hasSelection && !disabled">
                <button type="button"
                        @click.stop="clearAll()"
                        class="p-1 rounded-full text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition"
                        title="Hapus semua pilihan">
                    <i class='bx bx-x text-base leading-none'></i>
                </button>
            </template>

            {{-- Dropdown chevron --}}
            <i class='bx bx-chevron-down text-lg transition-transform duration-200'
               :class="{ 'rotate-180 text-[#003FA9]': isOpen }"></i>
        </div>
    </div>

    {{-- Floating Dropdown Popover --}}
    <div class="myskanic-select-panel"
         x-show="isOpen"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         x-cloak>

        {{-- Search Box --}}
        <div class="myskanic-select-searchbox">
            <i class='bx bx-search absolute left-6 top-1/2 -translate-y-1/2 text-slate-400 text-base'></i>
            <input type="text"
                   x-ref="searchInput"
                   :value="searchQuery"
                   @input="onSearch($event)"
                   :placeholder="searchPlaceholder"
                   autocomplete="off">
            <template x-if="searchQuery.trim() !== ''">
                <button type="button"
                        @click="clearSearch()"
                        class="absolute right-6 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                    <i class='bx bx-x text-lg leading-none'></i>
                </button>
            </template>
        </div>

        {{-- Multi-Select Quick Action Toolbar --}}
        <template x-if="multiple">
            <div class="myskanic-select-toolbar">
                <button type="button"
                        @click="toggleSelectAllCurrentPage()"
                        class="inline-flex items-center gap-1.5 hover:underline focus:outline-none cursor-pointer">
                    <i class='bx' :class="isAllCurrentPageSelected() ? 'bx-check-square text-sm' : 'bx-square text-sm'"></i>
                    <span x-text="isAllCurrentPageSelected() ? 'Batalkan Pilih Halaman Ini' : 'Pilih Semua di Halaman Ini'"></span>
                </button>
                <template x-if="selectedItems.length > 0">
                    <button type="button"
                            @click="clearAll()"
                            class="text-rose-600 hover:underline focus:outline-none">
                        Hapus Semua (<span x-text="selectedItems.length"></span>)
                    </button>
                </template>
            </div>
        </template>

        {{-- Loading Indicator --}}
        <div x-show="isLoading" class="py-8 text-center text-slate-500">
            <div class="inline-flex items-center gap-2 text-xs font-semibold">
                <svg class="animate-spin h-4 w-4 text-[#003FA9]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Memuat data dari server...</span>
            </div>
        </div>

        {{-- Error State --}}
        <template x-if="!isLoading && errorMessage">
            <div class="py-6 px-4 text-center text-rose-600 text-xs">
                <i class='bx bx-error-circle text-2xl mb-1'></i>
                <p x-text="errorMessage"></p>
                <button type="button" @click="fetchPage(currentPage)" class="mt-2 text-[#003FA9] underline font-bold">
                    Coba Lagi
                </button>
            </div>
        </template>

        {{-- Options List --}}
        <div class="myskanic-select-list" x-show="!isLoading && !errorMessage">
            <template x-for="item in items" :key="item.id">
                <div class="myskanic-select-option"
                     :class="{ 'selected': isSelected(item.id) }"
                     @click="toggleItem(item)">

                    {{-- Checkbox / Radio indicator --}}
                    <div class="myskanic-select-option-checkbox">
                        <template x-if="isSelected(item.id)">
                            <i class='bx bx-check text-sm leading-none font-bold'></i>
                        </template>
                    </div>

                    {{-- Item Text Details --}}
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-bold text-slate-800 leading-tight truncate" x-text="item.label"></div>
                        <template x-if="item.subtext">
                            <div class="text-[11px] text-slate-400 mt-0.5 leading-tight truncate" x-text="item.subtext"></div>
                        </template>
                    </div>
                </div>
            </template>

            {{-- Empty State --}}
            <template x-if="items.length === 0">
                <div class="py-8 text-center text-slate-400 text-xs">
                    <i class='bx bx-search-alt text-3xl text-slate-300 mb-1.5'></i>
                    <p class="font-semibold text-slate-600" x-text="emptyMessage"></p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Coba gunakan kata kunci pencarian yang lain.</p>
                </div>
            </template>
        </div>

        {{-- Pagination Footer --}}
        <div class="myskanic-select-pagination" x-show="!isLoading && lastPage > 1">
            <div>
                Hal. <strong class="text-slate-700" x-text="currentPage"></strong> dari <strong class="text-slate-700" x-text="lastPage"></strong>
                <span class="text-slate-400">(<span x-text="total"></span> data)</span>
            </div>
            <div class="flex items-center gap-1.5">
                <button type="button"
                        class="myskanic-pagination-btn"
                        :disabled="currentPage <= 1 || isLoading"
                        @click="prevPage()">
                    <i class='bx bx-chevron-left text-sm'></i> Sebelumnya
                </button>
                <button type="button"
                        class="myskanic-pagination-btn"
                        :disabled="currentPage >= lastPage || isLoading"
                        @click="nextPage()">
                    Berikutnya <i class='bx bx-chevron-right text-sm'></i>
                </button>
            </div>
        </div>

    </div>

</div>
