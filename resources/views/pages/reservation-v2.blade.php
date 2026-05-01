@extends('layouts.main')
@section('title', $pageTitle ?? 'Reservation')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@if(!empty($stripeEnabled))
<script src="https://js.stripe.com/v3/"></script>
@endif
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .reservation-v2-shell {
        max-width: 1240px;
        margin: 0 auto;
    }
    .reservation-v2-card {
        border: none;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 12px 38px rgba(15, 35, 60, 0.08);
        overflow: visible;
    }
    .reservation-v2-card .card-body {
        padding: 1.5rem;
    }
    .reservation-v2-intro {
        margin-bottom: 1.5rem;
    }
    .reservation-v2-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1b3552;
        margin-bottom: 0.35rem;
    }
    .reservation-v2-subtitle {
        color: #6c7a89;
        margin-bottom: 0;
    }
    .compact-group-title {
        font-size: 0.92rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #1e3a5f;
        margin: 0 0 1rem;
        padding-top: 0.25rem;
    }
    .reservation-v2-main-form {
        padding-bottom: 0.25rem;
    }
    .compact-group-payment {
        padding-top: 1.5rem;
        margin-top: 1.5rem;
        border-top: 1px solid #edf1f5;
    }
    .reservation-v2-card .form-group {
        margin-bottom: 1rem;
    }
    .reservation-v2-card .form-control,
    .reservation-v2-card .custom-select {
        min-height: 46px;
    }
    .reservation-v2-card textarea.form-control {
        min-height: 110px;
    }
    .account-details-box {
        border: 1px solid #e3e9f1;
        border-radius: 12px;
        background: #f8fbff;
        padding: 0.85rem 0.9rem 0.2rem;
        margin-bottom: 1rem;
    }
    .account-details-box .small-title {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #5f7083;
        font-weight: 700;
        margin-bottom: 0.75rem;
    }
    .account-readonly {
        background: #edf2f8 !important;
        color: #2c3e50;
        cursor: not-allowed;
    }
    .stops-block {
        border: 1px dashed #d3dbe6;
        border-radius: 10px;
        padding: 0.9rem 0.9rem 0.1rem;
        background: #fbfdff;
        margin-bottom: 1rem;
    }
    .inline-option-bar {
        min-height: 46px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem;
        padding: 0.65rem 0.9rem;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        background: #fff;
    }
    .inline-option-bar .custom-control {
        margin-right: 0;
    }
    /* Pair checkbox/radio rows with dropdowns: top-align so labels + controls line up */
    .reservation-v2-pair-row {
        align-items: flex-start !important;
    }
    .reservation-v2-pair-row > .form-group > label.d-block:first-of-type,
    .reservation-v2-pair-row > .form-group > .field-heading {
        margin-bottom: 0.5rem;
    }
    .compact-select {
        position: relative;
        width: 100%;
    }
    .compact-select-trigger {
        width: 100%;
        min-height: 46px;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        background: #fff;
        padding: 0.65rem 2.75rem 0.65rem 0.9rem;
        text-align: left;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        color: #212529;
    }
    .compact-select-trigger:focus {
        outline: 0;
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
    }
    .compact-select-value {
        flex: 1;
        min-width: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .compact-select-arrow {
        position: absolute;
        right: 0.95rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        pointer-events: none;
    }
    .compact-select.open .compact-select-arrow {
        transform: translateY(-50%) rotate(180deg);
    }
    .compact-select-panel {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        z-index: 1050;
        background: #fff;
        border: 1px solid #dfe6ee;
        border-radius: 12px;
        box-shadow: 0 12px 32px rgba(15, 35, 60, 0.16);
        overflow: hidden;
        opacity: 0;
        transform: translateY(-4px);
        pointer-events: none;
        transition: opacity .15s ease, transform .15s ease;
    }
    .compact-select.open .compact-select-panel {
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
    }
    .compact-select-search {
        width: 100%;
        border: 0;
        border-bottom: 1px solid #edf1f5;
        padding: 0.8rem 0.9rem;
        outline: 0;
    }
    .compact-select-list {
        max-height: 300px;
        overflow-y: auto;
        margin: 0;
        padding: 0.35rem 0;
        list-style: none;
    }
    .compact-select-option {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
        padding: 0.7rem 0.9rem;
        cursor: pointer;
        color: #1f2933;
    }
    .compact-select-option:hover,
    .compact-select-option.selected {
        background: #f4f8fc;
    }
    .compact-select-option-main {
        font-weight: 600;
        line-height: 1.2;
    }
    .compact-select-option-sub {
        font-size: 0.82rem;
        color: #6c757d;
        line-height: 1.3;
        margin-top: 0.15rem;
    }
    .location-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background: #fff;
        z-index: 10050;
        border: 1px solid #ddd;
        border-radius: 0 0 6px 6px;
        max-height: 300px;
        overflow-y: auto;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        display: none;
    }
    .suggestion-item {
        padding: 10px 15px;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .suggestion-item:last-child {
        border-bottom: none;
    }
    .suggestion-item:hover {
        background-color: #f8f9fa;
    }
    .suggestion-icon {
        color: #6c757d;
        flex-shrink: 0;
    }
    .suggestion-item .main-text {
        display: block;
        font-weight: 600;
        color: #333;
        font-size: 0.95rem;
    }
    .suggestion-item .sub-text {
        display: block;
        font-size: 0.85rem;
        color: #777;
    }
    .summary-box {
        min-height: 100%;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: linear-gradient(180deg, #f8fbff 0%, #f3f6fa 100%);
        padding: 1rem 1.1rem;
    }
    .summary-box-label {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #748091;
        margin-bottom: 0.35rem;
    }
    .summary-box-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1b3552;
        line-height: 1.2;
    }
    .summary-box-help {
        font-size: 0.88rem;
        color: #667281;
        margin-top: 0.45rem;
        margin-bottom: 0;
    }
    #reservation-card-element {
        padding: 0.75rem 1rem !important;
        min-height: 46px;
        background: #f4f6f9 !important;
        border: 1px solid rgba(0,0,0,0.15) !important;
        border-radius: 4px;
    }
    #card-name-reservation.form-control {
        min-height: 46px;
        background: #f4f6f9 !important;
    }
    #reservation-card-errors {
        min-height: 1.25rem;
    }
    .pac-container {
        z-index: 10050 !important;
    }
    @media (max-width: 767px) {
        .reservation-v2-card .card-body {
            padding: 1.1rem;
        }
        .reservation-v2-title {
            font-size: 1.45rem;
        }
    }
</style>
@endpush

