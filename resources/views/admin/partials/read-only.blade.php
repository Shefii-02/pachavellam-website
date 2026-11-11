{{-- Shown on a page the person may only View (not Manage): says so, and hides the buttons that add, edit or delete.
     The server refuses the changes anyway (403); this just keeps the buttons away. --}}
<div class="alert alert-secondary py-2 small mx-3 mt-3 mb-0" id="viewOnlyNote"><i class="bi bi-eye me-1"></i> View only: you can look at this page but not add, edit or delete anything.</div>
<style>
    .vo-hide { display: none !important; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var root = document.querySelector('.page-content-wrapper') || document.body;
    var hide = function (el) { if (el) { el.classList.add('vo-hide'); } };
    var changes = /(^|[\/_-])(create|add|new|edit|delete|remove|import|upload|bulk|publish|restore)([\/_-]|$|\?)/i;
    var words = /^\s*(\+?\s*(add|new|create|edit|delete|remove|import|upload|publish|unpublish|save|update|restore|move|copy|clone|duplicate|bulk)\b)/i;

    // forms that change something (not search / filter forms)
    root.querySelectorAll('form').forEach(function (f) {
        var method = (f.getAttribute('method') || 'get').toLowerCase();
        var spoof = f.querySelector('input[name="_method"]');
        if (method !== 'get' || (spoof && spoof.value.toLowerCase() !== 'get')) {
            if (!f.closest('.modal')) { hide(f); } else { hide(f.closest('.modal')); }
        }
    });
    // modals with a changing form: hide the buttons that open them
    root.querySelectorAll('[data-bs-toggle="modal"],[data-toggle="modal"]').forEach(function (b) {
        var target = document.querySelector(b.getAttribute('data-bs-target') || b.getAttribute('data-target') || b.getAttribute('href') || '__none__');
        if (!target || target.querySelector('form:not([method="get"]):not([method="GET"])')) { hide(b); }
    });
    // links and buttons that add / edit / delete
    root.querySelectorAll('a[href], button, input[type=submit], [data-action]').forEach(function (el) {
        var href = el.getAttribute('href') || el.getAttribute('data-url') || '';
        var text = (el.textContent || el.value || el.getAttribute('title') || '').trim();
        var icons = el.querySelector('.bi-trash, .bi-trash3, .bi-pencil, .bi-pencil-square, .bi-plus, .bi-plus-lg, .bi-plus-circle, .bi-x-lg, .fa-trash, .fa-edit, .fa-plus');
        var cls = el.className || '';
        if (typeof cls !== 'string') { cls = ''; }
        if (/\b(btn-(danger|success|warning)|btn-(outline-)?(danger))\b/.test(cls) && el.tagName !== 'A') { hide(el); return; }
        if ((href && changes.test(href)) || words.test(text) || icons || /\b(btn-delete|btn-edit|js-delete|js-edit|delete-btn|edit-btn)\b/.test(cls)) { hide(el); }
    });
    // keep what the person must still use (navigation, filters, pagination, view / open links)
    root.querySelectorAll('.pagination a, [data-vo-keep], form[method="get"] button, form[method="GET"] button').forEach(function (el) { el.classList.remove('vo-hide'); });
});
</script>
