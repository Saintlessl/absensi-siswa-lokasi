/**
 * MySkanic Design System - Searchable Select & Multi-Select Engine
 * Features:
 *  - Server-Side Pagination & AJAX Loading
 *  - Cross-Page Selection Persistence
 *  - Debounced Search (300ms) with In-Flight Request Cancellation (AbortController)
 *  - Select All on Current Page & Clear All
 *  - Single & Multi-Select Modes
 *  - Dynamic Hidden Inputs for Native Laravel Form Submission
 *  - Dependent Select Watcher (e.g. Jurusan -> Rombel -> Siswa)
 */

(function () {
    'use strict';

    function defineMyskanicSelect() {
        if (!window.Alpine) return;

        window.Alpine.data('myskanicSelect', (config = {}) => ({
            // Configuration
            name: config.name || 'select_field',
            endpoint: config.endpoint || '',
            multiple: Boolean(config.multiple),
            placeholder: config.placeholder || 'Pilih data...',
            searchPlaceholder: config.searchPlaceholder || 'Ketik untuk mencari...',
            perPage: config.perPage || 15,
            disabled: Boolean(config.disabled),
            required: Boolean(config.required),
            dependsOn: config.dependsOn || null,
            dependsParam: config.dependsParam || 'parent_id',
            emptyMessage: config.emptyMessage || 'Tidak ada data yang sesuai.',
            extraParams: config.extraParams || {},

            // UI State
            isOpen: false,
            searchQuery: '',
            isLoading: false,
            errorMessage: null,

            // Data & Pagination
            items: [],
            selectedItems: [], // Array of { id, label, subtext, ... }
            currentPage: 1,
            lastPage: 1,
            total: 0,
            hasMore: false,

            // Internal controllers
            abortController: null,
            debounceTimer: null,
            currentDependencyValue: null,

            init() {
                // Initialize selected items if passed
                if (config.initialSelected) {
                    this.loadInitialSelected(config.initialSelected);
                }

                // Setup dependency watcher if configured
                if (this.dependsOn) {
                    this.$nextTick(() => {
                        this.setupDependencyWatcher();
                    });
                }

                // Global event listeners for programmatic control
                window.addEventListener('myskanic-select:set-param', (e) => {
                    if (!e.detail) return;
                    if (e.detail.name === this.name || e.detail.id === this.$el.id) {
                        if (e.detail.param && e.detail.value !== undefined) {
                            this.extraParams = this.extraParams || {};
                            this.extraParams[e.detail.param] = e.detail.value;
                            this.selectedItems = [];
                            this.disabled = false;
                            this.fetchPage(1);
                        }
                    }
                });

                window.addEventListener('myskanic-select:reset', (e) => {
                    if (!e.detail) return;
                    if (e.detail.name === this.name || e.detail.id === this.$el.id) {
                        this.selectedItems = [];
                        this.items = [];
                        this.searchQuery = '';
                        this.currentPage = 1;
                        if (e.detail.disabled !== undefined) {
                            this.disabled = Boolean(e.detail.disabled);
                        }
                    }
                });
            },

            /**
             * Watch parent element (e.g. #selRombel or #selJurusan)
             */
            setupDependencyWatcher() {
                const parentEl = document.getElementById(this.dependsOn);
                if (!parentEl) return;

                const checkParent = () => {
                    const val = parentEl.value;
                    if (val !== this.currentDependencyValue) {
                        this.currentDependencyValue = val;
                        this.selectedItems = [];
                        this.items = [];
                        this.searchQuery = '';
                        this.currentPage = 1;

                        if (val) {
                            this.disabled = false;
                            this.fetchPage(1);
                        } else {
                            this.disabled = true;
                            this.isOpen = false;
                        }
                    }
                };

                // Listen to native change and custom events
                parentEl.addEventListener('change', checkParent);
                parentEl.addEventListener('input', checkParent);

                // Initial evaluation
                if (parentEl.value) {
                    this.currentDependencyValue = parentEl.value;
                    this.disabled = false;
                } else if (!this.endpoint) {
                    this.disabled = true;
                }
            },

            /**
             * Load pre-selected items on edit forms
             */
            async loadInitialSelected(raw) {
                if (!raw) return;

                // If already passed as array of objects with id and label
                if (Array.isArray(raw) && raw.length > 0 && typeof raw[0] === 'object' && raw[0].id) {
                    this.selectedItems = raw;
                    return;
                }

                // If passed as array of IDs or single ID
                const ids = Array.isArray(raw) ? raw : [raw];
                if (ids.length === 0) return;

                // If endpoint supports resolving selected_ids
                if (this.endpoint) {
                    try {
                        const url = new URL(this.endpoint, window.location.origin);
                        url.searchParams.set('selected_ids', ids.join(','));
                        const res = await fetch(url.toString(), {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        if (res.ok) {
                            const json = await res.json();
                            if (json.data && Array.isArray(json.data)) {
                                this.selectedItems = json.data;
                            }
                        }
                    } catch (e) {
                        // Fallback: create placeholder items
                        this.selectedItems = ids.map(id => ({ id, label: `Pilihan #${id}` }));
                    }
                } else {
                    this.selectedItems = ids.map(id => ({ id, label: `Pilihan #${id}` }));
                }
            },

            /**
             * Open / Close Dropdown
             */
            toggleDropdown() {
                if (this.disabled) return;
                if (this.isOpen) {
                    this.closeDropdown();
                } else {
                    this.openDropdown();
                }
            },

            openDropdown() {
                if (this.disabled) return;
                this.isOpen = true;
                this.errorMessage = null;

                // Load first page if empty
                if (this.items.length === 0) {
                    this.fetchPage(1);
                }

                // Focus search input on next tick
                this.$nextTick(() => {
                    if (this.$refs.searchInput) {
                        this.$refs.searchInput.focus();
                    }
                });
            },

            closeDropdown() {
                this.isOpen = false;
            },

            /**
             * Handle Search Input with Debounce & AbortController
             */
            onSearch(e) {
                const query = e.target.value;
                this.searchQuery = query;

                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    this.fetchPage(1);
                }, 300);
            },

            clearSearch() {
                this.searchQuery = '';
                this.fetchPage(1);
                this.$nextTick(() => {
                    if (this.$refs.searchInput) this.$refs.searchInput.focus();
                });
            },

            /**
             * Build URL with query params
             */
            buildUrl(page = 1) {
                const url = new URL(this.endpoint, window.location.origin);
                url.searchParams.set('page', page);
                url.searchParams.set('per_page', this.perPage);

                if (this.searchQuery.trim() !== '') {
                    url.searchParams.set('q', this.searchQuery.trim());
                }

                // If dependent on another field
                if (this.dependsOn && this.currentDependencyValue) {
                    url.searchParams.set(this.dependsParam, this.currentDependencyValue);
                }

                // Append any extra params
                if (this.extraParams && typeof this.extraParams === 'object') {
                    for (const [key, val] of Object.entries(this.extraParams)) {
                        if (val !== null && val !== undefined && val !== '') {
                            url.searchParams.set(key, val);
                        }
                    }
                }

                return url.toString();
            },

            /**
             * Fetch Page from Server with In-Flight Cancellation
             */
            async fetchPage(page = 1) {
                if (!this.endpoint) return;

                // Cancel previous in-flight request
                if (this.abortController) {
                    this.abortController.abort();
                }
                this.abortController = new AbortController();

                this.isLoading = true;
                this.errorMessage = null;

                try {
                    const targetUrl = this.buildUrl(page);
                    const res = await fetch(targetUrl, {
                        signal: this.abortController.signal,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!res.ok) {
                        throw new Error(`HTTP ${res.status}`);
                    }

                    const json = await res.json();
                    this.items = json.data || [];

                    if (json.pagination) {
                        this.currentPage = json.pagination.current_page || page;
                        this.lastPage = json.pagination.last_page || 1;
                        this.total = json.pagination.total || this.items.length;
                        this.hasMore = Boolean(json.pagination.has_more);
                    } else {
                        this.currentPage = 1;
                        this.lastPage = 1;
                        this.total = this.items.length;
                        this.hasMore = false;
                    }
                } catch (err) {
                    if (err.name === 'AbortError') return; // Normal cancellation
                    console.error('MySkanicSelect fetch error:', err);
                    this.errorMessage = 'Gagal memuat data. Periksa koneksi Anda.';
                    this.items = [];
                } finally {
                    this.isLoading = false;
                }
            },

            /**
             * Pagination Navigation
             */
            prevPage() {
                if (this.currentPage > 1 && !this.isLoading) {
                    this.fetchPage(this.currentPage - 1);
                }
            },

            nextPage() {
                if (this.currentPage < this.lastPage && !this.isLoading) {
                    this.fetchPage(this.currentPage + 1);
                }
            },

            /**
             * Selection Logic
             */
            isSelected(id) {
                return this.selectedItems.some(item => String(item.id) === String(id));
            },

            toggleItem(item) {
                if (this.multiple) {
                    const idx = this.selectedItems.findIndex(i => String(i.id) === String(item.id));
                    if (idx > -1) {
                        this.selectedItems.splice(idx, 1);
                    } else {
                        this.selectedItems.push(item);
                    }
                } else {
                    // Single select
                    this.selectedItems = [item];
                    this.closeDropdown();
                }

                // Dispatch custom change event to container
                this.$dispatch('myskanic-select-change', {
                    name: this.name,
                    selected: this.selectedItems,
                    ids: this.selectedItems.map(i => i.id)
                });
            },

            removeItem(id) {
                const idx = this.selectedItems.findIndex(i => String(i.id) === String(id));
                if (idx > -1) {
                    this.selectedItems.splice(idx, 1);
                    this.$dispatch('myskanic-select-change', {
                        name: this.name,
                        selected: this.selectedItems,
                        ids: this.selectedItems.map(i => i.id)
                    });
                }
            },

            clearAll() {
                this.selectedItems = [];
                this.$dispatch('myskanic-select-change', {
                    name: this.name,
                    selected: [],
                    ids: []
                });
            },

            /**
             * Select All on Current Page
             */
            isAllCurrentPageSelected() {
                if (this.items.length === 0) return false;
                return this.items.every(item => this.isSelected(item.id));
            },

            toggleSelectAllCurrentPage() {
                if (this.isAllCurrentPageSelected()) {
                    // Deselect all on current page
                    const currentPageIds = new Set(this.items.map(i => String(i.id)));
                    this.selectedItems = this.selectedItems.filter(i => !currentPageIds.has(String(i.id)));
                } else {
                    // Add items from current page that aren't already selected
                    const currentSelectedIds = new Set(this.selectedItems.map(i => String(i.id)));
                    for (const item of this.items) {
                        if (!currentSelectedIds.has(String(item.id))) {
                            this.selectedItems.push(item);
                            currentSelectedIds.add(String(item.id));
                        }
                    }
                }

                this.$dispatch('myskanic-select-change', {
                    name: this.name,
                    selected: this.selectedItems,
                    ids: this.selectedItems.map(i => i.id)
                });
            },

            /**
             * Label display helper
             */
            get singleSelectedLabel() {
                return this.selectedItems.length > 0 ? this.selectedItems[0].label : this.placeholder;
            },

            get hasSelection() {
                return this.selectedItems.length > 0;
            }
        }));
    }

    if (window.Alpine) {
        defineMyskanicSelect();
    } else {
        document.addEventListener('alpine:init', defineMyskanicSelect);
    }
})();