@section('content')
@php
    $formDefaults = $formDefaults ?? [];
    $formAction = $formAction ?? route('reservation.store');
    $formMethod = strtoupper($formMethod ?? 'POST');
    $isEditMode = (bool) ($isEditMode ?? false);
    $hasOldInput = session()->hasOldInput();
    $formValue = function ($key, $default = null) use ($formDefaults, $hasOldInput) {
        return $hasOldInput ? old($key) : data_get($formDefaults, $key, $default);
    };
    $formBool = function ($key, $default = false) use ($formValue) {
        return in_array($formValue($key, $default), [true, 1, '1', 'true', 'on', 'yes'], true);
    };
    $pickupFlightOld = $formValue('pickup_flight_details');
    $meetOld = $formValue('meet_option');
    $serviceOptionOld = $formValue('service_option');
    if ($serviceOptionOld === null) {
        $serviceOptionOld = $formValue('service_type') === 'hourlyHire' ? 'hourly_as_directed' : 'point_to_point';
    }
    $selectedVehicle = $vehicles->firstWhere('id', (int) $formValue('vehicle_id'));
    $selectedVehicleLabel = $selectedVehicle
        ? $selectedVehicle->vehicle_name . ' • ' . $selectedVehicle->number_of_passengers . ' pax • ' . $selectedVehicle->luggage_capacity . ' bags'
        : 'Select vehicle';
    $selectedAccount = ($accounts ?? collect())->firstWhere('id', (int) $formValue('account_id'));
    $selectedAccountLabel = $selectedAccount
        ? ($selectedAccount->company_name . ($selectedAccount->company_number ? ' (' . $selectedAccount->company_number . ')' : ''))
        : 'Select account';
    $displayAccount = [
        'company_number' => $selectedAccount?->company_number ?? $formValue('account_company_number'),
        'company_name' => $selectedAccount?->company_name ?? $formValue('account_company_name'),
        'email' => $selectedAccount?->email ?? $formValue('account_company_email'),
        'phone' => $selectedAccount ? \App\Models\Account::formatUsPhone($selectedAccount->phone) : $formValue('account_company_phone'),
        'address' => $selectedAccount?->address ?? $formValue('account_company_address'),
        'billing_name' => $selectedAccount?->billingContact?->name ?? $formValue('account_billing_name'),
        'billing_email' => $selectedAccount?->billingContact?->email ?? $formValue('account_billing_email'),
        'billing_phone' => $selectedAccount?->billingContact ? \App\Models\Account::formatUsPhone($selectedAccount->billingContact->phone) : $formValue('account_billing_phone'),
    ];
    $bookingForSomeoneElse = $formBool('booking_for_someone_else');
    $stopLocations = $formValue('stop_locations', []);
    if (!is_array($stopLocations)) {
        $stopLocations = [];
    }
    $stopLocations = array_values(array_filter($stopLocations, fn($v) => is_string($v) && trim($v) !== ''));
    $returnServiceEnabled = $formBool('return_service');
    $flightDetailsEnabled = $formBool('no_flight_info', ! $isEditMode);
    $childSeatRequired = $formBool('child_seat_required');
    $bookingPaymentStatus = strtolower(trim((string) ($bookingPaymentStatus ?? '')));
    $paymentLockedStatuses = ['paid', 'authorized'];
    $hasLockedPayment = in_array($bookingPaymentStatus, $paymentLockedStatuses, true);
    $showPaymentSection = ! ($isEditMode && $hasLockedPayment);
    $canChargeOnEdit = $isEditMode && ! $hasLockedPayment;
@endphp

