{{-- Our own CAPTCHA box. Hidden unless admin/security says this form needs one. Include inside a form: @include('partials.captcha', ['form' => 'otp'])
     The form sends captcha_id + captcha_answer (named inputs, or PvCap.fields(box) for fetch calls). --}}
<div class="pv-cap" data-form="{{ $form }}" hidden style="margin:10px 0">
    <div style="display:flex;align-items:center;gap:8px">
        <img alt="Security code" width="160" height="56" style="border-radius:8px;border:1px solid #d5dbe6;background:#fff">
        <button type="button" class="pv-cap-r" aria-label="New code" style="border:0;background:none;font-size:1.4rem;cursor:pointer">&#8635;</button>
    </div>
    <input type="hidden" name="captcha_id">
    <input name="captcha_answer" autocomplete="off" maxlength="8" placeholder="Type the code you see" style="margin-top:6px;width:100%;max-width:240px;text-transform:uppercase" class="form-control">
</div>
@once
<script>
    (function () {
        function load(box, d) { box.querySelector('img').src = d.image; box.querySelector('[name=captcha_id]').value = d.id; box.querySelector('[name=captcha_answer]').value = ''; box.hidden = false; }
        function fresh(box) { return fetch('{{ url('captcha') }}', { credentials: 'same-origin', headers: { Accept: 'application/json' } }).then(function (r) { return r.json(); }).then(function (d) { load(box, d); }); }
        window.PvCap = {
            fields: function (box) { box = box || document.querySelector('.pv-cap'); return !box || box.hidden ? {} : { captcha_id: box.querySelector('[name=captcha_id]').value, captcha_answer: box.querySelector('[name=captcha_answer]').value }; },
            again: function (data, box) { box = box || document.querySelector('.pv-cap'); if (box && data && data.captcha) load(box, data.captcha); },
            init: function (box) {
                box.querySelector('.pv-cap-r').addEventListener('click', function () { fresh(box); });
                fetch('{{ url('captcha/status') }}?form=' + encodeURIComponent(box.dataset.form), { credentials: 'same-origin', headers: { Accept: 'application/json' } })
                    .then(function (r) { return r.json(); }).then(function (s) { if (s.required) load(box, s.captcha); }).catch(function () {});
            }
        };
        document.addEventListener('DOMContentLoaded', function () { [].forEach.call(document.querySelectorAll('.pv-cap'), window.PvCap.init); });
    })();
</script>
@endonce
