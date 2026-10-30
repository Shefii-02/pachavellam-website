/*
 * Media library for the admin: PVMedia.panel() draws the library (folders, files, upload) into any element;
 * PVMedia.pick() opens it in a window to choose files; every <input type="file"> inside a form becomes
 * "Choose from library / Upload" automatically, and every .pvm-field is a library field that stores the file's path.
 */
(function () {
    'use strict';
    var CFG = window.PVMEDIA || {};
    var BASE = CFG.base || '/admin/media';
    var CSRF = CFG.csrf || (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    var SPECS = CFG.specs || {};
    var PVMEDIA_ADMIN = !!CFG.admin;
    var ICON = { image: 'bi-image', video: 'bi-camera-video', audio: 'bi-mic', document: 'bi-file-earmark-text', other: 'bi-file-earmark' };
    var TYPE_LABEL = { '': 'All files', image: 'Pictures', video: 'Videos', audio: 'Voice / audio', document: 'Documents' };

    function esc(t) { var d = document.createElement('div'); d.textContent = t == null ? '' : t; return d.innerHTML; }
    function h(html) { var t = document.createElement('template'); t.innerHTML = html.trim(); return t.content.firstChild; }
    function api(path, opt) {
        opt = opt || {};
        var headers = { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' };
        if (opt.json) { headers['Content-Type'] = 'application/json'; opt.body = JSON.stringify(opt.json); }
        return fetch(BASE + path, { method: opt.method || 'GET', headers: headers, body: opt.body, credentials: 'same-origin' }).then(function (r) {
            return r.json().catch(function () { return {}; }).then(function (j) { j._status = r.status; j._ok = r.ok; return j; });
        });
    }
    function gcd(a, b) { return b ? gcd(b, a % b) : a; }
    function ratioText(w, hh) { if (!w || !hh) return ''; var g = gcd(w, hh), a = w / g, b = hh / g; return (a > 40 || b > 40) ? (Math.round(w / hh * 100) / 100) + ':1' : a + ':' + b; }

    /** Is a picture right for the field? Returns {ok, text}. */
    function check(spec, it) {
        if (!spec || it.type !== 'image' || !it.w) return null;
        var msgs = [], ok = true;
        if (spec.w && spec.h) {
            var want = spec.w / spec.h, have = it.w / it.h;
            if (Math.abs(want - have) / want > 0.06) { ok = false; msgs.push('shape is ' + ratioText(it.w, it.h) + ', the page shows ' + spec.ratio + ' - the edges may be cut'); }
            if (it.w < spec.w * 0.75) { ok = false; msgs.push('small (' + it.w + '×' + it.h + ') - may look blurry at ' + spec.w + '×' + spec.h); }
        }
        if (spec.max_mb && it.bytes > spec.max_mb * 1048576) { ok = false; msgs.push('large file (' + it.size + ') - pages load faster under ' + spec.max_mb + ' MB'); }
        return { ok: ok, text: ok ? 'Good fit: ' + it.w + '×' + it.h + (spec.ratio ? ' (' + ratioText(it.w, it.h) + ')' : '') : msgs.join('; ') };
    }
    function specLine(spec) {
        if (!spec) return '';
        var s = [];
        if (spec.w && spec.h) s.push('<b>' + spec.w + ' × ' + spec.h + ' px</b>' + (spec.ratio ? ' (' + spec.ratio + ')' : ''));
        if (spec.formats) s.push(spec.formats);
        if (spec.max_mb) s.push('up to ' + spec.max_mb + ' MB');
        return 'Recommended: ' + s.join(' · ');
    }



    /* Where a pop-up (dialog, "where to?" window, menu) is mounted. Bootstrap keeps the keyboard focus inside an open modal / drawer, so a pop-up
       appended to <body> can never be typed into. It goes inside the picker drawer (or the open modal / drawer) instead; on a plain page: <body>. */
    var drawerEl = null;
    function overlayHost() {
        if (drawerEl && drawerEl.classList.contains('on')) return drawerEl;
        var open = document.querySelectorAll('.modal.show, .offcanvas.show');
        return open.length ? open[open.length - 1] : document.body;
    }

    /* ------------------------------------------------------------------ dialogs (no browser prompt / confirm / alert) */
    var ask = {
        /** o: title, message, input {label,value,placeholder}, options [{value,label,desc,danger}], confirm, danger, hideCancel. Resolves to the answer, or null when cancelled. */
        dlg: function (o) {
            return new Promise(function (resolve) {
                var box = h('<div class="pvm-dlg"><div class="box" role="dialog" aria-modal="true"><div class="hd"><i class="bi ' + (o.danger ? 'bi-exclamation-triangle-fill red' : (o.icon || 'bi-info-circle-fill')) + '"></i><h6></h6></div><div class="msg"></div><div class="body"></div><div class="err"></div>' +
                    '<div class="act"><button type="button" class="pvm-btn cancel">Cancel</button><button type="button" class="pvm-btn ok"></button></div></div></div>');
                box.querySelector('h6').textContent = o.title || '';
                var msg = box.querySelector('.msg'); msg.innerHTML = o.message ? esc(o.message).replace(/\n/g, '<br>') : ''; if (!o.message) msg.hidden = true;
                var ok = box.querySelector('.ok'); ok.innerHTML = o.confirm || 'OK'; ok.classList.add(o.danger ? 'redfill' : 'pri');
                if (o.hideCancel) box.querySelector('.cancel').hidden = true;
                var body = box.querySelector('.body'), inp = null, chosen = o.options ? (o.options[0] && o.options[0].value) : null;
                if (o.input) { body.innerHTML = '<label>' + esc(o.input.label || '') + '<input type="text" maxlength="' + (o.input.max || 240) + '" placeholder="' + esc(o.input.placeholder || '') + '"></label>'; inp = body.querySelector('input'); inp.value = o.input.value || ''; }
                if (o.options) {
                    body.innerHTML = o.options.map(function (op, i) { return '<label class="opt' + (op.danger ? ' dg' : '') + '"><input type="radio" name="pvmopt" value="' + i + '"' + (i === 0 ? ' checked' : '') + '><span><b>' + esc(op.label) + '</b>' + (op.desc ? '<small>' + esc(op.desc) + '</small>' : '') + '</span></label>'; }).join('');
                    body.addEventListener('change', function (e) { if (e.target.name === 'pvmopt') chosen = o.options[+e.target.value].value; });
                }
                var tc = null;
                if (o.typeToConfirm) {
                    var tcBox = h('<div class="tc"><label>Type <b>' + esc(o.typeToConfirm) + '</b> to confirm<input type="text" autocomplete="off" spellcheck="false" placeholder="' + esc(o.typeToConfirm) + '"></label></div>');
                    body.parentNode.insertBefore(tcBox, box.querySelector('.err')); tc = tcBox.querySelector('input');
                    ok.disabled = true;
                    tc.addEventListener('input', function () { ok.disabled = tc.value.trim() !== o.typeToConfirm; });
                }
                overlayHost().appendChild(box);
                var prev = document.activeElement;
                function close(v) { document.removeEventListener('keydown', key, true); box.remove(); if (prev && prev.focus) try { prev.focus(); } catch (e) {} resolve(v); }
                function done() {
                    if (tc && tc.value.trim() !== o.typeToConfirm) { box.querySelector('.err').textContent = 'Type ' + o.typeToConfirm + ' (capital letters) to continue.'; tc.focus(); return; }
                    if (inp) { var v = inp.value.trim(); if (!v && o.input.required !== false) { box.querySelector('.err').textContent = o.input.error || 'Type a name.'; inp.focus(); return; } return close(v); }
                    close(o.options ? chosen : true);
                }
                function key(e) { if (e.key === 'Escape') { e.stopPropagation(); close(null); } else if (e.key === 'Enter' && (!e.target || e.target.tagName !== 'BUTTON')) { e.preventDefault(); done(); } }
                document.addEventListener('keydown', key, true);
                box.querySelector('.cancel').onclick = function () { close(null); };
                ok.onclick = done;
                box.addEventListener('mousedown', function (e) { if (e.target === box && !o.hideCancel) close(null); });
                setTimeout(function () { (tc || inp || ok).focus(); if (inp && !tc) inp.select(); }, 30);
            });
        },
        confirm: function (o) { return ask.dlg(o).then(function (v) { return v === true; }); },
        prompt: function (o) { return ask.dlg(o); },
        notice: function (m) { return ask.dlg({ title: 'Please note', message: String(m), confirm: 'OK', hideCancel: true }); }
    };
    function alert(m) { ask.notice(m); }   // inside this file every old alert() uses the styled dialog

    /* ------------------------------------------------------------------ "where to?": pick a folder by its path */
    function choosePath(o) {
        return new Promise(function (resolve) {
            var overlay = h('<div class="pvm-pathwin"><div class="box"><h6></h6><div class="pvm-pathbar"><span class="lbl">Path</span><code class="cur"></code></div>' +
                '<div class="tree"></div><div class="pvm-pathnew"><button type="button" class="pvm-btn nf"><i class="bi bi-folder-plus"></i> New folder here</button></div>' +
                '<label class="nm" hidden><span></span><input type="text" maxlength="240"></label><div class="err"></div>' +
                '<div class="act"><button type="button" class="pvm-btn cancel">Cancel</button><button type="button" class="pvm-btn pri ok"></button></div></div></div>');
            overlay.querySelector('h6').textContent = o.title;
            overlay.querySelector('.ok').innerHTML = o.confirm;
            var nameBox = overlay.querySelector('.nm');
            if (o.nameLabel) { nameBox.hidden = false; nameBox.querySelector('span').textContent = o.nameLabel; nameBox.querySelector('input').value = o.name || ''; }
            overlayHost().appendChild(overlay);
            var tree = [], sel = o.start == null ? null : o.start, open = {}, cur = overlay.querySelector('.cur'), err = overlay.querySelector('.err');
            function pathOf(id) { var names = [], found = false; (function walk(n, trail) { n.forEach(function (f) { if (found) return; var t = trail.concat(f.name); if (f.id === id) { names = t; found = true; } else walk(f.children, t); }); })(tree, []); return 'Media library' + (names.length ? ' / ' + names.join(' / ') : ''); }
            function blocked(id) { return (o.exclude || []).indexOf(id) >= 0; }
            function render() {
                cur.textContent = pathOf(sel);
                var html = '<div class="row' + (sel === null ? ' on' : '') + '" data-id=""><i class="bi bi-house-door-fill"></i>Media library (top)</div>';
                (function walk(n, d) {
                    n.forEach(function (f) {
                        var kids = f.children.length, isOpen = open[f.id] || (sel !== null && pathOf(sel).indexOf('/ ' + f.name) >= 0);
                        html += '<div class="row' + (sel === f.id ? ' on' : '') + (blocked(f.id) ? ' off' : '') + '" data-id="' + f.id + '" style="padding-left:' + (10 + d * 18) + 'px">' +
                            (kids ? '<i class="bi ' + (isOpen ? 'bi-caret-down-fill' : 'bi-caret-right-fill') + ' tg" data-t="' + f.id + '"></i>' : '<i class="tg"></i>') + '<i class="bi bi-folder-fill"></i>' + esc(f.name) + (blocked(f.id) ? ' <em>(not here)</em>' : '') + '</div>';
                        if (kids && isOpen) walk(f.children, d + 1);
                    });
                })(tree, 0);
                overlay.querySelector('.tree').innerHTML = html;
            }
            function load() { return api('/tree').then(function (j) { tree = j.tree || []; render(); }); }
            overlay.querySelector('.tree').addEventListener('click', function (e) {
                var t = e.target.closest('[data-t]'); if (t) { open[+t.dataset.t] = !open[+t.dataset.t]; return render(); }
                var r = e.target.closest('.row'); if (!r || r.classList.contains('off')) return;
                sel = r.dataset.id ? +r.dataset.id : null; err.textContent = ''; render();
            });
            overlay.querySelector('.nf').addEventListener('click', function () {
                ask.prompt({ title: 'New folder', message: 'It will be created inside:\n' + pathOf(sel), input: { label: 'Folder name', placeholder: 'e.g. Banners 2026', max: 120 }, confirm: '<i class="bi bi-folder-plus"></i> Create folder', icon: 'bi-folder-plus' }).then(function (n) {
                    if (!n) return;
                    api('/folders', { method: 'POST', json: { name: n, parent_id: sel } }).then(function (j) { if (!j._ok) { err.textContent = j.message || 'Could not create the folder'; return; } open[sel] = true; sel = j.id; load(); });
                });
            });
            function close(v) { overlay.remove(); resolve(v); }
            overlay.querySelector('.cancel').addEventListener('click', function () { close(null); });
            overlay.addEventListener('mousedown', function (e) { if (e.target === overlay) close(null); });
            overlay.querySelector('.ok').addEventListener('click', function () {
                if (blocked(sel)) { err.textContent = 'Choose another folder.'; return; }
                var nm = overlay.querySelector('.nm input').value.trim();
                if (o.nameLabel && o.nameRequired && !nm) { err.textContent = 'Type a name.'; return; }
                close({ folder_id: sel, name: o.nameLabel ? nm : '', path: pathOf(sel) });
            });
            load();
        });
    }

    /* small pop-up menu next to a click */
    function popMenu(ev, entries) {
        [].forEach.call(document.querySelectorAll('.pvm-pop'), function (x) { x.remove(); });
        var m = h('<div class="pvm-pop">' + entries.map(function (e, i) { return e === '-' ? '<hr>' : '<button type="button" data-i="' + i + '" class="' + (e.red ? 'red' : '') + '"><i class="bi ' + e.icon + '"></i>' + esc(e.label) + '</button>'; }).join('') + '</div>');
        overlayHost().appendChild(m);
        var r = ev.currentTarget ? ev.currentTarget.getBoundingClientRect() : { left: ev.clientX, bottom: ev.clientY };
        m.style.left = Math.max(8, Math.min(window.innerWidth - 230, r.left - 150)) + 'px'; m.style.top = Math.min(window.innerHeight - 280, r.bottom + 4) + 'px';
        m.addEventListener('click', function (e) { var b = e.target.closest('button'); if (!b) return; m.remove(); entries[+b.dataset.i].run(); });
        setTimeout(function () { document.addEventListener('click', function off() { m.remove(); document.removeEventListener('click', off); }); }, 0);
    }

    /* ------------------------------------------------------------------ the library panel */
    function Panel(root, o) {
        this.o = o || {}; this.root = root; this.mode = this.o.mode || 'manage'; this.type = this.o.type || ''; this.lockType = !!this.o.lockType;
        this.folder = null; this.q = ''; this.page = 1; this.sel = {}; this.items = []; this.last = 1; this.multiple = !!this.o.multiple; this.current = null;
        this.build(); this.load(true);
    }
    Panel.prototype.build = function () {
        var self = this, root = this.root;
        root.classList.add('pvm'); root.innerHTML =
            '<div class="pvm-bar">' +
            '<div class="pvm-crumbs"></div>' +
            '<div class="pvm-tools"><input type="search" class="pvm-q" placeholder="Search files…">' +
            '<select class="pvm-type"><option value="">All files</option><option value="image">Pictures</option><option value="video">Videos</option><option value="audio">Voice / audio</option><option value="document">Documents</option></select>' +
            '<button type="button" class="pvm-btn pvm-newf"><i class="bi bi-folder-plus"></i> New folder</button>' + (this.mode === 'manage' ? '<button type="button" class="pvm-btn pvm-trash"><i class="bi bi-trash3"></i> Trash <span class="cnt"></span></button>' : '') +
            '<button type="button" class="pvm-btn pri pvm-up"><i class="bi bi-cloud-upload"></i> Upload</button>' +
            '<input type="file" class="pvm-file" multiple hidden></div></div>' +
            '<div class="pvm-main"><div class="pvm-gridwrap"><div class="pvm-drop"><i class="bi bi-cloud-arrow-up"></i><b>Drop files to upload</b><span>to this folder</span></div>' +
            '<div class="pvm-prog"></div><div class="pvm-grid"></div><div class="pvm-more"></div></div>' +
            '<aside class="pvm-side"><div class="pvm-empty">Select a file to see its details</div></aside></div>';
        this.$ = function (s) { return root.querySelector(s); };
        this.$('.pvm-type').value = this.type;
        if (this.lockType) this.$('.pvm-type').disabled = true;
        this.$('.pvm-file').accept = (this.o.accept || '');
        var t;
        this.$('.pvm-q').addEventListener('input', function () { clearTimeout(t); var v = this.value; t = setTimeout(function () { self.q = v.trim(); self.load(true); }, 300); });
        this.$('.pvm-type').addEventListener('change', function () { self.type = this.value; self.load(true); });
        this.$('.pvm-up').addEventListener('click', function () { if (self.trashMode) { self.trashMode = false; self.sel = {}; self.load(true).then(function () { self.$('.pvm-file').click(); }); } else self.$('.pvm-file').click(); });
        if (this.$('.pvm-trash')) this.$('.pvm-trash').addEventListener('click', function () { self.trashMode = !self.trashMode; self.sel = {}; self.q = ''; self.$('.pvm-q').value = ''; self.load(true); });
        this.$('.pvm-file').addEventListener('change', function () { self.upload(this.files); this.value = ''; });
        this.$('.pvm-newf').addEventListener('click', function () {
            var where = 'Media library' + (self.trail || []).map(function (t) { return ' / ' + t.name; }).join('');
            ask.prompt({ title: 'New folder', message: 'It will be created inside:\n' + where, input: { label: 'Folder name', placeholder: 'e.g. Banners 2026', max: 120 }, confirm: '<i class="bi bi-folder-plus"></i> Create folder', icon: 'bi-folder-plus' }).then(function (n) {
                if (n) api('/folders', { method: 'POST', json: { name: n, parent_id: self.folder } }).then(function (j) { if (j._ok) { self.toast('Folder created'); self.load(true); } else alert(j.message || 'Could not create the folder'); });
            });
        });
        var gw = this.$('.pvm-gridwrap'), depth = 0;
        gw.addEventListener('dragenter', function (e) { if (e.dataTransfer && [].indexOf.call(e.dataTransfer.types, 'Files') >= 0) { depth++; gw.classList.add('drag'); } });
        gw.addEventListener('dragover', function (e) { e.preventDefault(); });
        gw.addEventListener('dragleave', function () { depth = Math.max(0, depth - 1); if (!depth) gw.classList.remove('drag'); });
        gw.addEventListener('drop', function (e) { e.preventDefault(); depth = 0; gw.classList.remove('drag'); if (e.dataTransfer.files.length) self.upload(e.dataTransfer.files); });
        this.$('.pvm-more').addEventListener('click', function (e) { if (e.target.closest('button')) { self.page++; self.load(false); } });
    };
    Panel.prototype.load = function (reset) {
        var self = this; if (reset) { this.page = 1; this.items = []; }
        if (this.trashMode) {
            this.$('.pvm-grid').classList.add('busy');
            return api('/trash?page=' + this.page).then(function (j) {
                self.$('.pvm-grid').classList.remove('busy');
                self.items = reset ? j.items : self.items.concat(j.items); if (reset || j.page === 1) self.tfolders = j.folders || []; self.last = j.last; self.total = j.total; self.isAdmin = !!j.admin; self.draw();
            });
        }
        var qs = '?page=' + this.page + (this.folder ? '&folder=' + this.folder : '') + (this.type ? '&type=' + this.type : '') + (this.q ? '&q=' + encodeURIComponent(this.q) : '');
        this.$('.pvm-grid').classList.add('busy');
        return api('/list' + qs).then(function (j) {
            self.$('.pvm-grid').classList.remove('busy');
            if (!j.items) { self.$('.pvm-grid').innerHTML = '<div class="pvm-empty">Could not load the library.</div>'; return; }
            self.trashCount = j.trash; self.items = reset ? j.items : self.items.concat(j.items); self.last = j.last; self.folders = j.folders; self.trail = j.trail; self.total = j.total; self.searching = !!self.q;
            self.draw();
        });
    };
    Panel.prototype.drawTrash = function () {
        var self = this, crumbs = this.$('.pvm-crumbs');
        this.$('.pvm-trash').classList.add('on'); this.$('.pvm-trash .cnt').textContent = '';
        crumbs.innerHTML = '<a href="#" data-back="1"><i class="bi bi-arrow-left"></i> Media library</a> <i class="bi bi-chevron-right"></i> <b><i class="bi bi-trash3"></i> Trash</b> <small>' + this.total + ' item' + (this.total === 1 ? '' : 's') + ' · kept until an administrator deletes them for good</small>' +
            ' <a href="#" data-all="1" class="ms-2">Select all</a>' + (this.isAdmin && this.total ? ' <a href="#" data-empty="1" class="red ms-2">Empty trash</a>' : '');
        crumbs.onclick = function (e) {
            var a = e.target.closest('a'); if (!a) return; e.preventDefault();
            if (a.dataset.back) { self.trashMode = false; self.sel = {}; self.load(true); }
            if (a.dataset.all) { self.sel = {}; (self.tfolders || []).forEach(function (f) { self.sel['f' + f.id] = { id: 'f' + f.id, fid: f.id, isFolder: true, name: f.name, file: f.name, was: f.was, deleted: f.deleted, files: f.files }; }); self.items.forEach(function (it) { self.sel[it.id] = it; }); self.draw(); }
            if (a.dataset.empty) ask.confirm({ title: 'Empty the Trash?', message: 'Every file and folder in the Trash is deleted for good and the files are removed from the server. This cannot be undone.', typeToConfirm: 'DELETE', confirm: '<i class="bi bi-trash3-fill"></i> Delete everything forever', danger: true }).then(function (y) { if (y) self.purge({ all: true }); });
        };
        var html = '';
        (this.tfolders || []).forEach(function (f) {
            html += '<div class="pvm-tile folder trashed' + (self.sel['f' + f.id] ? ' sel' : '') + '" data-tf="' + f.id + '"><i class="bi bi-folder-fill"></i><b>' + esc(f.name) + '</b><small>' + f.files + ' file' + (f.files === 1 ? '' : 's') + ' inside</small><small>was in: ' + esc(f.was) + '</small><span class="chk"><i class="bi bi-check-lg"></i></span></div>';
        });
        this.items.forEach(function (it, i) {
            html += '<div class="pvm-tile' + (self.sel[it.id] ? ' sel' : '') + '" data-i="' + i + '" title="' + esc(it.file) + '">' +
                (it.type === 'image' ? '<img src="' + esc(it.url) + '" loading="lazy" alt="">' : '<div class="ic"><i class="bi ' + (ICON[it.type] || ICON.other) + '"></i><em>' + esc((it.ext || '').toUpperCase()) + '</em></div>') +
                '<b>' + esc(it.name) + '</b><small>deleted ' + esc(it.deleted || '') + '</small><small>was in: ' + esc(it.was) + '</small><span class="chk"><i class="bi bi-check-lg"></i></span></div>';
        });
        if (!html) html = '<div class="pvm-empty big"><i class="bi bi-trash3"></i>The Trash is empty.</div>';
        var grid = this.$('.pvm-grid'); grid.innerHTML = html;
        this.$('.pvm-more').innerHTML = this.page < this.last ? '<button type="button" class="pvm-btn">Load more</button>' : '';
        grid.onclick = function (e) {
            var tf = e.target.closest('[data-tf]'), t = e.target.closest('.pvm-tile[data-i]'), k, v;
            if (tf) { var f = (self.tfolders || []).filter(function (x) { return x.id === +tf.dataset.tf; })[0]; k = 'f' + f.id; v = { id: k, fid: f.id, isFolder: true, name: f.name, file: f.name, was: f.was, deleted: f.deleted, files: f.files }; }
            else if (t) { v = self.items[+t.dataset.i]; k = v.id; } else return;
            if (self.sel[k]) delete self.sel[k]; else self.sel[k] = v;
            self.draw();
        };
        grid.ondblclick = null; grid.oncontextmenu = null;
        this.sideTrash();
    };
    Panel.prototype.sideTrash = function () {
        var self = this, side = this.$('.pvm-side'), chosen = this.selected();
        if (!chosen.length) { side.innerHTML = '<div class="pvm-empty">Select files or folders to restore them to where they were' + (this.isAdmin ? ', or delete them for good' : '') + '.</div>'; return; }
        var files = chosen.filter(function (c) { return !c.isFolder; }), folders = chosen.filter(function (c) { return c.isFolder; }), one = chosen.length === 1 ? chosen[0] : null;
        side.innerHTML = '<div class="pvm-note">' + chosen.length + ' selected' + (folders.length ? ' (' + folders.length + ' folder' + (folders.length === 1 ? '' : 's') + ')' : '') + '</div>' +
            (one && !one.isFolder && one.type === 'image' ? '<div class="pvm-prev"><img src="' + esc(one.url) + '" alt=""></div>' : '') +
            (one ? '<div class="pvm-meta"><b>' + esc(one.file) + '</b><div>Deleted ' + esc(one.deleted || '') + '</div><div>Restores to: <b>' + esc(one.was) + '</b></div>' + (one.isFolder ? '<div>Brings back its sub folders and the ' + one.files + ' file(s) deleted with it.</div>' : '') + '</div>' : '<div class="pvm-spec">Each item goes back to the folder it was deleted from (the folder is brought back too if it was deleted).</div>') +
            '<div class="pvm-act"><button type="button" class="pvm-btn pri pvm-restore"><i class="bi bi-arrow-counterclockwise"></i> Restore ' + (chosen.length > 1 ? chosen.length + ' items' : '') + '</button>' +
            (this.isAdmin ? '<button type="button" class="pvm-btn red pvm-purge"><i class="bi bi-trash3-fill"></i> Delete forever</button>' : '<div class="pvm-spec">Only an administrator can delete forever.</div>') + '</div>';
        side.querySelector('.pvm-restore').onclick = function () {
            api('/restore', { method: 'POST', json: { ids: files.map(function (c) { return c.id; }), folder_ids: folders.map(function (c) { return c.fid; }) } }).then(function (j) {
                if (j._ok) { self.sel = {}; self.toast('Restored ' + j.files + ' file' + (j.files === 1 ? '' : 's') + (j.folders ? ' and ' + j.folders + ' folder' + (j.folders === 1 ? '' : 's') : '') + ' to their folders.'); self.load(true); } else alert(j.message || 'Could not restore');
            });
        };
        var pg = side.querySelector('.pvm-purge');
        if (pg) pg.onclick = function () {
            ask.confirm({ title: 'Delete forever?', message: 'This removes ' + (chosen.length === 1 ? '"' + chosen[0].file + '"' : chosen.length + ' items') + ' from the server for good. It cannot be restored.', typeToConfirm: 'DELETE', confirm: '<i class="bi bi-trash3-fill"></i> Delete forever', danger: true }).then(function (y) {
                if (y) self.purge({ ids: files.map(function (c) { return c.id; }), folder_ids: folders.map(function (c) { return c.fid; }) });
            });
        };
    };
    Panel.prototype.purge = function (body, force) {
        var self = this;
        api('/purge', { method: 'POST', json: Object.assign({ force: !!force, confirm: 'DELETE' }, body) }).then(function (j) {
            if (j._status === 409) { return ask.confirm({ title: 'Still used on the website', message: j.message, confirm: 'Delete forever anyway', danger: true }).then(function (y) { if (y) self.purge(body, true); }); }
            if (j._status === 403) return alert(j.message || 'Only an administrator can do this.');
            if (j._ok) { self.sel = {}; self.toast('Deleted for good: ' + j.files + ' file' + (j.files === 1 ? '' : 's') + '.'); self.load(true); }
        });
    };
    Panel.prototype.draw = function () {
        if (this.trashMode) return this.drawTrash();
        if (this.$('.pvm-trash')) { this.$('.pvm-trash').classList.remove('on'); this.$('.pvm-trash .cnt').textContent = this.trashCount ? '(' + this.trashCount + ')' : ''; }
        var self = this, crumbs = this.$('.pvm-crumbs');
        crumbs.innerHTML = '<a href="#" data-f="">Media library</a>' + (this.trail || []).map(function (t) { return ' <i class="bi bi-chevron-right"></i> <a href="#" data-f="' + t.id + '">' + esc(t.name) + '</a>'; }).join('') + (this.q ? ' <i class="bi bi-chevron-right"></i> <span>Search "' + esc(this.q) + '"</span>' : '') + ' <small>' + this.total + ' file' + (this.total === 1 ? '' : 's') + '</small>';
        crumbs.onclick = function (e) { var a = e.target.closest('a[data-f]'); if (!a) return; e.preventDefault(); self.folder = a.dataset.f ? +a.dataset.f : null; self.q = ''; self.$('.pvm-q').value = ''; self.load(true); };
        var html = '';
        (this.q ? [] : this.folders || []).forEach(function (f) {
            html += '<div class="pvm-tile folder" data-fid="' + f.id + '"><i class="bi bi-folder-fill"></i><b>' + esc(f.name) + '</b><small>' + f.count + ' file' + (f.count === 1 ? '' : 's') + (f.children ? ' · ' + f.children + ' folder' + (f.children === 1 ? '' : 's') : '') + '</small>' +
                (self.mode === 'manage' ? '<span class="pvm-fm" data-fm="' + f.id + '" title="Rename or delete"><i class="bi bi-three-dots"></i></span>' : '') + '</div>';
        });
        this.items.forEach(function (it, i) {
            var on = self.sel[it.id] ? ' sel' : '', bad = self.o.type && self.o.type !== 'any' && false;
            html += '<div class="pvm-tile' + on + '" data-i="' + i + '" title="' + esc(it.file) + '">' +
                (it.type === 'image' ? '<img src="' + esc(it.url) + '" loading="lazy" alt="">' : '<div class="ic"><i class="bi ' + (ICON[it.type] || ICON.other) + '"></i><em>' + esc((it.ext || '').toUpperCase()) + '</em></div>') +
                '<b>' + esc(it.name) + '</b><small>' + (it.w ? it.w + '×' + it.h + ' · ' : '') + it.size + '</small><span class="chk"><i class="bi bi-check-lg"></i></span></div>';
        });
        if (!html) html = '<div class="pvm-empty big"><i class="bi bi-images"></i>' + (this.q ? 'Nothing found.' : 'This folder is empty. Drop files here or press Upload.') + '</div>';
        var grid = this.$('.pvm-grid'); grid.innerHTML = html;
        this.$('.pvm-more').innerHTML = this.page < this.last ? '<button type="button" class="pvm-btn">Load more</button>' : '';
        grid.onclick = function (e) {
            var fm = e.target.closest('[data-fm]'); if (fm) { e.stopPropagation(); return self.folderMenu(+fm.dataset.fm, { currentTarget: fm, clientX: e.clientX, clientY: e.clientY }); }
            var f = e.target.closest('.folder'); if (f) { self.folder = +f.dataset.fid; self.q = ''; self.$('.pvm-q').value = ''; return self.load(true); }
            var t = e.target.closest('.pvm-tile[data-i]'); if (!t) return;
            self.select(self.items[+t.dataset.i], e.ctrlKey || e.metaKey || e.shiftKey || (self.mode === 'manage' && e.target.closest('.chk')));
        };
        grid.oncontextmenu = function (e) { var f = e.target.closest('.folder'); if (f && self.mode === 'manage') { e.preventDefault(); self.folderMenu(+f.dataset.fid, { currentTarget: null, clientX: e.clientX, clientY: e.clientY }); } };
        grid.ondblclick = function (e) { var t = e.target.closest('.pvm-tile[data-i]'); if (t && self.mode === 'pick') { self.sel = {}; self.select(self.items[+t.dataset.i], false); self.confirm(); } };
        this.side();
    };
    Panel.prototype.select = function (it, additive) {
        var multi = this.mode === 'manage' || this.multiple, toggle = multi && (additive || this.mode === 'pick');
        if (!toggle) this.sel = {};
        if (toggle && this.sel[it.id]) delete this.sel[it.id]; else this.sel[it.id] = it;
        this.current = it;
        this.draw();
    };
    Panel.prototype.selected = function () { var s = this.sel; return Object.keys(s).map(function (k) { return s[k]; }); };
    Panel.prototype.side = function () {
        var self = this, side = this.$('.pvm-side'), chosen = this.selected();
        if (!chosen.length) { side.innerHTML = '<div class="pvm-empty">' + (this.mode === 'pick' ? 'Choose a file, or upload new ones' : 'Select a file to see its details') + '</div>' + (this.o.spec ? '<div class="pvm-spec">' + specLine(this.o.spec) + '</div>' : ''); return; }
        var it = chosen[chosen.length - 1], c = check(this.o.spec, it);
        var preview = it.type === 'image' ? '<img src="' + esc(it.url) + '" alt="">' : it.type === 'video' ? '<video src="' + esc(it.url) + '" controls preload="metadata"></video>' : it.type === 'audio' ? '<audio src="' + esc(it.url) + '" controls></audio>' : '<div class="ic big"><i class="bi ' + (ICON[it.type] || ICON.other) + '"></i></div>';
        var more = chosen.length > 1 ? '<div class="pvm-note">' + chosen.length + ' files selected</div>' : '';
        side.innerHTML = more + '<div class="pvm-prev">' + preview + '</div>' +
            '<div class="pvm-meta"><b>' + esc(it.file) + '</b><div>' + esc(it.date || '') + ' · ' + esc(it.size) + (it.w ? ' · ' + it.w + '×' + it.h + ' px' : '') + '</div></div>' +
            (c ? '<div class="pvm-fit ' + (c.ok ? 'ok' : 'warn') + '"><i class="bi ' + (c.ok ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill') + '"></i> ' + esc(c.text) + '</div>' : '') +
            (this.o.spec ? '<div class="pvm-spec">' + specLine(this.o.spec) + '</div>' : '') +
            (this.o.needsPath && !it.path ? '<div class="pvm-fit warn"><i class="bi bi-exclamation-triangle-fill"></i> This file is kept in an old module folder. Upload it again (or pick another) to use it here.</div>' : '') +
            (this.mode === 'manage' ? '<label>File name<input class="pvm-fname" value="' + esc(it.file) + '"></label>' : '') + '<label>Title<input class="pvm-title" value="' + esc(it.name) + '"></label>' + (it.type === 'image' ? '<label>Alt text<input class="pvm-alt" value="' + esc(it.alt || '') + '" placeholder="Describe the picture"></label>' : '') +
            '<label>Address<span class="pvm-url"><input readonly value="' + esc(location.origin + it.url) + '"><button type="button" class="pvm-btn pvm-copy" title="Copy"><i class="bi bi-clipboard"></i></button></span></label>' +
            '<div class="pvm-act">' + (this.mode === 'pick' ? '<button type="button" class="pvm-btn pri pvm-use">Use ' + (chosen.length > 1 ? chosen.length + ' files' : 'this file') + '</button>' : '') +
            '<button type="button" class="pvm-btn pvm-save">Save details</button>' +
            (this.mode === 'manage' ? '<button type="button" class="pvm-btn pvm-move"><i class="bi bi-folder-symlink"></i> Move to…</button><button type="button" class="pvm-btn pvm-copy2"><i class="bi bi-files"></i> Copy to…</button>' : '') +
            '<button type="button" class="pvm-btn red pvm-del"><i class="bi bi-trash"></i> Delete</button></div>';
        var q = function (s) { return side.querySelector(s); };
        if (q('.pvm-use')) q('.pvm-use').onclick = function () { self.confirm(); };
        q('.pvm-copy').onclick = function () { var v = location.origin + it.url; (navigator.clipboard ? navigator.clipboard.writeText(v) : Promise.reject()).then(function () { q('.pvm-copy').innerHTML = '<i class="bi bi-check2"></i>'; }).catch(function () { prompt('Copy the address', v); }); };
        q('.pvm-save').onclick = function () { api('/' + it.id, { method: 'PUT', json: { title: q('.pvm-title').value, alt: q('.pvm-alt') ? q('.pvm-alt').value : '', name: q('.pvm-fname') ? q('.pvm-fname').value : null } }).then(function (j) { if (j._ok) { it.name = j.name; it.file = j.file; it.alt = j.alt; self.draw(); self.toast('Saved'); } }); };
        q('.pvm-del').onclick = function () { self.remove(chosen); };
        if (q('.pvm-move')) q('.pvm-move').onclick = function () { self.moveTo(chosen); };
        if (q('.pvm-copy2')) q('.pvm-copy2').onclick = function () { self.copyTo(chosen); };
    };
    Panel.prototype.confirm = function () { var c = this.selected(); if (c.length && this.o.onSelect) this.o.onSelect(this.multiple ? c : c[0]); };
    Panel.prototype.remove = function (chosen, opt) {
        var self = this; opt = opt || {};
        var go = function (o) {
            api('/delete', { method: 'POST', json: { ids: chosen.map(function (c) { return c.id; }), force: !!o.force, permanent: !!o.permanent, confirm: 'DELETE' } }).then(function (j) {
                if (j._status === 409) { return ask.confirm({ title: 'Used on the website', message: j.message, confirm: 'Delete anyway', danger: true }).then(function (y) { if (y) go({ force: true, permanent: o.permanent }); }); }
                if (j._status === 403) return alert(j.message || 'Only an administrator can do this.');
                if (j._ok) { self.sel = {}; self.toast(j.permanent ? j.deleted + ' file' + (j.deleted === 1 ? '' : 's') + ' deleted for good.' : j.deleted + ' file' + (j.deleted === 1 ? '' : 's') + ' moved to the Trash - you can restore ' + (j.deleted === 1 ? 'it' : 'them') + ' there.'); self.load(true); }
            });
        };
        var what = chosen.length === 1 ? '"' + chosen[0].file + '"' : chosen.length + ' files';
        var options = [{ value: 'trash', label: 'Move to the Trash', desc: 'Can be restored to the same folder later.' }];
        if (PVMEDIA_ADMIN) options.push({ value: 'forever', label: 'Delete for good (administrator)', desc: 'Skips the Trash. The file is removed from the server and cannot be restored.', danger: true });
        ask.dlg({ title: 'Delete ' + what + '?', message: PVMEDIA_ADMIN ? 'Choose what happens, then type DELETE.' : what + ' will be moved to the Trash. You can restore ' + (chosen.length === 1 ? 'it' : 'them') + ' to the same folder later.',
            options: PVMEDIA_ADMIN ? options : null, typeToConfirm: 'DELETE', danger: true, confirm: '<i class="bi bi-trash"></i> Delete' }).then(function (v) {
            if (v === null) return;
            go({ permanent: v === 'forever' });
        });
    };
    Panel.prototype.moveTo = function (chosen) {
        var self = this, one = chosen.length === 1;
        choosePath({ title: 'Move ' + (one ? '"' + chosen[0].file + '"' : chosen.length + ' files') + ' to…', confirm: '<i class="bi bi-folder-symlink"></i> Move here', start: this.folder, nameLabel: one ? 'File name (change it to rename while moving)' : '', name: one ? chosen[0].file : '' })
            .then(function (r) { if (!r) return; api('/move', { method: 'POST', json: { ids: chosen.map(function (c) { return c.id; }), folder_id: r.folder_id, name: one && r.name !== chosen[0].file ? r.name : null } }).then(function (j) { if (j._ok) { self.sel = {}; self.toast('Moved to ' + j.path); self.load(true); } else alert(j.message || 'Could not move'); }); });
    };
    Panel.prototype.copyTo = function (chosen) {
        var self = this, one = chosen.length === 1;
        choosePath({ title: 'Copy ' + (one ? '"' + chosen[0].file + '"' : chosen.length + ' files') + ' to…', confirm: '<i class="bi bi-files"></i> Copy here', start: this.folder, nameLabel: one ? 'Name of the copy' : '', name: one ? chosen[0].file : '' })
            .then(function (r) { if (!r) return; api('/copy', { method: 'POST', json: { ids: chosen.map(function (c) { return c.id; }), folder_id: r.folder_id, name: one && r.name !== chosen[0].file ? r.name : null } }).then(function (j) { if (j._ok) { self.toast(j.copied + ' file' + (j.copied === 1 ? '' : 's') + ' copied to ' + j.path); self.load(true); } else alert(j.message || 'Could not copy'); }); });
    };
    Panel.prototype.toast = function (t) { var p = this.$('.pvm-prog'); p.innerHTML = '<div class="pvm-ok">' + esc(t) + '</div>'; setTimeout(function () { p.innerHTML = ''; }, 5000); };
    /* the ⋯ menu of a folder tile */
    Panel.prototype.folderMenu = function (id, ev) {
        var self = this, f = (this.folders || []).filter(function (x) { return x.id === id; })[0]; if (!f) return;
        var excl = function (cb) { api('/tree').then(function (j) { var ids = []; (function find(n) { n.forEach(function (x) { if (x.id === id) { (function all(y) { ids.push(y.id); y.children.forEach(all); })(x); } else find(x.children); }); })(j.tree || []); cb(ids); }); };
        popMenu(ev, [
            { icon: 'bi-folder2-open', label: 'Open', run: function () { self.folder = id; self.load(true); } },
            { icon: 'bi-pencil', label: 'Rename', run: function () { ask.prompt({ title: 'Rename folder', message: 'Current path:\n' + 'Media library' + (self.trail || []).map(function (t) { return ' / ' + t.name; }).join('') + ' / ' + f.name, input: { label: 'New name', value: f.name, max: 120 }, confirm: 'Rename', icon: 'bi-pencil-fill' }).then(function (n) { if (n && n !== f.name) api('/folders/' + id, { method: 'PUT', json: { name: n } }).then(function (j) { if (!j._ok) alert(j.message || 'Could not rename'); else self.toast('Renamed. New path: ' + j.path); self.load(true); }); }); } },
            { icon: 'bi-folder-plus', label: 'New sub folder', run: function () { ask.prompt({ title: 'New sub folder', message: 'It will be created inside "' + f.name + '".', input: { label: 'Folder name', max: 120 }, confirm: '<i class="bi bi-folder-plus"></i> Create folder', icon: 'bi-folder-plus' }).then(function (n) { if (n) api('/folders', { method: 'POST', json: { name: n, parent_id: id } }).then(function (j) { if (!j._ok) alert(j.message || 'Could not create'); else self.toast('Folder created'); self.load(true); }); }); } },
            '-',
            { icon: 'bi-folder-symlink', label: 'Move to…', run: function () { excl(function (ids) { choosePath({ title: 'Move folder "' + f.name + '" to…', confirm: '<i class="bi bi-folder-symlink"></i> Move here', start: self.folder, exclude: ids, nameLabel: 'Folder name (change it to rename while moving)', name: f.name, nameRequired: true })
                .then(function (r) { if (!r) return; api('/folders/' + id, { method: 'PUT', json: { name: r.name, parent_id: r.folder_id } }).then(function (j) { if (!j._ok) alert(j.message || 'Could not move'); else self.toast('Folder moved. New path: ' + j.path); self.load(true); }); }); }); } },
            { icon: 'bi-files', label: 'Copy to…', run: function () { excl(function (ids) { choosePath({ title: 'Copy folder "' + f.name + '" (with everything in it) to…', confirm: '<i class="bi bi-files"></i> Copy here', start: self.folder, exclude: ids, nameLabel: 'Name of the copy (empty = same name)', name: '' })
                .then(function (r) { if (!r) return; self.toast('Copying…'); api('/folders/' + id + '/copy', { method: 'POST', json: { parent_id: r.folder_id, name: r.name || null } }).then(function (j) { if (!j._ok) alert(j.message || 'Could not copy'); else self.toast('Copied ' + j.folders + ' folder' + (j.folders === 1 ? '' : 's') + ' and ' + j.files + ' file' + (j.files === 1 ? '' : 's') + ' to ' + j.path); self.load(true); }); }); }); } },
            '-',
            { icon: 'bi-trash', label: 'Delete…', red: true, run: function () { self.deleteFolder(id, f); } }
        ]);
    };
    /** Delete a folder: ask what happens to what is inside. Nothing is lost - it goes to the Trash. */
    Panel.prototype.deleteFolder = function (id, f) {
        var self = this, options = [
            { value: 'keep', label: 'Delete only the folder', desc: 'Its files and sub folders move up one level.' },
            { value: 'all', label: 'Delete the folder and everything in it', desc: 'The folder, its sub folders and all files go to the Trash. You can restore them there.', danger: true }];
        if (PVMEDIA_ADMIN) options.push({ value: 'forever', label: 'Delete everything for good (administrator)', desc: 'Skips the Trash. The folder, its sub folders and all files are removed from the server for good.', danger: true });
        ask.dlg({ title: 'Delete the folder "' + f.name + '"?', message: 'It has ' + f.count + ' file' + (f.count === 1 ? '' : 's') + (f.children ? ' and ' + f.children + ' sub folder' + (f.children === 1 ? '' : 's') : '') + '. Choose what happens to them, then type DELETE.',
            danger: true, confirm: '<i class="bi bi-trash"></i> Delete', typeToConfirm: 'DELETE', options: options }).then(function (mode) { if (mode) self.deleteMode(id, mode === 'forever' ? 'all' : mode, false, mode === 'forever'); });
    };
    Panel.prototype.deleteMode = function (id, mode, force, permanent) {
        var self = this;
        api('/folders/' + id + '?mode=' + mode + '&confirm=DELETE' + (force ? '&force=1' : '') + (permanent ? '&permanent=1' : ''), { method: 'DELETE' }).then(function (j) {
            if (j._status === 409) { return ask.confirm({ title: 'Used on the website', message: j.message, confirm: 'Delete anyway', danger: true }).then(function (y) { if (y) self.deleteMode(id, mode, true, permanent); }); }
            if (j._status === 403) return alert(j.message || 'Only an administrator can do this.');
            if (j._ok) { self.toast(mode === 'all' ? (permanent ? 'Deleted for good: ' : 'Moved to the Trash: ') + j.deleted_folders + ' folder(s) and ' + j.deleted_files + ' file(s).' : 'Folder deleted. Its contents moved up.'); self.load(true); }
            else if (j.message) alert(j.message);
        });
    };
    Panel.prototype.upload = function (files) {
        var self = this, list = [].slice.call(files); if (!list.length) return;
        var prog = this.$('.pvm-prog'), done = [], errors = [], total = list.length, n = 0;
        function row() { prog.innerHTML = '<div class="pvm-up-row"><div class="bar"><i style="width:' + Math.round(n / total * 100) + '%"></i></div><span>Uploading ' + Math.min(n + 1, total) + ' of ' + total + '…</span></div>' + errors.map(function (e) { return '<div class="pvm-err">' + esc(e) + '</div>'; }).join(''); }
        row();
        (function next() {
            if (!list.length) {
                prog.innerHTML = errors.map(function (e) { return '<div class="pvm-err">' + esc(e) + '</div>'; }).join('');
                self.load(true).then(function () {
                    if (done.length) { self.sel = {}; done.forEach(function (it) { if (self.multiple || !Object.keys(self.sel).length) self.sel[it.id] = it; }); self.current = done[0]; self.draw(); if (self.o.onUploaded) self.o.onUploaded(done); }
                });
                return;
            }
            var f = list.shift(), fd = new FormData(); fd.append('files[]', f); if (self.folder) fd.append('folder_id', self.folder);
            var xhr = new XMLHttpRequest(); xhr.open('POST', BASE + '/upload'); xhr.setRequestHeader('X-CSRF-TOKEN', CSRF); xhr.setRequestHeader('Accept', 'application/json');
            xhr.onload = function () { var j = {}; try { j = JSON.parse(xhr.responseText); } catch (e) {} (j.items || []).forEach(function (it) { done.push(it); }); (j.errors || []).forEach(function (e) { errors.push(e); }); if (!j.items && !j.errors) errors.push('"' + f.name + '": the server refused it (' + xhr.status + ') - the file may be bigger than the server allows.'); n++; row(); next(); };
            xhr.onerror = function () { errors.push('"' + f.name + '": the connection broke.'); n++; row(); next(); };
            xhr.upload.onprogress = function (e) { if (e.lengthComputable) { prog.querySelector('.bar i').style.width = Math.round((n + e.loaded / e.total) / total * 100) + '%'; } };
            xhr.send(fd);
        })();
    };

    /* ------------------------------------------------------------------ the pick window */
    var panel, pending, lastFocus;
    function ensureDrawer() {
        if (drawerEl) return;
        drawerEl = h('<div class="pvm-drawer" role="dialog" aria-modal="true"><div class="pvm-dback"></div><aside class="pvm-dpanel" tabindex="-1">' +
            '<div class="hd"><h5 class="ttl">Choose or upload a file</h5><button type="button" class="pvm-dx" aria-label="Close"><i class="bi bi-x-lg"></i></button></div>' +
            '<div class="bd"><div class="pvm-host"></div></div></aside></div>');
        drawerEl.querySelector('.pvm-dback').onclick = closeDrawer;
        drawerEl.querySelector('.pvm-dx').onclick = closeDrawer;
        document.addEventListener('keydown', function (e) {   // Esc closes the drawer, unless a dialog on top of it is open (that one takes the key)
            if (e.key === 'Escape' && drawerEl.classList.contains('on') && !drawerEl.querySelector('.pvm-dlg, .pvm-pathwin, .pvm-pop')) closeDrawer();
        });
    }
    function closeDrawer() {
        if (!drawerEl) return;
        drawerEl.classList.remove('on'); document.documentElement.classList.remove('pvm-noscroll');
        if (lastFocus && lastFocus.focus) try { lastFocus.focus(); } catch (e) {}
    }
    var TYPE_MAP = { image: 'image', file: '', any: '', audio: 'audio', video: 'video' };
    function pick(o) {
        ensureDrawer();
        lastFocus = document.activeElement;
        // opened from inside a Bootstrap modal / drawer: sit inside it, so its focus trap lets the keyboard reach us
        var openers = document.querySelectorAll('.modal.show, .offcanvas.show');
        (openers.length ? openers[openers.length - 1] : document.body).appendChild(drawerEl);
        drawerEl.querySelector('.ttl').textContent = o.title || ({ image: 'Choose or upload a picture', audio: 'Choose or upload a voice / audio file', video: 'Choose or upload a video', file: 'Choose or upload a file' }[o.kind] || 'Choose or upload a file');
        var host = drawerEl.querySelector('.pvm-host'); host.innerHTML = '';
        var t = TYPE_MAP[o.kind || 'any'] || '';
        var accept = o.kind === 'image' ? 'image/*' : o.kind === 'audio' ? 'audio/*' : o.kind === 'video' ? 'video/*' : '';
        panel = new Panel(host, { mode: 'pick', type: t, lockType: !!t, multiple: !!o.multiple, spec: o.spec, accept: accept, needsPath: !!o.needsPath,
            onSelect: function (res) { closeDrawer(); o.onSelect && o.onSelect(res); } });
        drawerEl.classList.add('on'); document.documentElement.classList.add('pvm-noscroll');
        setTimeout(function () { var q = drawerEl.querySelector('.pvm-q'); if (q && window.innerWidth > 760) q.focus(); }, 320);
    }

    /* ------------------------------------------------------------------ native library field: <div class="pvm-field"> */
    function field(el) {
        if (el.dataset.ready) return; el.dataset.ready = 1;
        var name = el.dataset.name, kind = el.dataset.kind || 'image', spec = SPECS[el.dataset.spec] || SPECS[kind] || null;
        if (spec && el.dataset.hint) spec = Object.assign({}, spec, JSON.parse(el.dataset.hint));
        var cur = { path: el.dataset.value || '', url: el.dataset.url || '', name: el.dataset.label || '' };
        el.innerHTML = '<div class="pvm-fprev"></div><div class="pvm-fbody"><div class="pvm-fbtn"><button type="button" class="pvm-btn pri pvm-fpick"><i class="bi bi-images"></i> Choose from library / Upload</button> <button type="button" class="pvm-btn red pvm-frm" hidden><i class="bi bi-x-lg"></i> Remove</button></div><div class="pvm-fhint"></div><div class="pvm-ffit"></div></div>' +
            '<input type="hidden" name="' + name + '_media" value=""><input type="hidden" name="remove_' + name + '" value="">';
        var prev = el.querySelector('.pvm-fprev'), hint = el.querySelector('.pvm-fhint'), fit = el.querySelector('.pvm-ffit'), rm = el.querySelector('.pvm-frm');
        var inMedia = el.querySelector('[name="' + name + '_media"]'), inRemove = el.querySelector('[name="remove_' + name + '"]');
        hint.innerHTML = specLine(spec) + (el.dataset.required ? ' <span class="text-danger">· required</span>' : '');
        function draw(it) {
            var url = it ? it.url : cur.url, nm = it ? it.name : cur.name;
            if (!url && !cur.path && !it) { prev.innerHTML = '<div class="ph"><i class="bi ' + (ICON[kind === 'file' ? 'document' : kind] || ICON.other) + '"></i></div>'; rm.hidden = true; return; }
            var isImg = kind === 'image' || (it && it.type === 'image');
            prev.innerHTML = isImg ? '<img src="' + esc(url) + '" alt="">' : '<div class="ph file"><i class="bi ' + (ICON[it ? it.type : 'document'] || ICON.other) + '"></i><span>' + esc(nm || 'Current file') + '</span></div>';
            rm.hidden = false;
        }
        draw(null);
        el.querySelector('.pvm-fpick').addEventListener('click', function () {
            pick({ kind: kind, spec: spec, needsPath: true, onSelect: function (it) {
                if (!it.path) { fit.className = 'pvm-ffit warn'; fit.textContent = 'This file is kept in an old module folder - upload it again to use it here.'; return; }
                inMedia.value = it.path; inRemove.value = ''; draw(it);
                var c = check(spec, it); fit.className = 'pvm-ffit ' + (c ? (c.ok ? 'ok' : 'warn') : ''); fit.innerHTML = c ? '<i class="bi ' + (c.ok ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill') + '"></i> ' + esc(c.text) : (it.type !== 'image' ? esc(it.name) + ' (' + esc(it.size) + ')' : '');
            } });
        });
        rm.addEventListener('click', function () { inMedia.value = ''; inRemove.value = '1'; cur = { path: '', url: '', name: '' }; fit.textContent = ''; fit.className = 'pvm-ffit'; draw(null); });
    }

    /* ------------------------------------------------------------------ every plain file input in a form */
    var SKIP = /\.(csv|xlsx?|json|zip|sql)\b|text\/csv|spreadsheet/i;
    function enhance(input) {
        if (input.dataset.pvm || input.dataset.pvmSkip !== undefined || !input.form || input.closest('.pvm-modal, .pvm, .pvm-field')) return;
        if (SKIP.test(input.accept || '') || /import|csv|excel|backup|zip/i.test(input.name || '')) return;
        input.dataset.pvm = 1;
        var kind = /audio/i.test(input.accept) ? 'audio' : /video/i.test(input.accept) ? 'video' : (/image/i.test(input.accept) && !/pdf|doc/i.test(input.accept)) ? 'image' : (/image/i.test(input.accept) ? 'file' : 'any');
        var spec = SPECS[input.dataset.spec] || (kind === 'image' ? SPECS.image : kind === 'file' || kind === 'any' ? SPECS.file : SPECS[kind]) || null;
        var wrap = h('<div class="pvm-auto"><div class="pvm-chosen"><span class="pvm-none">No file chosen</span></div><div><button type="button" class="pvm-btn pri"><i class="bi bi-images"></i> Choose from library / Upload</button> <button type="button" class="pvm-btn red" hidden><i class="bi bi-x-lg"></i></button></div><div class="pvm-fhint">' + specLine(spec) + '</div><div class="pvm-ffit"></div></div>');
        input.style.display = 'none'; input.parentNode.insertBefore(wrap, input);
        var chosen = wrap.querySelector('.pvm-chosen'), btns = wrap.querySelectorAll('button'), fit = wrap.querySelector('.pvm-ffit');
        function show(files) {
            chosen.innerHTML = ''; fit.textContent = '';
            if (!files.length) { chosen.innerHTML = '<span class="pvm-none">No file chosen</span>'; btns[1].hidden = true; return; }
            [].forEach.call(files, function (f) {
                var item = document.createElement('div'); item.className = 'pvm-chip';
                if (/^image\//.test(f.type)) { var im = new Image(); im.src = URL.createObjectURL(f); im.onload = function () { var c = check(spec, { type: 'image', w: im.naturalWidth, h: im.naturalHeight, bytes: f.size, size: Math.round(f.size / 1024) + ' KB' }); if (c) { fit.className = 'pvm-ffit ' + (c.ok ? 'ok' : 'warn'); fit.innerHTML = '<i class="bi ' + (c.ok ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill') + '"></i> ' + esc(c.text); } }; item.appendChild(im); }
                else item.innerHTML = '<i class="bi bi-file-earmark"></i>';
                var s = document.createElement('span'); s.textContent = f.name; item.appendChild(s); chosen.appendChild(item);
            });
            btns[1].hidden = false;
        }
        btns[0].addEventListener('click', function () {
            pick({ kind: kind, spec: spec, multiple: input.multiple, onSelect: function (res) {
                var items = [].concat(res); btns[0].disabled = true; btns[0].textContent = 'Preparing…';
                Promise.all(items.map(function (it) { return fetch(it.url, { credentials: 'same-origin' }).then(function (r) { return r.blob(); }).then(function (b) { return new File([b], it.file || 'file.' + it.ext, { type: b.type || '' }); }); })).then(function (files) {
                    var dt = new DataTransfer(); files.forEach(function (f) { dt.items.add(f); }); input.files = dt.files;
                    input.dispatchEvent(new Event('change', { bubbles: true })); show(input.files);
                }).catch(function () { ask.notice('Could not read the chosen file.'); }).then(function () { btns[0].disabled = false; btns[0].innerHTML = '<i class="bi bi-images"></i> Choose from library / Upload'; });
            } });
        });
        btns[1].addEventListener('click', function () { input.value = ''; input.dispatchEvent(new Event('change', { bubbles: true })); show([]); });
        input.addEventListener('change', function () { show(input.files); });   // (file chosen another way)
    }

    function scan(root) {
        [].forEach.call((root || document).querySelectorAll('.pvm-field'), field);
        [].forEach.call((root || document).querySelectorAll('input[type=file]'), enhance);
    }
    window.PVMedia = { choosePath: choosePath, Panel: Panel, pick: pick, field: field, scan: scan, specLine: specLine, check: check };
    document.addEventListener('DOMContentLoaded', function () { scan(); new MutationObserver(function (ms) { ms.forEach(function (m) { [].forEach.call(m.addedNodes, function (n) { if (n.nodeType === 1) scan(n.parentNode || n); }); }); }).observe(document.body, { childList: true, subtree: true }); });
})();