<div class="container-fluid reservation-v2-shell">
    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-lg-8">
                <h2 class="mb-1">{{ $pageTitle ?? 'Reservation' }}</h2>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <div class="card reservation-v2-card">
        <div class="card-body">
            <div class="reservation-v2-intro">
                <div class="reservation-v2-title">Reservation form</div>
            </div>

            <form method="post" action="{{ $formAction }}" id="reservation-form" novalidate>
                @csrf
                @if($formMethod !== 'POST')
                    @method($formMethod)
                @endif
                <input type="hidden" name="is_airport" id="is_airport" value="{{ $formBool('is_airport') ? '1' : '0' }}">

                <div class="reservation-v2-main-form">
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="account-select-trigger">Account (optional)</label>
                            <div class="compact-select" id="account-select">
                                <button type="button" class="compact-select-trigger" id="account-select-trigger" aria-haspopup="listbox" aria-expanded="false">
                                    <span class="compact-select-value">{{ $selectedAccountLabel }}</span>
                                </button>
                                <span class="compact-select-arrow"><i class="bi bi-chevron-down"></i></span>
                                <div class="compact-select-panel">
                                    <input type="text" class="compact-select-search" placeholder="Type to search accounts…" autocomplete="off" aria-label="Search accounts">
                                    <ul class="compact-select-list" role="listbox" tabindex="-1">
                                        <li
                                            class="compact-select-option {{ (string) $formValue('account_id') === '' ? 'selected' : '' }}"
                                            data-value=""
                                            data-label="Select account"
                                            role="option"
                                            aria-selected="{{ (string) $formValue('account_id') === '' ? 'true' : 'false' }}"
                                        >
                                            <div>
                                                <div class="compact-select-option-main">Select account</div>
                                            </div>
                                        </li>
                                        @foreach(($accounts ?? collect()) as $acc)
                                            @php
                                                $accMain = $acc->company_name . ($acc->company_number ? ' (' . $acc->company_number . ')' : '');
                                                $accSub = trim(collect([$acc->email, $acc->company_number ? '#' . $acc->company_number : null])->filter()->implode(' · '));
                                                $accFilter = mb_strtolower(trim(
                                                    ($acc->company_name ?? '') . ' '
                                                    . ($acc->company_number ?? '') . ' '
                                                    . ($acc->email ?? '') . ' '
                                                    . ($acc->billingContact?->name ?? '') . ' '
                                                    . ($acc->billingContact?->email ?? '')
                                                ), 'UTF-8');
                                            @endphp
                                            <li
                                                class="compact-select-option {{ (string) $formValue('account_id') === (string) $acc->id ? 'selected' : '' }}"
                                                data-value="{{ $acc->id }}"
                                                data-label="{{ $accMain }}"
                                                data-filter-text="{{ $accFilter }}"
                                                role="option"
                                                aria-selected="{{ (string) $formValue('account_id') === (string) $acc->id ? 'true' : 'false' }}"
                                            >
                                                <div>
                                                    <div class="compact-select-option-main">{{ $acc->company_name }}{{ $acc->company_number ? ' (' . $acc->company_number . ')' : '' }}</div>
                                                    @if($accSub !== '')
                                                        <div class="compact-select-option-sub">{{ $accSub }}</div>
                                                    @endif
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <input type="hidden" name="account_id" id="account_id" value="{{ $formValue('account_id') }}">
                            </div>
                            <small class="form-text text-muted">Pick an account to auto-fill company and billing contact details. Open the list and type to filter by name, number, or email.</small>
                        </div>
                    </div>

                    <div class="account-details-box {{ (string) $formValue('account_id', '') === '' ? 'd-none' : '' }}" id="account-details-box">
                        <div class="small-title">Account details</div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Company #</label>
                                <input type="text" class="form-control account-readonly" id="account_company_number_view" value="{{ $displayAccount['company_number'] }}" readonly>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Company name</label>
                                <input type="text" class="form-control account-readonly" id="account_company_name_view" value="{{ $displayAccount['company_name'] }}" readonly>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Company email</label>
                                <input type="text" class="form-control account-readonly" id="account_company_email_view" value="{{ $displayAccount['email'] }}" readonly>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Company phone</label>
                                <input type="text" class="form-control account-readonly" id="account_company_phone_view" value="{{ $displayAccount['phone'] }}" readonly>
                            </div>
                            <div class="form-group col-md-8">
                                <label>Company address</label>
                                <input type="text" class="form-control account-readonly" id="account_company_address_view" value="{{ $displayAccount['address'] }}" readonly>
                            </div>
                        </div>
                        <div class="small-title">Billing contact</div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Name</label>
                                <input type="text" class="form-control account-readonly" id="account_billing_name_view" value="{{ $displayAccount['billing_name'] }}" readonly>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Email</label>
                                <input type="text" class="form-control account-readonly" id="account_billing_email_view" value="{{ $displayAccount['billing_email'] }}" readonly>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Phone</label>
                                <input type="text" class="form-control account-readonly" id="account_billing_phone_view" value="{{ $displayAccount['billing_phone'] }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Passenger first name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="first_name" value="{{ $formValue('first_name') }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Passenger last name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="last_name" value="{{ $formValue('last_name') }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" value="{{ $formValue('email') }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Phone</label>
                            <input type="text" class="form-control" name="number" value="{{ $formValue('number') }}">
                        </div>
                    </div>

                    <div class="form-row align-items-end">
                        <div class="form-group col-md-12">
                            <label class="d-block">Reservation type</label>
                            <div class="inline-option-bar">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="booking_for_someone_else" name="booking_for_someone_else" value="1" @checked($bookingForSomeoneElse)>
                                    <label class="custom-control-label" for="booking_for_someone_else">Booking for someone else</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="booker-fields" class="{{ $bookingForSomeoneElse ? '' : 'd-none' }}">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Booker first name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="booker_first_name" value="{{ $formValue('booker_first_name') }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Booker last name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="booker_last_name" value="{{ $formValue('booker_last_name') }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Booker email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="booker_email" value="{{ $formValue('booker_email') }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Booker phone <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="booker_number" value="{{ $formValue('booker_number') }}">
                            </div>
                        </div>
                    </div>

                    @if(empty($googleMapsApiKey))
                        <div class="alert alert-warning">
                            Add <code>GOOGLE_MAPS_API_KEY</code> to your <code>.env</code> file and enable the <strong>Places API</strong> plus <strong>Distance Matrix API</strong>.
                        </div>
                    @endif

                    <div class="form-row">
                        <div class="form-group col-md-6 position-relative">
                            <label for="pickup_location">Pickup location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="pickup_location" name="pickup_location" value="{{ $formValue('pickup_location') }}" required placeholder="Address, airport, hotel..." autocomplete="off" spellcheck="false">
                            <div id="pickup-suggestions-reservation" class="location-suggestions" aria-live="polite"></div>
                        </div>
                        <div class="form-group col-md-6 position-relative" id="wrap-dropoff">
                            <label for="dropoff_location">Drop-off location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="dropoff_location" name="dropoff_location" value="{{ $formValue('dropoff_location') }}" placeholder="Address, airport, hotel..." autocomplete="off" spellcheck="false">
                            <div id="dropoff-suggestions-reservation" class="location-suggestions" aria-live="polite"></div>
                        </div>
                        <div class="form-group col-md-6 d-none" id="wrap-hours">
                            <label for="select_hours">Hours <span class="text-danger">*</span></label>
                            <select class="form-control" id="select_hours" name="select_hours">
                                @for ($h = 1; $h <= 24; $h++)
                                    <option value="{{ $h }}" @selected($formValue('select_hours', '3') == $h)>{{ $h }} hour(s)</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="stops-block" id="wrap-stops">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="mb-0 font-weight-bold">Stops (optional)</label>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-stop">
                                <i class="ik ik-plus"></i> Add stop
                            </button>
                        </div>
                        <div id="stops-container">
                            @foreach($stopLocations as $idx => $stop)
                                <div class="form-group position-relative stop-item" data-stop-index="{{ $idx + 1 }}">
                                    <label for="stop_location_{{ $idx + 1 }}" class="stop-label">Stop {{ $idx + 1 }}</label>
                                    <div class="d-flex" style="gap:8px;">
                                        <input
                                            type="text"
                                            class="form-control stop-location-input"
                                            id="stop_location_{{ $idx + 1 }}"
                                            name="stop_locations[]"
                                            value="{{ $stop }}"
                                            placeholder="Address, airport, hotel..."
                                            autocomplete="off"
                                            spellcheck="false"
                                        >
                                        <button type="button" class="btn btn-outline-danger btn-remove-stop" title="Remove stop">
                                            <i class="ik ik-x"></i>
                                        </button>
                                    </div>
                                    <div id="stop-suggestions-reservation-{{ $idx + 1 }}" class="location-suggestions" aria-live="polite"></div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="pickup_date">Pickup date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="pickup_date" name="pickup_date" value="{{ $formValue('pickup_date') }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pickup_time">Pickup time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="pickup_time" name="pickup_time" value="{{ $formValue('pickup_time') }}" required>
                        </div>
                    </div>

                    <div class="form-row align-items-end">
                        <div class="form-group col-md-6" id="wrap-return-trip">
                            <label class="d-block">Return trip</label>
                            <div class="inline-option-bar">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="return_service" name="return_service" value="1" @checked($returnServiceEnabled)>
                                    <label class="custom-control-label" for="return_service">Add a return trip</label>
                                </div>
                            </div>
                            <div id="return-fields" class="mt-3 {{ $returnServiceEnabled ? '' : 'd-none' }}">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="return_pickup_date">Return pick-up date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="return_pickup_date" name="return_pickup_date" value="{{ $formValue('return_pickup_date') }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="return_pickup_time">Return pick-up time <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" id="return_pickup_time" name="return_pickup_time" value="{{ $formValue('return_pickup_time') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="d-block">Flight details</label>
                            <div class="inline-option-bar">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="no-flight-info-checkbox" name="no_flight_info" value="1" @checked($flightDetailsEnabled)>
                                    <label class="custom-control-label" for="no-flight-info-checkbox">I have my flight details</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="outbound-flight-fields">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Pickup flight details</label>
                                <div class="compact-select" id="pickup-flight-select">
                                    <button type="button" class="compact-select-trigger" aria-haspopup="listbox" aria-expanded="false">
                                        <span class="compact-select-value">{{ $pickupFlightOld ? $pickupFlightOld : 'Select airline' }}</span>
                                    </button>
                                    <span class="compact-select-arrow"><i class="bi bi-chevron-down"></i></span>
                                    <div class="compact-select-panel">
                                        <input type="text" class="compact-select-search" placeholder="Type to search airlines..." autocomplete="off">
                                        <ul class="compact-select-list" role="listbox" tabindex="-1">
                                            <li class="compact-select-option {{ $pickupFlightOld === '' || $pickupFlightOld === null ? 'selected' : '' }}" data-value="" data-label="Select airline" role="option" aria-selected="{{ $pickupFlightOld === '' || $pickupFlightOld === null ? 'true' : 'false' }}">
                                                <div>
                                                    <div class="compact-select-option-main">Select airline</div>
                                                </div>
                                            </li>
                                            @foreach($airports ?? [] as $airport)
                                                @php
                                                    $displayValue = ($airport->iata_code ? $airport->iata_code . ' - ' : '') . $airport->name . ($airport->city ? ' (' . $airport->city . ')' : '');
                                                @endphp
                                                <li class="compact-select-option {{ $pickupFlightOld === $displayValue ? 'selected' : '' }}" data-value="{{ $displayValue }}" data-label="{{ $displayValue }}" role="option" aria-selected="{{ $pickupFlightOld === $displayValue ? 'true' : 'false' }}">
                                                    <div>
                                                        <div class="compact-select-option-main">{{ $displayValue }}</div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <input type="hidden" name="pickup_flight_details" id="pickup-flight-details" value="{{ $pickupFlightOld }}">
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="flight_number">Flight number</label>
                                <input type="text" class="form-control" id="flight_number" name="flight_number" value="{{ $formValue('flight_number') }}" placeholder="e.g. AA123">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Meet option</label>
                                <div class="compact-select" id="meet-option-select">
                                    <button type="button" class="compact-select-trigger" aria-haspopup="listbox" aria-expanded="false">
                                        <span class="compact-select-value">
                                            @if($meetOld === 'curbside')
                                                Curbside pickup
                                            @elseif($meetOld === 'inside')
                                                Inside pickup
                                            @else
                                                Select option
                                            @endif
                                        </span>
                                    </button>
                                    <span class="compact-select-arrow"><i class="bi bi-chevron-down"></i></span>
                                    <div class="compact-select-panel">
                                        <ul class="compact-select-list" role="listbox" tabindex="-1">
                                            <li class="compact-select-option {{ ($meetOld === null || $meetOld === '') ? 'selected' : '' }}" data-value="" data-label="Select option" role="option" aria-selected="{{ ($meetOld === null || $meetOld === '') ? 'true' : 'false' }}">
                                                <div>
                                                    <div class="compact-select-option-main">Select option</div>
                                                </div>
                                            </li>
                                            <li class="compact-select-option {{ $meetOld === 'curbside' ? 'selected' : '' }}" data-value="curbside" data-label="Curbside pickup" role="option" aria-selected="{{ $meetOld === 'curbside' ? 'true' : 'false' }}">
                                                <div>
                                                    <div class="compact-select-option-main">Curbside pickup</div>
                                                </div>
                                            </li>
                                            <li class="compact-select-option {{ $meetOld === 'inside' ? 'selected' : '' }}" data-value="inside" data-label="Inside pickup" role="option" aria-selected="{{ $meetOld === 'inside' ? 'true' : 'false' }}">
                                                <div>
                                                    <div class="compact-select-option-main">Inside pickup</div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                    <input type="hidden" name="meet_option" id="meet-option" value="{{ $meetOld === 'curbside' || $meetOld === 'inside' ? $meetOld : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="note">Trip notes</label>
                            <input type="text" class="form-control" id="note" name="note" value="{{ $formValue('note') }}" placeholder="Special requests, luggage, gate...">
                        </div>
                    </div>

                    <div class="form-row reservation-v2-pair-row">
                        <div class="form-group col-md-6 mb-0">
                            <span class="field-heading d-block font-weight-bold text-dark">Child seat</span>
                            <div class="inline-option-bar">
                                <div class="custom-control custom-checkbox mb-0">
                                    <input type="checkbox" class="custom-control-input" id="child_seat_required" name="child_seat_required" value="1" @checked($childSeatRequired)>
                                    <label class="custom-control-label font-weight-bold mb-0" for="child_seat_required">Child seat required</label>
                                </div>
                            </div>
                            <small class="form-text text-muted d-block mb-0" style="min-height: 1.25rem;">&nbsp;</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="field-heading d-block font-weight-bold text-dark" for="vehicle-select-trigger">Select vehicle <span class="text-danger">*</span></label>
                            <div class="compact-select" id="vehicle-select">
                                <button type="button" class="compact-select-trigger" id="vehicle-select-trigger" aria-haspopup="listbox" aria-expanded="false">
                                    <span class="compact-select-value">{{ $selectedVehicleLabel }}</span>
                                </button>
                                <span class="compact-select-arrow"><i class="bi bi-chevron-down"></i></span>
                                <div class="compact-select-panel">
                                    <input type="text" class="compact-select-search" placeholder="Search vehicle..." autocomplete="off">
                                    <ul class="compact-select-list" role="listbox" tabindex="-1">
                                        @foreach ($vehicles as $v)
                                            @php
                                                $vehicleLabel = $v->vehicle_name . ' • ' . $v->number_of_passengers . ' pax • ' . $v->luggage_capacity . ' bags';
                                            @endphp
                                            <li class="compact-select-option {{ (string) $formValue('vehicle_id') === (string) $v->id ? 'selected' : '' }}" data-value="{{ $v->id }}" data-label="{{ $vehicleLabel }}" role="option" aria-selected="{{ (string) $formValue('vehicle_id') === (string) $v->id ? 'true' : 'false' }}">
                                                <div>
                                                    <div class="compact-select-option-main">{{ $v->vehicle_name }}</div>
                                                    <div class="compact-select-option-sub">{{ $v->number_of_passengers }} passengers • {{ $v->luggage_capacity }} luggage</div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <input type="hidden" name="vehicle_id" id="vehicle-id" value="{{ $formValue('vehicle_id') }}">
                            </div>
                            <small class="form-text text-muted">Required. Optional custom total is in Payment.</small>
                        </div>
                    </div>
                    <div id="wrap-child-seat-fields" class="form-row {{ $childSeatRequired ? '' : 'd-none' }}">
                        <div class="form-group col-md-6">
                            <label for="child_seat_type">Seat type</label>
                            <select class="form-control" id="child_seat_type" name="child_seat_type">
                                <option value="" @selected($formValue('child_seat_type') === null || $formValue('child_seat_type') === '')>Select seat type…</option>
                                <option value="forward_toddler" @selected($formValue('child_seat_type') === 'forward_toddler')>Forward facing (Toddler)</option>
                                <option value="rear_infant" @selected($formValue('child_seat_type') === 'rear_infant')>Rear facing (Infant)</option>
                                <option value="booster" @selected($formValue('child_seat_type') === 'booster')>Booster seat</option>
                            </select>
                        </div>
                        <div id="wrap-child-seat-qty" class="form-group col-md-6 {{ $formValue('child_seat_type') ? '' : 'd-none' }}">
                            <label for="child_seat_quantity">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="child_seat_quantity" name="child_seat_quantity" min="1" max="20" step="1" value="{{ $formValue('child_seat_quantity') }}" placeholder="How many">
                            <small class="form-text text-muted">${{ number_format($childSeatPricePerSeatUsd ?? 20, 0) }} per seat × quantity is added to the total.</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="pax_count"># of passengers <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="pax_count" name="pax_count" min="1" max="99" step="1" value="{{ $formValue('pax_count', '1') }}" required placeholder="e.g. 2">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="luggage_count">Luggage</label>
                            <input type="number" class="form-control" id="luggage_count" name="luggage_count" min="0" max="99" step="1" value="{{ $formValue('luggage_count') }}" placeholder="Number of bags">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="service_option">Service type <span class="text-danger">*</span></label>
                            <select class="form-control" id="service_option" name="service_option" required>
                                <option value="from_airport" @selected($serviceOptionOld === 'from_airport')>From Airport</option>
                                <option value="to_airport" @selected($serviceOptionOld === 'to_airport')>To Airport</option>
                                <option value="point_to_point" @selected($serviceOptionOld === 'point_to_point')>Point to point</option>
                                <option value="hourly_as_directed" @selected($serviceOptionOld === 'hourly_as_directed')>Hourly / as directed</option>
                            </select>
                        </div>
                    </div>
                </div>

                @if($showPaymentSection)
                    <div class="compact-group-payment">
                        <h3 class="compact-group-title">Payment</h3>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="custom_total_price">Custom payment amount</label>
                                <input type="number" class="form-control" id="custom_total_price" name="custom_total_price" min="0.01" step="0.01" value="{{ $formValue('custom_total_price') }}" placeholder="Optional custom amount">
                                <small class="form-text text-muted">Leave blank to keep the system-calculated total based on the selected vehicle.</small>
                            </div>
                            <div class="form-group col-md-6">
                                <div class="summary-box">
                                    <div id="summary-child-seat-line" class="d-none mb-3 pb-3 border-bottom">
                                        <div class="d-flex justify-content-between align-items-baseline small">
                                            <span class="text-muted">Child seat add-on</span>
                                            <span class="font-weight-bold text-dark" id="summary-child-seat-amount">$0.00</span>
                                        </div>
                                    </div>
                                    <div class="summary-box-label">Total to charge</div>
                                    <div class="summary-box-value" id="summary-total-display">Calculated on submit</div>
                                    <p class="summary-box-help mb-0" id="summary-total-help">Enter a custom amount to override the calculated vehicle fare.</p>
                                </div>
                            </div>
                        </div>

                        @if($isEditMode)
                            @if($canChargeOnEdit && !empty($stripeEnabled))
                                <div class="alert alert-info small">
                                    This reservation is not paid yet. You can update the booking only, or charge the card and update the reservation in one step.
                                </div>
                                <input type="hidden" name="payment_method_id" id="payment_method_id" value="">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="card-name-reservation">Name on card <span class="text-danger">*</span></label>
                                        <input type="text" id="card-name-reservation" class="form-control" autocomplete="cc-name" placeholder="As shown on card">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Card details <span class="text-danger">*</span></label>
                                        <div id="reservation-card-element" class="form-control"></div>
                                        <div id="reservation-card-errors" class="text-danger small mt-1"></div>
                                    </div>
                                </div>
                            @elseif($canChargeOnEdit)
                                <div class="alert alert-info small">
                                    This reservation is not paid yet. Stripe is not configured, so only booking details can be updated from this screen.
                                </div>
                            @else
                                <div class="alert alert-info small">
                                    This reservation already has a paid or authorized payment. No new card charge can be created from this screen.
                                </div>
                            @endif
                            <div class="d-flex flex-wrap align-items-center" style="gap: 0.5rem;">
                                @if($canChargeOnEdit && !empty($stripeEnabled))
                                    <button type="button" class="btn btn-primary btn-lg" id="btn-reservation-pay">
                                        <span id="btn-reservation-text"><i class="ik ik-credit-card"></i> Pay &amp; update reservation</span>
                                        <span id="btn-reservation-spinner" class="spinner-border spinner-border-sm d-none ml-2 align-middle" role="status" aria-hidden="true"></span>
                                    </button>
                                @endif
                                <button type="submit" class="btn btn-success btn-lg" id="btn-reservation-update">
                                    <i class="ik ik-save"></i> Update reservation
                                </button>
                            </div>
                        @elseif(!empty($stripeEnabled))
                            <input type="hidden" name="payment_method_id" id="payment_method_id" value="">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="card-name-reservation">Name on card <span class="text-danger">*</span></label>
                                    <input type="text" id="card-name-reservation" class="form-control" autocomplete="cc-name" placeholder="As shown on card" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Card details <span class="text-danger">*</span></label>
                                    <div id="reservation-card-element" class="form-control"></div>
                                    <div id="reservation-card-errors" class="text-danger small mt-1"></div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap align-items-center" style="gap: 0.5rem;">
                                <button type="button" class="btn btn-success btn-lg" id="btn-reservation-pay">
                                    <span id="btn-reservation-text"><i class="ik ik-check"></i> Pay &amp; create reservation</span>
                                    <span id="btn-reservation-spinner" class="spinner-border spinner-border-sm d-none ml-2 align-middle" role="status" aria-hidden="true"></span>
                                </button>
                                <button type="submit" name="save_without_pay" value="1" class="btn btn-outline-secondary btn-lg" id="btn-save-without-pay" formnovalidate title="Save booking only — no charge, no emails">
                                    Save without pay
                                </button>
                            </div>
                        @else
                            <div class="alert alert-info small">
                                Add <code>STRIPE_KEY</code> and <code>STRIPE_SECRET</code> to <code>.env</code> to enable card authorization. Without Stripe, the booking is saved as <strong>Pending</strong>.
                            </div>
                            <div class="d-flex flex-wrap align-items-center" style="gap: 0.5rem;">
                                <button type="submit" class="btn btn-success btn-lg" id="btn-reservation-submit-fallback">
                                    <i class="ik ik-check"></i> Create reservation
                                </button>
                                <button type="submit" name="save_without_pay" value="1" class="btn btn-outline-secondary btn-lg" id="btn-save-without-pay-fallback" formnovalidate title="Save booking only — no payment record, no emails">
                                    Save without pay
                                </button>
                            </div>
                        @endif
                    </div>
                @elseif($isEditMode)
                    <div class="mt-4 d-flex flex-wrap align-items-center" style="gap: 0.5rem;">
                        <button type="submit" class="btn btn-success btn-lg" id="btn-reservation-update">
                            <i class="ik ik-save"></i> Update reservation
                        </button>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
@if(!empty($googleMapsApiKey))
<script>
window.initReservationPlaces = function () {
    if (typeof google === 'undefined' || !google.maps || !google.maps.places) {
        return;
    }

    function setAirportHidden(place, hiddenEl) {
        if (!hiddenEl) return;
        var isAirport = false;
        if (place.name && place.name.toLowerCase().indexOf('airport') !== -1) isAirport = true;
        if (place.types && place.types.indexOf('airport') !== -1) isAirport = true;
        var comps = place.address_components || [];
        for (var i = 0; i < comps.length; i++) {
            var c = comps[i];
            var t = c.types || [];
            for (var j = 0; j < t.length; j++) {
                if (t[j] === 'airport') {
                    isAirport = true;
                    break;
                }
            }
            var ln = (c.long_name || '').toLowerCase();
            var sn = (c.short_name || '').toLowerCase();
            if (ln.indexOf('airport') !== -1 || sn.indexOf('airport') !== -1) isAirport = true;
        }
        hiddenEl.value = isAirport ? '1' : '0';
    }

    function setupCustomAutocomplete(inputId, suggestionsListId, hiddenAirportFieldId, onSelectCallback) {
        var input = document.getElementById(inputId);
        var suggestionsContainer = document.getElementById(suggestionsListId);
        var hiddenAirport = hiddenAirportFieldId ? document.getElementById(hiddenAirportFieldId) : null;
        if (!input || !suggestionsContainer || input.getAttribute('data-places-bound') === '1') return;
        input.setAttribute('data-places-bound', '1');

        var autocompleteService = new google.maps.places.AutocompleteService();
        var placesService = new google.maps.places.PlacesService(document.createElement('div'));
        var debounceTimer = null;

        function selectPlace(place, displayText) {
            input.value = displayText || place.formatted_address || place.name || '';
            suggestionsContainer.style.display = 'none';
            if (hiddenAirport) setAirportHidden(place, hiddenAirport);
            if (typeof onSelectCallback === 'function') onSelectCallback(place);
        }

        input.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            var query = this.value.trim();
            if (hiddenAirport && query.length === 0) hiddenAirport.value = '0';

            if (query.length < 2) {
                suggestionsContainer.innerHTML = '';
                suggestionsContainer.style.display = 'none';
                return;
            }

            debounceTimer = setTimeout(function () {
                autocompleteService.getPlacePredictions({
                    input: query,
                    types: ['geocode', 'establishment'],
                    componentRestrictions: { country: 'us' }
                }, function (predictions, status) {
                    if (status !== google.maps.places.PlacesServiceStatus.OK || !predictions) {
                        suggestionsContainer.style.display = 'none';
                        return;
                    }

                    suggestionsContainer.innerHTML = '';
                    predictions.forEach(function (prediction) {
                        var sf = prediction.structured_formatting || {};
                        var mainTxt = sf.main_text || '';
                        var subTxt = sf.secondary_text || '';
                        var item = document.createElement('div');
                        item.className = 'suggestion-item';
                        item.setAttribute('tabindex', '0');
                        item.innerHTML = '<span class="suggestion-icon"><i class="bi bi-geo-alt" aria-hidden="true"></i></span>'
                            + '<div><span class="main-text">' + mainTxt + '</span><span class="sub-text">' + subTxt + '</span></div>';

                        item.addEventListener('click', function () {
                            placesService.getDetails({
                                placeId: prediction.place_id,
                                fields: ['formatted_address', 'name', 'address_components', 'types', 'geometry']
                            }, function (place, st) {
                                if (st === google.maps.places.PlacesServiceStatus.OK && place) {
                                    var displayText = subTxt ? mainTxt + ', ' + subTxt : mainTxt;
                                    selectPlace(place, displayText);
                                }
                            });
                        });

                        suggestionsContainer.appendChild(item);
                    });

                    suggestionsContainer.style.display = 'block';
                });
            }, 400);
        });

        document.addEventListener('click', function (e) {
            if (!input.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                suggestionsContainer.style.display = 'none';
            }
        });
    }

    setupCustomAutocomplete('pickup_location', 'pickup-suggestions-reservation', 'is_airport');
    setupCustomAutocomplete('dropoff_location', 'dropoff-suggestions-reservation', null);
    window.setupReservationPlacesAutocomplete = setupCustomAutocomplete;
};
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places&callback=initReservationPlaces" async defer></script>
@endif

