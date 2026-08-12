@php
    $savedCardOnFile = $savedCardOnFile ?? null;
    $isEditMode = !empty($isEditMode);
    $bookingPaymentStatus = strtolower(trim((string) ($bookingPaymentStatus ?? '')));
    $hasLockedPayment = in_array($bookingPaymentStatus, ['paid', 'authorized'], true);
    $canChargeOnEdit = $isEditMode && ! $hasLockedPayment;
    $showCardEntry = empty($stripeEnabled) ? false : ((! $isEditMode) || $canChargeOnEdit);
    $hasSavedCard = is_array($savedCardOnFile) && filled($savedCardOnFile['payment_method_id'] ?? null);
@endphp

@if(!empty($stripeEnabled))
    @if($isEditMode && $hasLockedPayment)
        <div class="reservation-saved-card-note alert alert-info small mb-0 py-2 px-2">
            Payment status: <strong>{{ ucfirst($bookingPaymentStatus ?: 'unknown') }}</strong>.
            Card on file cannot be changed from this screen.
        </div>
    @elseif($showCardEntry)
        <input type="hidden" name="payment_method_id" id="payment_method_id" value="{{ $hasSavedCard ? e($savedCardOnFile['payment_method_id']) : '' }}">

        @if($hasSavedCard)
            <div id="saved-card-on-file" class="reservation-saved-card-box mb-2">
                <div class="alert alert-success small mb-2 py-2 px-2 mb-0">
                    <strong>Card on file:</strong>
                    {{ $savedCardOnFile['brand'] ?? 'Card' }}
                    •••• {{ $savedCardOnFile['last4'] ?? '' }}
                    @if(!empty($savedCardOnFile['exp_month']) && !empty($savedCardOnFile['exp_year']))
                        <span class="text-muted">(exp {{ str_pad((string) $savedCardOnFile['exp_month'], 2, '0', STR_PAD_LEFT) }}/{{ $savedCardOnFile['exp_year'] }})</span>
                    @endif
                </div>
                <button type="button" class="btn btn-link btn-sm p-0 reservation-change-card-btn" id="btn-change-card">
                    Change card
                </button>
            </div>
        @endif

        <div id="new-card-entry" class="reservation-new-card-entry" @if($hasSavedCard) style="display:none;" @endif>
            <div class="la-right-field reservation-card-name-field">
                <label for="card-name-reservation">Name on card</label>
                <input type="text" id="card-name-reservation" class="form-control" autocomplete="cc-name" placeholder="As shown on card" value="{{ $hasSavedCard ? e($savedCardOnFile['name'] ?? '') : '' }}">
            </div>
            <div class="la-right-field reservation-card-element-field">
                <label>Card details</label>
                <div id="reservation-card-element" class="form-control"></div>
                <div id="reservation-card-errors" class="text-danger small mt-1"></div>
            </div>
        </div>
    @endif
@endif
