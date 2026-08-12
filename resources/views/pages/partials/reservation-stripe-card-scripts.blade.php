@if(!empty($stripeEnabled))
<script>
window.initReservationStripeCardFlow = function (config) {
    config = config || {};
    if (typeof Stripe === 'undefined') return null;

    var form = document.getElementById('reservation-form');
    if (!form) return null;

    var stripe = Stripe(config.publishableKey || '');
    var card = null;
    var cardMounted = false;
    var cardEl = document.getElementById('reservation-card-element');

    function mountCardIfNeeded() {
        if (!cardEl || cardMounted) return;
        card = stripe.elements().create('card', { style: { base: { fontSize: '14px' } } });
        card.mount('#reservation-card-element');
        cardMounted = true;
    }

    var newCardEntry = document.getElementById('new-card-entry');
    if (newCardEntry && newCardEntry.style.display !== 'none') {
        mountCardIfNeeded();
    }

    var changeBtn = document.getElementById('btn-change-card');
    if (changeBtn) {
        changeBtn.addEventListener('click', function () {
            var savedBox = document.getElementById('saved-card-on-file');
            var pmInput = document.getElementById('payment_method_id');
            if (savedBox) savedBox.style.display = 'none';
            if (newCardEntry) newCardEntry.style.display = '';
            if (pmInput) pmInput.value = '';
            mountCardIfNeeded();
        });
    }

    async function createPaymentMethodFromCardFields() {
        var nameInput = document.getElementById('card-name-reservation');
        var errEl = document.getElementById('reservation-card-errors');
        if (errEl) errEl.textContent = '';

        if (!newCardEntry || newCardEntry.style.display === 'none') {
            return true;
        }

        if (!nameInput || !nameInput.value.trim()) {
            return true;
        }

        if (!cardMounted) {
            mountCardIfNeeded();
        }

        if (!card) return true;

        var pm = await stripe.createPaymentMethod({
            type: 'card',
            card: card,
            billing_details: { name: nameInput.value.trim() }
        });

        if (pm.error) {
            if (errEl) errEl.textContent = pm.error.message;
            return false;
        }

        var pmInput = document.getElementById('payment_method_id');
        if (pmInput) pmInput.value = pm.paymentMethod.id;
        return true;
    }

    form.addEventListener('submit', async function (e) {
        if (form.dataset.stripePmReady === '1') {
            form.dataset.stripePmReady = '0';
            return;
        }

        var submitter = e.submitter;
        var isPayButtonFlow = submitter && submitter.id === 'btn-reservation-pay';
        if (isPayButtonFlow) return;

        var needsPm = false;
        if (submitter && submitter.name === 'save_without_pay') {
            needsPm = true;
        } else if (newCardEntry && newCardEntry.style.display !== 'none') {
            var nameInput = document.getElementById('card-name-reservation');
            needsPm = !!(nameInput && nameInput.value.trim());
        }

        if (!needsPm) return;

        e.preventDefault();
        var ok = await createPaymentMethodFromCardFields();
        if (!ok) return;

        form.dataset.stripePmReady = '1';
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit(submitter || undefined);
        } else {
            form.submit();
        }
    });

    return { stripe: stripe, getCard: function () { return card; }, mountCardIfNeeded: mountCardIfNeeded };
};

window.resolveReservationPaymentMethodId = async function (stripe, card) {
    var pmInput = document.getElementById('payment_method_id');
    var newCardEntry = document.getElementById('new-card-entry');
    var errEl = document.getElementById('reservation-card-errors');
    if (errEl) errEl.textContent = '';

    if (pmInput && pmInput.value && (!newCardEntry || newCardEntry.style.display === 'none')) {
        return pmInput.value;
    }

    var nameInput = document.getElementById('card-name-reservation');
    if (!nameInput || !nameInput.value.trim()) {
        if (errEl) errEl.textContent = 'Enter the name on card.';
        return null;
    }

    if (!card) {
        if (errEl) errEl.textContent = 'Enter card details.';
        return null;
    }

    var pm = await stripe.createPaymentMethod({
        type: 'card',
        card: card,
        billing_details: { name: nameInput.value.trim() }
    });

    if (pm.error) {
        if (errEl) errEl.textContent = pm.error.message;
        return null;
    }

    if (pmInput) pmInput.value = pm.paymentMethod.id;
    return pm.paymentMethod.id;
};
</script>
@endif