<script>
(function () {
    var form = document.getElementById('reservation-form');
    if (!form) return;

    var tokenEl = document.querySelector('meta[name="csrf-token"]');
    var token = tokenEl ? tokenEl.getAttribute('content') : '';
    var reservationStripeEnabled = @json(!empty($stripeEnabled));
    var isEditMode = @json((bool) $isEditMode);
    var submitUrl = form.getAttribute('action') || @json(route('reservation.store'));
    var finalizeUrl = @json(route('reservation.finalize'));

    function serviceType() {
        var sel = document.getElementById('service_option');
        if (!sel) return 'pointToPoint';
        return sel.value === 'hourly_as_directed' ? 'hourlyHire' : 'pointToPoint';
    }

    function toggleReturnRequired(on) {
        ['return_pickup_date', 'return_pickup_time'].forEach(function (id) {
            var el = document.getElementById(id);
            if (!el) return;
            if (on) el.setAttribute('required', 'required');
            else el.removeAttribute('required');
        });
    }

    function toggleServiceUi() {
        var hourly = serviceType() === 'hourlyHire';
        var wrapDropoff = document.getElementById('wrap-dropoff');
        var wrapHours = document.getElementById('wrap-hours');
        var wrapReturn = document.getElementById('wrap-return-trip');
        var dropoff = document.getElementById('dropoff_location');
        var returnCheckbox = document.getElementById('return_service');
        var returnFields = document.getElementById('return-fields');
        var wrapStops = document.getElementById('wrap-stops');

        var hideDropoff = hourly && !isEditMode;
        if (wrapDropoff) wrapDropoff.classList.toggle('d-none', hideDropoff);
        if (wrapHours) wrapHours.classList.toggle('d-none', !hourly);

        if (dropoff) {
            if (hourly) dropoff.removeAttribute('required');
            else dropoff.setAttribute('required', 'required');
        }

        if (wrapReturn) wrapReturn.classList.toggle('d-none', hourly);
        if (wrapStops) wrapStops.classList.toggle('d-none', hourly);
        if (hourly && returnCheckbox) {
            returnCheckbox.checked = false;
            if (returnFields) returnFields.classList.add('d-none');
            toggleReturnRequired(false);
        }
    }

    function reindexStops() {
        var rows = Array.prototype.slice.call(document.querySelectorAll('#stops-container .stop-item'));
        rows.forEach(function (row, idx) {
            var n = idx + 1;
            row.setAttribute('data-stop-index', String(n));
            var label = row.querySelector('.stop-label');
            if (label) {
                label.textContent = 'Stop ' + n;
                label.setAttribute('for', 'stop_location_' + n);
            }
            var input = row.querySelector('.stop-location-input');
            if (input) input.id = 'stop_location_' + n;
            var suggest = row.querySelector('.location-suggestions');
            if (suggest) suggest.id = 'stop-suggestions-reservation-' + n;
        });
    }

    function bindStopAutocomplete(row) {
        var input = row ? row.querySelector('.stop-location-input') : null;
        var suggest = row ? row.querySelector('.location-suggestions') : null;
        if (!input || !suggest) return;
        if (window.setupReservationPlacesAutocomplete) {
            window.setupReservationPlacesAutocomplete(input.id, suggest.id, null);
        }
    }

    function makeStopRow(value) {
        var container = document.getElementById('stops-container');
        if (!container) return null;
        var next = container.querySelectorAll('.stop-item').length + 1;
        var wrap = document.createElement('div');
        wrap.className = 'form-group position-relative stop-item';
        wrap.setAttribute('data-stop-index', String(next));
        wrap.innerHTML =
            '<label for="stop_location_' + next + '" class="stop-label">Stop ' + next + '</label>' +
            '<div class="d-flex" style="gap:8px;">' +
                '<input type="text" class="form-control stop-location-input" id="stop_location_' + next + '" name="stop_locations[]" placeholder="Address, airport, hotel..." autocomplete="off" spellcheck="false">' +
                '<button type="button" class="btn btn-outline-danger btn-remove-stop" title="Remove stop"><i class="ik ik-x"></i></button>' +
            '</div>' +
            '<div id="stop-suggestions-reservation-' + next + '" class="location-suggestions" aria-live="polite"></div>';
        container.appendChild(wrap);
        var input = wrap.querySelector('.stop-location-input');
        if (input && typeof value === 'string') {
            input.value = value;
        }
        bindStopAutocomplete(wrap);
        return wrap;
    }

    function toggleFlightFields() {
        var cb = document.getElementById('no-flight-info-checkbox');
        var wrap = document.getElementById('outbound-flight-fields');
        if (!cb || !wrap) return;
        var on = cb.checked;
        wrap.style.display = on ? '' : 'none';
        wrap.querySelectorAll('input, button').forEach(function (el) {
            if (el.type !== 'hidden') el.disabled = !on;
        });
    }

    function syncBookingForSomeoneElse() {
        var toggle = document.getElementById('booking_for_someone_else');
        var wrap = document.getElementById('booker-fields');
        if (!toggle || !wrap) return;
        wrap.classList.toggle('d-none', !toggle.checked);
    }

    function syncReturnFields() {
        var toggle = document.getElementById('return_service');
        var wrap = document.getElementById('return-fields');
        if (!toggle || !wrap) return;
        wrap.classList.toggle('d-none', !toggle.checked);
        toggleReturnRequired(toggle.checked && serviceType() === 'pointToPoint');
    }

    function formatMoney(value) {
        return '$' + value.toFixed(2);
    }

    var CHILD_SEAT_PRICE_PER_SEAT_USD = @json((float) ($childSeatPricePerSeatUsd ?? 20));

    function getChildSeatFeeUsd() {
        var req = document.getElementById('child_seat_required');
        var type = document.getElementById('child_seat_type');
        var qtyInput = document.getElementById('child_seat_quantity');
        if (!req || !req.checked || !type || !type.value || !qtyInput) return 0;
        var q = parseInt(qtyInput.value, 10);
        if (isNaN(q) || q < 1) return 0;
        return CHILD_SEAT_PRICE_PER_SEAT_USD * q;
    }

    function syncChildSeatSummary() {
        var line = document.getElementById('summary-child-seat-line');
        var amtEl = document.getElementById('summary-child-seat-amount');
        var fee = getChildSeatFeeUsd();
        if (!line || !amtEl) return;
        if (fee > 0) {
            line.classList.remove('d-none');
            amtEl.textContent = formatMoney(fee);
        } else {
            line.classList.add('d-none');
        }
    }

    function syncChildSeatFieldsUi() {
        var req = document.getElementById('child_seat_required');
        var wrap = document.getElementById('wrap-child-seat-fields');
        var type = document.getElementById('child_seat_type');
        var qtyWrap = document.getElementById('wrap-child-seat-qty');
        var qty = document.getElementById('child_seat_quantity');
        if (!req || !wrap || !type || !qtyWrap || !qty) return;
        var on = req.checked;
        wrap.classList.toggle('d-none', !on);
        type.disabled = !on;
        var hasType = on && type.value;
        qtyWrap.classList.toggle('d-none', !hasType);
        qty.disabled = !hasType;
        if (hasType) {
            qty.setAttribute('required', 'required');
        } else {
            qty.removeAttribute('required');
        }
    }

    function syncDisplayedAmount() {
        var customAmountInput = document.getElementById('custom_total_price');
        var summaryEl = document.getElementById('summary-total-display');
        var helpEl = document.getElementById('summary-total-help');
        if (!customAmountInput || !summaryEl || !helpEl) return;

        syncChildSeatSummary();

        var raw = customAmountInput.value.trim();
        var parsed = parseFloat(raw);
        var childFee = getChildSeatFeeUsd();

        if (raw !== '' && !isNaN(parsed)) {
            summaryEl.textContent = formatMoney(parsed + childFee);
            helpEl.textContent = childFee > 0
                ? 'Custom trip amount plus child seat fee.'
                : 'Custom amount will be used for this reservation.';
            return;
        }

        if (childFee > 0) {
            summaryEl.textContent = 'Calculated trip + ' + formatMoney(childFee);
            helpEl.textContent = 'Vehicle fare is computed on submit; child seat fee is included in the total charged.';
        } else {
            summaryEl.textContent = 'Calculated on submit';
            helpEl.textContent = 'Leave custom amount blank to use the selected vehicle fare.';
        }
    }

    function updateAccountDetailsBoxVisibility() {
        var box = document.getElementById('account-details-box');
        var sel = document.getElementById('account_id');
        if (!box || !sel) return;
        var hasAccount = (sel.value || '').trim() !== '';
        box.classList.toggle('d-none', !hasAccount);
    }

    function setAccountDetailFields(data) {
        var map = {
            account_company_number_view: data.companyNumber || '',
            account_company_name_view: data.companyName || '',
            account_company_email_view: data.companyEmail || '',
            account_company_phone_view: data.companyPhone || '',
            account_company_address_view: data.companyAddress || '',
            account_billing_name_view: data.billingName || '',
            account_billing_email_view: data.billingEmail || '',
            account_billing_phone_view: data.billingPhone || ''
        };
        Object.keys(map).forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.value = map[id];
        });
        updateAccountDetailsBoxVisibility();
    }

    function syncAccountFromSelect() {
        var sel = document.getElementById('account_id');
        if (!sel) return;
        var selectedId = (sel.value || '').trim();
        if (!selectedId) {
            setAccountDetailFields({});
            return;
        }

        // Show the details panel and clear stale values while the row loads.
        setAccountDetailFields({});

        // Most reliable: fetch fresh details by id (works for native select + Select2 + AJAX mode).
        fetch('{{ url('/accounts') }}/' + encodeURIComponent(selectedId) + '/edit-data', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function (r) {
            if (!r.ok) throw new Error('Failed');
            return r.json();
        }).then(function (payload) {
            var a = payload && payload.account ? payload.account : null;
            if (!a) {
                setAccountDetailFields({});
                return;
            }
            setAccountDetailFields({
                companyNumber: a.company_number || '',
                companyName: a.company_name || '',
                companyEmail: a.email || '',
                companyPhone: a.phone || '',
                companyAddress: a.address || '',
                billingName: a.billing && a.billing.name ? a.billing.name : '',
                billingEmail: a.billing && a.billing.email ? a.billing.email : '',
                billingPhone: a.billing && a.billing.phone ? a.billing.phone : ''
            });
        }).catch(function () {
            setAccountDetailFields({});
        });
        return;

        // Prefer Select2-selected object data (works for AJAX-loaded options)
        if (window.jQuery && typeof window.jQuery.fn.select2 === 'function') {
            var s2 = window.jQuery(sel).select2('data');
            if (s2 && s2.length && s2[0].id) {
                var d = s2[0];
                var hasRichData = !!(d.company_name || d.company_email || d.company_phone || d.company_address || d.billing_name || d.billing_email || d.billing_phone || d.company_number);
                if (!hasRichData && d.element) {
                    hasRichData = !!(
                        d.element.getAttribute('data-company-name') ||
                        d.element.getAttribute('data-company-email') ||
                        d.element.getAttribute('data-company-phone') ||
                        d.element.getAttribute('data-company-address') ||
                        d.element.getAttribute('data-billing-name') ||
                        d.element.getAttribute('data-billing-email') ||
                        d.element.getAttribute('data-billing-phone') ||
                        d.element.getAttribute('data-company-number')
                    );
                }
                if (hasRichData) {
                    var el = d.element || null;
                    var getFromEl = function (attr) { return el ? (el.getAttribute(attr) || '') : ''; };
                    setAccountDetailFields({
                        companyNumber: d.company_number || getFromEl('data-company-number'),
                        companyName: d.company_name || getFromEl('data-company-name'),
                        companyEmail: d.company_email || getFromEl('data-company-email'),
                        companyPhone: d.company_phone || getFromEl('data-company-phone'),
                        companyAddress: d.company_address || getFromEl('data-company-address'),
                        billingName: d.billing_name || getFromEl('data-billing-name'),
                        billingEmail: d.billing_email || getFromEl('data-billing-email'),
                        billingPhone: d.billing_phone || getFromEl('data-billing-phone')
                    });
                    return;
                }
            }
        }

        var opt = sel.options[sel.selectedIndex];
        if (!opt || !opt.value) {
            setAccountDetailFields({});
            return;
        }
        setAccountDetailFields({
            companyNumber: opt.getAttribute('data-company-number') || '',
            companyName: opt.getAttribute('data-company-name') || '',
            companyEmail: opt.getAttribute('data-company-email') || '',
            companyPhone: opt.getAttribute('data-company-phone') || '',
            companyAddress: opt.getAttribute('data-company-address') || '',
            billingName: opt.getAttribute('data-billing-name') || '',
            billingEmail: opt.getAttribute('data-billing-email') || '',
            billingPhone: opt.getAttribute('data-billing-phone') || ''
        });
    }

    function initCompactSelect(rootId) {
        var root = document.getElementById(rootId);
        if (!root) return;

        var trigger = root.querySelector('.compact-select-trigger');
        var panel = root.querySelector('.compact-select-panel');
        var search = root.querySelector('.compact-select-search');
        var hidden = root.querySelector('input[type="hidden"]');
        var valueEl = root.querySelector('.compact-select-value');
        var options = Array.prototype.slice.call(root.querySelectorAll('.compact-select-option'));

        function visibleOptions() {
            return options.filter(function (option) {
                return option.style.display !== 'none';
            });
        }

        function open() {
            root.classList.add('open');
            trigger.setAttribute('aria-expanded', 'true');
            if (search) {
                search.value = '';
                filter('');
                search.focus();
            }
        }

        function close() {
            root.classList.remove('open');
            trigger.setAttribute('aria-expanded', 'false');
        }

        function selectOption(option) {
            options.forEach(function (item) {
                item.classList.remove('selected');
                item.setAttribute('aria-selected', 'false');
            });

            option.classList.add('selected');
            option.setAttribute('aria-selected', 'true');

            if (hidden) hidden.value = option.getAttribute('data-value') || '';
            if (valueEl) valueEl.textContent = option.getAttribute('data-label') || option.textContent.trim();
            if (hidden) hidden.dispatchEvent(new Event('change', { bubbles: true }));
            close();
        }

        function filter(query) {
            var normalized = (query || '').toLowerCase().trim();
            options.forEach(function (option) {
                var attr = option.getAttribute('data-filter-text');
                var haystack = (attr !== null && attr !== '' ? attr : option.textContent).toLowerCase();
                option.style.display = normalized === '' || haystack.indexOf(normalized) !== -1 ? '' : 'none';
            });
        }

        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            if (root.classList.contains('open')) close();
            else open();
        });

        options.forEach(function (option) {
            option.addEventListener('click', function () {
                selectOption(option);
            });
        });

        if (search) {
            search.addEventListener('input', function () {
                filter(this.value);
            });
            search.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    e.preventDefault();
                    close();
                    trigger.focus();
                }
                if (e.key === 'Enter') {
                    var visible = visibleOptions();
                    if (visible.length) {
                        e.preventDefault();
                        selectOption(visible[0]);
                    }
                }
            });
        }

        document.addEventListener('click', function (e) {
            if (!root.contains(e.target)) close();
        });

        if (panel) {
            panel.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    e.preventDefault();
                    close();
                    trigger.focus();
                }
            });
        }
    }

    function applyAccountCompactUiFromHidden() {
        var root = document.getElementById('account-select');
        var hidden = document.getElementById('account_id');
        if (!root || !hidden) return;
        var list = root.querySelector('.compact-select-list');
        var labelEl = root.querySelector('.compact-select-value');
        if (!list || !labelEl) return;
        var want = hidden.value !== null && hidden.value !== undefined ? String(hidden.value) : '';
        var label = 'Select account';
        Array.prototype.slice.call(list.querySelectorAll('.compact-select-option')).forEach(function (li) {
            var dv = li.getAttribute('data-value');
            dv = dv === null || dv === undefined ? '' : String(dv);
            var match = dv === want;
            li.classList.toggle('selected', match);
            li.setAttribute('aria-selected', match ? 'true' : 'false');
            if (match) {
                label = li.getAttribute('data-label') || li.textContent.trim() || label;
            }
        });
        labelEl.textContent = label;
    }

    function maybeHydrateReservationAccounts(done) {
        var root = document.getElementById('account-select');
        var list = root ? root.querySelector('.compact-select-list') : null;
        if (!root || !list) {
            done();
            return;
        }
        var hasAccounts = Array.prototype.slice.call(list.querySelectorAll('.compact-select-option')).some(function (li) {
            var v = li.getAttribute('data-value');
            return v !== null && String(v) !== '';
        });
        if (hasAccounts) {
            done();
            return;
        }
        var accountOptionsUrl = @json(route('reservation.account-options'));
        var join = accountOptionsUrl.indexOf('?') >= 0 ? '&' : '?';
        fetch(accountOptionsUrl + join + 'q=' + encodeURIComponent(''), {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        }).then(function (r) {
            return r.ok ? r.json() : { results: [] };
        }).then(function (payload) {
            var rows = Array.isArray(payload.results) ? payload.results : [];
            var hiddenEl = document.getElementById('account_id');
            var saved = hiddenEl && hiddenEl.value ? String(hiddenEl.value) : '';
            rows.forEach(function (row) {
                if (row == null || row.id == null || String(row.id) === '') {
                    return;
                }
                var main = row.text || row.company_name || ('Account #' + row.id);
                var subParts = [];
                if (row.company_email) subParts.push(String(row.company_email));
                if (row.company_number) subParts.push('#' + String(row.company_number));
                var sub = subParts.join(' · ');
                var filterBits = [
                    row.company_name,
                    row.company_number,
                    row.company_email,
                    row.billing_name,
                    row.billing_email,
                ].filter(function (x) {
                    return x != null && String(x).trim() !== '';
                }).join(' ').toLowerCase();
                var li = document.createElement('li');
                li.className = 'compact-select-option';
                li.setAttribute('role', 'option');
                li.setAttribute('data-value', String(row.id));
                li.setAttribute('data-label', main);
                li.setAttribute('data-filter-text', filterBits);
                if (saved && String(row.id) === saved) {
                    li.classList.add('selected');
                    li.setAttribute('aria-selected', 'true');
                } else {
                    li.setAttribute('aria-selected', 'false');
                }
                var wrap = document.createElement('div');
                var mainDiv = document.createElement('div');
                mainDiv.className = 'compact-select-option-main';
                mainDiv.textContent = main;
                wrap.appendChild(mainDiv);
                if (sub) {
                    var subDiv = document.createElement('div');
                    subDiv.className = 'compact-select-option-sub';
                    subDiv.textContent = sub;
                    wrap.appendChild(subDiv);
                }
                li.appendChild(wrap);
                list.appendChild(li);
            });
            Array.prototype.slice.call(list.querySelectorAll('.compact-select-option[data-value=""]')).forEach(function (blank) {
                if (saved) {
                    blank.classList.remove('selected');
                    blank.setAttribute('aria-selected', 'false');
                }
            });
            applyAccountCompactUiFromHidden();
        }).catch(function () {
            /* keep placeholder row only */
        }).finally(function () {
            done();
        });
    }

    var serviceOptionSelect = document.getElementById('service_option');
    if (serviceOptionSelect) {
        serviceOptionSelect.addEventListener('change', function () {
            toggleServiceUi();
            syncReturnFields();
        });
    }

    var bookingElseCheckbox = document.getElementById('booking_for_someone_else');
    if (bookingElseCheckbox) {
        bookingElseCheckbox.addEventListener('change', syncBookingForSomeoneElse);
    }

    var returnCheckbox = document.getElementById('return_service');
    if (returnCheckbox) {
        returnCheckbox.addEventListener('change', syncReturnFields);
    }

    var flightInfoCheckbox = document.getElementById('no-flight-info-checkbox');
    if (flightInfoCheckbox) {
        flightInfoCheckbox.addEventListener('change', toggleFlightFields);
    }

    var customAmountInput = document.getElementById('custom_total_price');
    if (customAmountInput) {
        customAmountInput.addEventListener('input', syncDisplayedAmount);
    }

    var childSeatReq = document.getElementById('child_seat_required');
    var childSeatType = document.getElementById('child_seat_type');
    var childSeatQty = document.getElementById('child_seat_quantity');
    if (childSeatReq) {
        childSeatReq.addEventListener('change', function () {
            syncChildSeatFieldsUi();
            syncDisplayedAmount();
        });
    }
    if (childSeatType) {
        childSeatType.addEventListener('change', function () {
            syncChildSeatFieldsUi();
            syncDisplayedAmount();
        });
    }
    if (childSeatQty) {
        childSeatQty.addEventListener('input', syncDisplayedAmount);
    }
    syncChildSeatFieldsUi();

    initCompactSelect('pickup-flight-select');
    initCompactSelect('meet-option-select');
    initCompactSelect('vehicle-select');
    maybeHydrateReservationAccounts(function () {
        initCompactSelect('account-select');
        var accountHidden = document.getElementById('account_id');
        if (accountHidden) {
            accountHidden.addEventListener('change', syncAccountFromSelect);
            applyAccountCompactUiFromHidden();
            syncAccountFromSelect();
        }
    });
    toggleServiceUi();
    syncBookingForSomeoneElse();
    syncReturnFields();
    toggleFlightFields();
    syncDisplayedAmount();
    syncAccountFromSelect();

    var addStopBtn = document.getElementById('btn-add-stop');
    var stopsContainer = document.getElementById('stops-container');
    if (addStopBtn && stopsContainer) {
        addStopBtn.addEventListener('click', function () {
            makeStopRow('');
        });

        stopsContainer.addEventListener('click', function (e) {
            var removeBtn = e.target.closest('.btn-remove-stop');
            if (!removeBtn) return;
            var row = removeBtn.closest('.stop-item');
            if (row) row.remove();
            reindexStops();
        });

        Array.prototype.slice.call(stopsContainer.querySelectorAll('.stop-item')).forEach(function (row) {
            bindStopAutocomplete(row);
        });
        reindexStops();
    }

    if (reservationStripeEnabled && typeof Stripe !== 'undefined') {
        var stripe = Stripe(@json($stripePublishableKey ?? ''));
        var elements = stripe.elements();
        var card = elements.create('card', { style: { base: { fontSize: '16px', color: '#32325d' } } });
        var cardEl = document.getElementById('reservation-card-element');
        if (cardEl) card.mount('#reservation-card-element');

        function setPayLoading(on) {
            var btn = document.getElementById('btn-reservation-pay');
            var sp = document.getElementById('btn-reservation-spinner');
            var tx = document.getElementById('btn-reservation-text');
            var defaultBtnHtml = btn ? (btn.getAttribute('data-default-html') || '') : '';
            if (btn) {
                btn.disabled = !!on;
                btn.setAttribute('aria-busy', on ? 'true' : 'false');
            }
            if (sp) sp.classList.toggle('d-none', !on);
            if (tx) {
                tx.innerHTML = on ? 'Processing payment...' : defaultBtnHtml;
            }
        }

        async function finalizeAfter3ds(bookingId) {
            var response = await fetch(finalizeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ booking_id: String(bookingId) })
            });

            var data = await response.json();
            if (data.success && data.redirect) {
                window.location.href = data.redirect;
                return;
            }

            alert(data.message || 'Could not finalize payment.');
            setPayLoading(false);
        }

        var payBtn = document.getElementById('btn-reservation-pay');
        if (payBtn) {
            var payBtnText = document.getElementById('btn-reservation-text');
            if (payBtnText) {
                payBtn.setAttribute('data-default-html', payBtnText.innerHTML);
            }
            payBtn.addEventListener('click', async function () {
                var errEl = document.getElementById('reservation-card-errors');
                if (errEl) errEl.textContent = '';

                var nameInput = document.getElementById('card-name-reservation');
                if (!nameInput || !nameInput.value.trim()) {
                    if (errEl) errEl.textContent = 'Enter the name on card.';
                    return;
                }

                if (!form.querySelector('input[name="vehicle_id"]').value) {
                    alert('Select a vehicle.');
                    return;
                }

                setPayLoading(true);

                var paymentMethodResult = await stripe.createPaymentMethod({
                    type: 'card',
                    card: card,
                    billing_details: { name: nameInput.value.trim() }
                });

                if (paymentMethodResult.error) {
                    if (errEl) errEl.textContent = paymentMethodResult.error.message;
                    setPayLoading(false);
                    return;
                }

                var formData = new FormData(form);
                formData.set('payment_method_id', paymentMethodResult.paymentMethod.id);

                try {
                    var response = await fetch(submitUrl, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    var raw = await response.text();
                    var data;
                    try {
                        data = JSON.parse(raw);
                    } catch (parseErr) {
                        alert('Unexpected server response. If the page was redirected, try again.');
                        setPayLoading(false);
                        return;
                    }

                    if (response.status === 422 && data.errors) {
                        alert(Object.values(data.errors).flat().join(' ') || data.message || 'Validation failed.');
                        setPayLoading(false);
                        return;
                    }

                    if (data.requires_action && data.payment_intent_client_secret) {
                        var confirmation = await stripe.confirmCardPayment(data.payment_intent_client_secret, {
                            return_url: data.return_url || window.location.href
                        });

                        if (confirmation.error) {
                            if (errEl) errEl.textContent = confirmation.error.message;
                            setPayLoading(false);
                            return;
                        }

                        await finalizeAfter3ds(data.booking_id);
                        return;
                    }

                    if (data.success && data.redirect) {
                        window.location.href = data.redirect;
                        return;
                    }

                    alert(data.message || 'Payment could not be completed.');
                } catch (e) {
                    alert(e.message || 'Request failed.');
                }

                setPayLoading(false);
            });
        }
    }
})();
</script>
@endpush
