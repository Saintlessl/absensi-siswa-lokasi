{{-- Generic client-side image preview for every <input type="file" data-preview="#target">.
     Preview is independent from DB save (requirement: preview tidak bergantung penyimpanan DB).
     Validates type (jpg/png/webp) + size (default 5 MB, override via data-max-mb),
     shows inline error, and refreshes when the file is replaced. --}}
<script>
(function () {
    const ALLOWED = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    function bind(input) {
        if (input.__imgPrevBound) return;
        input.__imgPrevBound = true;
        const targetSel = input.getAttribute('data-preview');
        const maxMb = parseFloat(input.getAttribute('data-max-mb') || '5');
        const errEl = input.parentElement.querySelector('[data-preview-error]');
        input.addEventListener('change', function () {
            const file = this.files && this.files[0];
            const img = targetSel ? document.querySelector(targetSel) : null;
            if (errEl) errEl.textContent = '';
            if (!file) { if (img) img.classList.add('hidden'); return; }
            if (ALLOWED.indexOf(file.type) === -1) {
                this.value = '';
                if (errEl) errEl.textContent = 'Format gambar tidak valid. Gunakan JPG, PNG, WEBP, atau GIF.';
                if (img) img.classList.add('hidden');
                return;
            }
            if (file.size > maxMb * 1024 * 1024) {
                this.value = '';
                if (errEl) errEl.textContent = 'Ukuran file melebihi batas ' + maxMb + ' MB.';
                if (img) img.classList.add('hidden');
                return;
            }
            if (img) {
                img.src = URL.createObjectURL(file);
                img.classList.remove('hidden');
                img.onload = () => URL.revokeObjectURL(img.src);
            }
            if (errEl) errEl.textContent = 'Pratinjau siap — gambar akan disimpan setelah form dikirim.';
        });
    }
    function init() { document.querySelectorAll('input[type="file"][data-preview]').forEach(bind); }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
</script>
