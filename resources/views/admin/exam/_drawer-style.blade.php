{{-- Makes a .modal.modal-drawer open as a drawer from the right (same Bootstrap modal: buttons, events and scrolling keep working). --}}
<style>
    .modal.modal-drawer .modal-dialog { position:fixed; top:0; right:0; bottom:0; margin:0; width:100%; max-width:min(560px, 100vw) !important; height:100%; transform:translateX(100%); transition:transform .3s ease-out; }
    .modal.modal-drawer.modal-drawer-wide .modal-dialog { max-width:min(720px, 100vw) !important; }
    .modal.modal-drawer.show .modal-dialog { transform:none; }
    .modal.modal-drawer .modal-content { height:100%; border:0; border-radius:0; box-shadow:-8px 0 30px rgba(0,0,0,.2); }
    .modal.modal-drawer form.modal-content, .modal.modal-drawer .modal-content > form { display:flex; flex-direction:column; height:100%; min-height:0; }
    .modal.modal-drawer .modal-body { flex:1 1 auto; overflow-y:auto; }
    .modal.modal-drawer .modal-footer { justify-content:flex-end; }
    /* a narrow drawer: the form fields stack (wide ones full width, small ones two by two) */
    .modal.modal-drawer .row > [class*="col-md-"] { flex:0 0 auto; width:50%; } .modal.modal-drawer .row > .col-md-6, .modal.modal-drawer .row > .col-md-12, .modal.modal-drawer .row > .col-12 { width:100%; }
    @media (max-width:575px) { .modal.modal-drawer .row > [class*="col-md-"] { width:100%; } }
</style>
