@php
    $p = $booking->passengers->first();
    $passengerEmail = $p->email ?? '';
    $bookerEmail = $booking->booker->email ?? '';
    $customerDefaults = [1 => '', 2 => $passengerEmail, 3 => $bookerEmail];
@endphp

<div class="modal fade" id="modalBookingEmailComposer" tabindex="-1" role="dialog" aria-labelledby="modalBookingEmailComposerTitle" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white border-0" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); padding: 1.1rem 1.35rem;">
                <div>
                    <h5 class="modal-title mb-0 font-weight-bold" id="modalBookingEmailComposerTitle">Send booking emails</h5>
                    <small class="opacity-75">Booking #{{ $booking->booking_id }} — PDF confirmation attached to each message</small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-booking-email-composer" method="post" action="{{ route('bookings.send-composer-emails', $booking->id) }}" data-send-url="{{ route('bookings.send-composer-emails', $booking->id) }}">
                @csrf
                <div class="modal-body p-0">
                    @if ($errors->any())
                        <div class="alert alert-danger m-3 mb-0 rounded">
                            <ul class="mb-0 pl-3 small">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- <div class="px-3 pt-3 pb-2">
                        <label class="small text-muted font-weight-bold text-uppercase mb-2" style="letter-spacing: .05em;">Template</label>
                        <select class="form-control rounded-lg border-secondary" disabled title="Reserved for future templates">
                            <option>Standard confirmation</option>
                        </select>
                    </div> -->

                    <div class="px-3 py-2">
                        <div class="rounded-lg border" style="border-color: #e2e8f0 !important;">
                            <!-- <div class="px-3 py-2 border-bottom font-weight-bold text-dark" style="background: #f8fafc;">
                                <i class="ik ik-users text-primary mr-1"></i> Customer-side recipients
                                <span class="badge badge-light border text-muted font-weight-normal ml-1">confirmation email</span>
                            </div> -->
                            <div class="p-2 p-md-3 bg-white">
                                @foreach ([
                                    1 => ['label' => 'B/C', 'hint' => 'Billing / company'],
                                    2 => ['label' => 'Pax', 'hint' => 'Passenger'],
                                    3 => ['label' => 'Bk/C', 'hint' => 'Booker / contact'],
                                ] as $idx => $meta)
                                    <div class="form-row align-items-center mb-2 mb-md-3">
                                        <div class="col-12 col-md-2 mb-1 mb-md-0">
                                            <span class="badge badge-pill font-weight-semibold" style="background: #eef2ff; color: #3730a3; font-size: 0.75rem;">{{ $meta['label'] }}</span>
                                            <div class="small text-muted d-none d-md-block" style="font-size: 10px;">{{ $meta['hint'] }}</div>
                                        </div>
                                        <div class="col-12 col-md-7 mb-1 mb-md-0">
                                            <input type="email"
                                                class="form-control form-control-sm rounded-lg @error('customer_email_'.$idx) is-invalid @enderror"
                                                name="customer_email_{{ $idx }}"
                                                id="customer_email_{{ $idx }}"
                                                value="{{ old('customer_email_'.$idx, $customerDefaults[$idx] ?? '') }}"
                                                placeholder="email@example.com"
                                                autocomplete="email">
                                        </div>
                                        <div class="col-12 col-md-3 text-md-right">
                                            <div class="custom-control custom-checkbox d-inline-block">
                                                <input type="checkbox" class="custom-control-input" name="customer_send_{{ $idx }}" id="customer_send_{{ $idx }}" value="1" {{ old('customer_send_'.$idx) ? 'checked' : '' }}>
                                                <label class="custom-control-label small font-weight-bold" for="customer_send_{{ $idx }}">Send</label>
                                            </div>
                                        </div>
                                    </div>
                                    @if ($idx < 3)
                                        <hr class="my-2 border-light">
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="px-3 py-2">
                        <div class="rounded-lg border" style="border-color: #e2e8f0 !important;">
                            <!-- <div class="px-3 py-2 border-bottom font-weight-bold text-dark" style="background: #fffbeb;">
                                <i class="ik ik-shield text-warning mr-1"></i> Admin-side recipients
                                <span class="badge badge-light border text-muted font-weight-normal ml-1">internal notification</span>
                            </div> -->
                            <div class="p-2 p-md-3 bg-white">
                                @foreach ([
                                    1 => ['label' => 'Aff', 'hint' => 'Affiliate / partner'],
                                    2 => ['label' => 'Drv 1', 'hint' => 'Driver'],
                                    3 => ['label' => 'Car 1', 'hint' => 'Fleet / car'],
                                ] as $idx => $meta)
                                    <div class="form-row align-items-center mb-2 mb-md-3">
                                        <div class="col-12 col-md-2 mb-1 mb-md-0">
                                            <span class="badge badge-pill font-weight-semibold" style="background: #fff7ed; color: #9a3412; font-size: 0.75rem;">{{ $meta['label'] }}</span>
                                            <div class="small text-muted d-none d-md-block" style="font-size: 10px;">{{ $meta['hint'] }}</div>
                                        </div>
                                        <div class="col-12 col-md-7 mb-1 mb-md-0">
                                            <input type="email"
                                                class="form-control form-control-sm rounded-lg @error('admin_email_'.$idx) is-invalid @enderror"
                                                name="admin_email_{{ $idx }}"
                                                id="admin_email_{{ $idx }}"
                                                value="{{ old('admin_email_'.$idx) }}"
                                                placeholder="email@example.com"
                                                autocomplete="email">
                                        </div>
                                        <div class="col-12 col-md-3 text-md-right">
                                            <div class="custom-control custom-checkbox d-inline-block">
                                                <input type="checkbox" class="custom-control-input" name="admin_send_{{ $idx }}" id="admin_send_{{ $idx }}" value="1" {{ old('admin_send_'.$idx) ? 'checked' : '' }}>
                                                <label class="custom-control-label small font-weight-bold" for="admin_send_{{ $idx }}">Send</label>
                                            </div>
                                        </div>
                                    </div>
                                    @if ($idx < 3)
                                        <hr class="my-2 border-light">
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="px-3 pb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="small font-weight-bold text-dark mb-0" for="personal_message">Include a personal message</label>
                            <span class="badge badge-secondary" id="personal-msg-count">0 / 500</span>
                        </div>
                        <textarea class="form-control rounded-lg" name="personal_message" id="personal_message" rows="4" maxlength="500" placeholder="Optional note shown in the email and PDF…">{{ old('personal_message') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-lg" id="btn-booking-email-cancel" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-lg px-4 font-weight-bold d-inline-flex align-items-center" id="btn-booking-email-send" style="background: linear-gradient(135deg, #0f3460, #16213e); border: none; min-width: 10rem;" aria-busy="false">
                        <span class="btn-booking-email-send__idle"><i class="ik ik-send mr-1"></i> Send emails</span>
                        <span class="btn-booking-email-send__busy d-none align-items-center"><i class="fa fa-spinner fa-spin mr-2" aria-hidden="true"></i> Sending…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="booking-composer-toast" class="booking-composer-toast" role="status" aria-live="polite" aria-hidden="true" style="display: none;">
    <div class="booking-composer-toast__inner">
        <div class="booking-composer-toast__icon" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="booking-composer-toast__content">
            <div class="booking-composer-toast__title" id="booking-composer-toast-title">Sent</div>
            <div class="booking-composer-toast__msg" id="booking-composer-toast-msg"></div>
        </div>
        <button type="button" class="booking-composer-toast__close" id="booking-composer-toast-close" aria-label="Dismiss">&times;</button>
    </div>
</div>

<style>
.booking-composer-toast {
    position: fixed;
    right: 1.25rem;
    bottom: 1.25rem;
    z-index: 100050;
    max-width: 22rem;
    animation: bookingComposerToastIn 0.38s cubic-bezier(0.22, 1, 0.36, 1);
    filter: drop-shadow(0 12px 28px rgba(15, 52, 96, 0.35));
}
@keyframes bookingComposerToastIn {
    from { opacity: 0; transform: translateY(12px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.booking-composer-toast__inner {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1rem 1rem 1rem 1.1rem;
    border-radius: 12px;
    background: linear-gradient(145deg, #ffffff 0%, #f4f7fb 100%);
    border: 1px solid rgba(15, 52, 96, 0.12);
    box-shadow: 0 4px 24px rgba(22, 33, 62, 0.12);
}
.booking-composer-toast--error .booking-composer-toast__inner {
    border-color: rgba(185, 28, 28, 0.2);
    background: linear-gradient(145deg, #fff5f5 0%, #fff 100%);
}
.booking-composer-toast__icon {
    flex-shrink: 0;
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
}
.booking-composer-toast--error .booking-composer-toast__icon {
    background: linear-gradient(135deg, #f87171, #dc2626);
}
.booking-composer-toast__title {
    font-weight: 700;
    font-size: 0.95rem;
    color: #0f172a;
    letter-spacing: 0.01em;
}
.booking-composer-toast__msg {
    font-size: 0.85rem;
    color: #475569;
    margin-top: 0.2rem;
    line-height: 1.45;
}
.booking-composer-toast__close {
    margin-left: auto;
    background: transparent;
    border: none;
    font-size: 1.35rem;
    line-height: 1;
    color: #94a3b8;
    padding: 0 0 0 0.5rem;
    cursor: pointer;
}
.booking-composer-toast__close:hover { color: #0f172a; }
</style>

@if (session('show_booking_email_composer') || $errors->any())
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
        jQuery('#modalBookingEmailComposer').modal('show');
    }
});
</script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    var ta = document.getElementById('personal_message');
    var badge = document.getElementById('personal-msg-count');
    function sync() {
        if (!ta || !badge) return;
        var n = (ta.value || '').length;
        badge.textContent = n + ' / 500';
    }
    if (ta) {
        ta.addEventListener('input', sync);
        sync();
    }

    var form = document.getElementById('form-booking-email-composer');
    var btnSend = document.getElementById('btn-booking-email-send');
    var btnCancel = document.getElementById('btn-booking-email-cancel');
    var idleEl = btnSend ? btnSend.querySelector('.btn-booking-email-send__idle') : null;
    var busyEl = btnSend ? btnSend.querySelector('.btn-booking-email-send__busy') : null;
    var toast = document.getElementById('booking-composer-toast');
    var toastTitle = document.getElementById('booking-composer-toast-title');
    var toastMsg = document.getElementById('booking-composer-toast-msg');
    var toastClose = document.getElementById('booking-composer-toast-close');

    function setLoading(on) {
        if (!btnSend || !idleEl || !busyEl) return;
        btnSend.disabled = !!on;
        btnSend.setAttribute('aria-busy', on ? 'true' : 'false');
        if (btnCancel) btnCancel.disabled = !!on;
        idleEl.classList.toggle('d-none', !!on);
        busyEl.classList.toggle('d-none', !on);
        busyEl.classList.toggle('d-inline-flex', !!on);
    }

    function showToast(title, message, isError) {
        if (!toast || !toastTitle || !toastMsg) return;
        toast.classList.toggle('booking-composer-toast--error', !!isError);
        toastTitle.textContent = title;
        toastMsg.textContent = message || '';
        toast.style.display = 'block';
        toast.setAttribute('aria-hidden', 'false');
        clearTimeout(showToast._t);
        showToast._t = setTimeout(function () { hideToast(); }, 6500);
    }

    function hideToast() {
        if (!toast) return;
        toast.style.display = 'none';
        toast.setAttribute('aria-hidden', 'true');
    }

    if (toastClose) toastClose.addEventListener('click', hideToast);

    if (form && btnSend) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (form.dataset.submitting === '1') return;
            form.dataset.submitting = '1';

            var url = form.getAttribute('data-send-url') || form.getAttribute('action');
            var fd = new FormData(form);
            setLoading(true);

            fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: fd,
                credentials: 'same-origin'
            }).then(function (res) {
                return res.json().then(function (data) {
                    return { ok: res.ok, status: res.status, data: data };
                }).catch(function () {
                    return { ok: false, status: res.status, data: { message: 'Unexpected response from server.' } };
                });
            }).then(function (result) {
                if (result.ok && result.data && result.data.success) {
                    showToast('All set', result.data.message || 'Emails sent successfully.', false);
                    if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
                        jQuery('#modalBookingEmailComposer').modal('hide');
                    }
                } else {
                    var msg = (result.data && result.data.message) ? result.data.message : 'Could not send emails.';
                    if (result.data && result.data.errors) {
                        var first = null;
                        Object.keys(result.data.errors).forEach(function (k) {
                            var arr = result.data.errors[k];
                            if (arr && arr.length && !first) first = arr[0];
                        });
                        if (first) msg = first;
                    }
                    showToast('Something went wrong', msg, true);
                }
            }).catch(function () {
                showToast('Network error', 'Please check your connection and try again.', true);
            }).finally(function () {
                form.dataset.submitting = '0';
                setLoading(false);
            });
        });
    }
});
</script>
