{{-- Shared modal: send Stripe payment link from bookings list --}}
<div class="modal fade" id="modalSendPaymentLink" tabindex="-1" role="dialog" aria-labelledby="modalSendPaymentLinkTitle" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white border-0" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); padding: 1.1rem 1.35rem;">
                <div>
                    <h5 class="modal-title mb-0 font-weight-bold" id="modalSendPaymentLinkTitle">Send Stripe payment link</h5>
                    <small class="opacity-75" id="payment-link-modal-subtitle">Customer will receive a secure Stripe checkout link by email</small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-send-payment-link" method="post" action="#">
                @csrf
                <div class="modal-body">
                    <div id="payment-link-form-alert" class="alert alert-danger d-none" role="alert"></div>

                    <div class="rounded border mb-3 p-3" style="background: #f8fafc; border-color: #e2e8f0 !important;">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="small text-muted text-uppercase font-weight-bold" style="letter-spacing: .04em;">Reservation</div>
                                <div class="font-weight-bold text-dark" id="payment-link-booking-label">—</div>
                            </div>
                            <div class="text-right">
                                <div class="small text-muted text-uppercase font-weight-bold" style="letter-spacing: .04em;">Status</div>
                                <div class="font-weight-bold" id="payment-link-status-label">—</div>
                            </div>
                        </div>
                        <div class="small text-muted mb-0" id="payment-link-passenger-label"></div>
                    </div>

                    <div class="form-group">
                        <label for="payment_link_email" class="small font-weight-bold text-dark">Customer email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" id="payment_link_email" required placeholder="customer@example.com" autocomplete="email">
                        <small class="form-text text-muted">Prefilled from passenger/booker when available. You can edit before sending.</small>
                    </div>

                    <div class="form-group">
                        <label for="payment_link_customer_name" class="small font-weight-bold text-dark">Customer name</label>
                        <input type="text" class="form-control" name="customer_name" id="payment_link_customer_name" placeholder="Optional display name">
                    </div>

                    <div class="form-group">
                        <label for="payment_link_amount" class="small font-weight-bold text-dark">Amount (USD) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" class="form-control" name="amount" id="payment_link_amount" min="0.50" step="0.01" required>
                        </div>
                        <small class="form-text text-muted">Defaults to the reservation total. Changing this updates the booking total for this charge.</small>
                    </div>

                    <div class="form-group mb-0">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="small font-weight-bold text-dark mb-0" for="payment_link_message">Optional message</label>
                            <span class="badge badge-secondary" id="payment-link-msg-count">0 / 500</span>
                        </div>
                        <textarea class="form-control" name="personal_message" id="payment_link_message" rows="3" maxlength="500" placeholder="Optional note included in the email…"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-outline-secondary" id="btn-payment-link-cancel" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 font-weight-bold d-inline-flex align-items-center" id="btn-payment-link-send" style="background: linear-gradient(135deg, #0f3460, #16213e); border: none; min-width: 11rem;" aria-busy="false">
                        <span class="btn-payment-link-send__idle"><i class="ik ik-credit-card mr-1"></i> Send payment link</span>
                        <span class="btn-payment-link-send__busy d-none align-items-center"><i class="fa fa-spinner fa-spin mr-2" aria-hidden="true"></i> Sending…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="payment-link-toast" class="booking-composer-toast" role="status" aria-live="polite" aria-hidden="true" style="display: none;">
    <div class="booking-composer-toast__inner">
        <div class="booking-composer-toast__icon" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="booking-composer-toast__content">
            <div class="booking-composer-toast__title" id="payment-link-toast-title">Sent</div>
            <div class="booking-composer-toast__msg" id="payment-link-toast-msg"></div>
        </div>
        <button type="button" class="booking-composer-toast__close" id="payment-link-toast-close" aria-label="Dismiss">&times;</button>
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
.bookings-table .table-actions a.js-send-payment-link,
.booking-mobile-card .table-actions a.js-send-payment-link,
.disp-grid a.js-send-payment-link {
    color: #28a745;
}
.bookings-table .table-actions a.js-send-payment-link.is-disabled,
.booking-mobile-card .table-actions a.js-send-payment-link.is-disabled,
.disp-grid a.js-send-payment-link.is-disabled,
button.js-send-payment-link.is-disabled {
    color: #bcc1c6;
    opacity: 0.55;
    pointer-events: none;
    cursor: not-allowed;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('modalSendPaymentLink');
    var form = document.getElementById('form-send-payment-link');
    if (!modal || !form) return;

    var emailInput = document.getElementById('payment_link_email');
    var nameInput = document.getElementById('payment_link_customer_name');
    var amountInput = document.getElementById('payment_link_amount');
    var messageInput = document.getElementById('payment_link_message');
    var msgCount = document.getElementById('payment-link-msg-count');
    var bookingLabel = document.getElementById('payment-link-booking-label');
    var statusLabel = document.getElementById('payment-link-status-label');
    var passengerLabel = document.getElementById('payment-link-passenger-label');
    var alertBox = document.getElementById('payment-link-form-alert');
    var btnSend = document.getElementById('btn-payment-link-send');
    var btnCancel = document.getElementById('btn-payment-link-cancel');
    var idleEl = btnSend ? btnSend.querySelector('.btn-payment-link-send__idle') : null;
    var busyEl = btnSend ? btnSend.querySelector('.btn-payment-link-send__busy') : null;
    var toast = document.getElementById('payment-link-toast');
    var toastTitle = document.getElementById('payment-link-toast-title');
    var toastMsg = document.getElementById('payment-link-toast-msg');
    var toastClose = document.getElementById('payment-link-toast-close');
    var sendUrlTemplate = @json(route('bookings.send-payment-link', ['id' => '__ID__']));

    function setLoading(on) {
        if (!btnSend || !idleEl || !busyEl) return;
        btnSend.disabled = !!on;
        btnSend.setAttribute('aria-busy', on ? 'true' : 'false');
        if (btnCancel) btnCancel.disabled = !!on;
        idleEl.classList.toggle('d-none', !!on);
        busyEl.classList.toggle('d-none', !on);
        busyEl.classList.toggle('d-inline-flex', !!on);
    }

    function showAlert(msg) {
        if (!alertBox) return;
        if (!msg) {
            alertBox.classList.add('d-none');
            alertBox.textContent = '';
            return;
        }
        alertBox.textContent = msg;
        alertBox.classList.remove('d-none');
    }

    function showToast(title, message, isError) {
        if (!toast || !toastTitle || !toastMsg) return;
        toast.classList.toggle('booking-composer-toast--error', !!isError);
        toastTitle.textContent = title;
        toastMsg.textContent = message || '';
        toast.style.display = 'block';
        toast.setAttribute('aria-hidden', 'false');
        clearTimeout(showToast._t);
        showToast._t = setTimeout(function () {
            toast.style.display = 'none';
            toast.setAttribute('aria-hidden', 'true');
        }, 6500);
    }

    if (toastClose) {
        toastClose.addEventListener('click', function () {
            if (!toast) return;
            toast.style.display = 'none';
            toast.setAttribute('aria-hidden', 'true');
        });
    }

    if (messageInput && msgCount) {
        messageInput.addEventListener('input', function () {
            msgCount.textContent = (messageInput.value || '').length + ' / 500';
        });
    }

    document.querySelectorAll('.js-send-payment-link').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            if (btn.classList.contains('is-disabled')) return;

            var id = btn.getAttribute('data-booking-id');
            var publicId = btn.getAttribute('data-public-id') || id;
            var email = btn.getAttribute('data-email') || '';
            var name = btn.getAttribute('data-customer-name') || '';
            var amount = btn.getAttribute('data-amount') || '';
            var status = btn.getAttribute('data-status') || '';

            form.setAttribute('action', sendUrlTemplate.replace('__ID__', id));
            form.dataset.bookingId = id;
            if (bookingLabel) bookingLabel.textContent = '#' + publicId;
            if (statusLabel) statusLabel.textContent = status || 'Unknown';
            if (passengerLabel) passengerLabel.textContent = name ? ('Passenger: ' + name) : '';
            if (emailInput) emailInput.value = email;
            if (nameInput) nameInput.value = name;
            if (amountInput) amountInput.value = amount;
            if (messageInput) {
                messageInput.value = '';
                if (msgCount) msgCount.textContent = '0 / 500';
            }
            showAlert('');

            if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
                jQuery('#modalSendPaymentLink').modal('show');
            }
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (form.dataset.submitting === '1') return;
        form.dataset.submitting = '1';
        showAlert('');

        var url = form.getAttribute('action');
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
                showToast('Payment link sent', result.data.message || 'Email sent successfully.', false);
                if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
                    jQuery('#modalSendPaymentLink').modal('hide');
                }
            } else {
                var msg = (result.data && result.data.message) ? result.data.message : 'Could not send payment link.';
                if (result.data && result.data.errors) {
                    Object.keys(result.data.errors).forEach(function (k) {
                        var arr = result.data.errors[k];
                        if (arr && arr.length) msg = arr[0];
                    });
                }
                showAlert(msg);
                showToast('Something went wrong', msg, true);
            }
        }).catch(function () {
            showAlert('Network error. Please try again.');
            showToast('Network error', 'Please check your connection and try again.', true);
        }).finally(function () {
            form.dataset.submitting = '0';
            setLoading(false);
        });
    });
});
</script>
