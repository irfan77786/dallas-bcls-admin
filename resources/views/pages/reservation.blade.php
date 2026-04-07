@extends('layouts.main')
@section('title', 'Reservation')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@if(!empty($stripeEnabled))
<script src="https://js.stripe.com/v3/"></script>
@endif
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .reservation-layout { display: flex; gap: 1.5rem; flex-wrap: wrap; align-items: flex-start; }
    .reservation-steps {
        flex: 0 0 220px;
        position: sticky;
        top: 88px;
        background: linear-gradient(180deg, #1e3a5f 0%, #152a45 100%);
        border-radius: 12px;
        padding: 1rem 0.75rem;
        color: #e8eef5;
        box-shadow: 0 8px 24px rgba(15, 35, 60, 0.15);
    }
    .reservation-steps .step-label {
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        opacity: 0.75;
        margin-bottom: 0.35rem;
    }
    .reservation-steps a {
        display: block;
        padding: 0.5rem 0.65rem;
        border-radius: 8px;
        color: rgba(255,255,255,0.85);
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
        transition: background 0.15s, color 0.15s;
    }
    .reservation-steps a:hover { background: rgba(255,255,255,0.08); color: #fff; text-decoration: none; }
    .reservation-steps a.active { background: rgba(255,255,255,0.18); color: #fff; font-weight: 600; }
    .reservation-main { flex: 1; min-width: 0; }
    .section-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 18px rgba(15, 35, 60, 0.08);
        margin-bottom: 1.25rem;
        overflow: hidden;
    }
    .section-card .card-header {
        background: #f6f8fb;
        border-bottom: 1px solid #e8ecf1;
        font-weight: 600;
        font-size: 1rem;
        padding: 0.85rem 1.25rem;
    }
    .section-card .card-header span.badge-step {
        background: #3d5a80;
        color: #fff;
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        margin-right: 0.5rem;
        vertical-align: middle;
    }
    /* Match customer site vehicle rows (product_section) */
    .vehicle-pick-list { max-width: 100%; }
    .vehicle-option {
        display: block;
        cursor: pointer;
        position: relative;
        margin-bottom: 0;
        font-weight: normal;
    }
    .vehicle-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
        pointer-events: none;
    }
    .reservation-veh-card {
        border-bottom: 1px solid #8b8b8b;
        padding: 1rem 0.5rem;
        transition: background-color 0.2s, box-shadow 0.2s;
        border-radius: 0;
    }
    .vehicle-option:hover .reservation-veh-card { background-color: #fafafa; }
    .vehicle-option.selected .reservation-veh-card {
        background: linear-gradient(90deg, rgba(127, 88, 3, 0.06) 0%, #fff 40%);
        box-shadow: inset 4px 0 0 #7f5803;
    }
    .reservation-veh-img {
        max-height: 180px;
        width: 100%;
        object-fit: cover;
        border-radius: 0.5rem;
        background: #e9ecef;
    }
    .reservation-veh-card h5 { font-size: 1.15rem; font-weight: 700; color: #1e1e1e; margin-top: 0.5rem; }
    .reservation-veh-card .veh-desc {
        font-size: 0.9rem;
        line-height: 1.45;
        color: #444;
        margin-bottom: 0.5rem;
    }
    .reservation-veh-meta { font-size: 0.8125rem; color: #333; }
    .reservation-veh-meta i { margin-right: 4px; color: #555; }
    .reservation-feature-item {
        display: flex;
        align-items: flex-start;
        gap: 6px;
        margin-bottom: 8px;
        font-size: 0.875rem;
        line-height: 1.4;
        color: #1e1e1e;
    }
    .reservation-feature-item i { flex-shrink: 0; margin-top: 2px; }
    .reservation-car-price h4 {
        font-size: 1.35rem;
        font-weight: 700;
        color: #1a1a1a;
    }
    .reservation-car-price .price-decimal { font-size: 0.75em; opacity: 0.9; }
    .btn-reservation-select {
        background-color: #7f5803;
        color: #fff !important;
        border: none;
        font-weight: 600;
        letter-spacing: 0.03em;
        padding: 0.5rem 1.5rem;
        min-width: 140px;
        border-radius: 4px;
        display: inline-block;
        text-align: center;
        margin-top: 0.5rem;
    }
    .vehicle-option.selected .btn-reservation-select {
        background-color: #5c4102;
        box-shadow: 0 2px 8px rgba(127, 88, 3, 0.35);
    }
    .vehicle-option .price { min-height: 2.5rem; }
    .vehicle-option .price .err { color: #c0392b; font-size: 0.85rem; }
    .quote-toolbar { display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; }
    .summary-total {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e3a5f;
    }
    @media (max-width: 991px) {
        .reservation-steps { position: static; flex: 1 1 100%; display: flex; flex-wrap: wrap; gap: 0.35rem; }
        .reservation-steps a { flex: 1 1 auto; text-align: center; font-size: 0.8rem; padding: 0.45rem; }
    }
    /* Google Places dropdown above admin modals/sidebar (same idea as customer site) */
    .pac-container { z-index: 10050 !important; }
    /* Match public site home search (custom.js setupCustomAutocomplete) */
    .location-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background: #fff;
        z-index: 10050;
        border: 1px solid #ddd;
        border-radius: 0 0 4px 4px;
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
        justify-content: flex-start;
        gap: 10px;
    }
    .suggestion-item:last-child { border-bottom: none; }
    .suggestion-item:hover { background-color: #f8f9fa; }
    .suggestion-item .suggestion-icon { flex-shrink: 0; color: #6c757d; font-size: 1.1rem; }
    .suggestion-item .main-text { display: block; font-weight: 600; color: #333; font-size: 0.95rem; }
    .suggestion-item .sub-text { display: block; font-size: 0.85rem; color: #777; }
    #reservation-card-element {
        padding: 0.75rem 1rem !important;
        min-height: 46px;
        background: #f4f6f9 !important;
        border: 1px solid rgba(0,0,0,0.15) !important;
        border-radius: 4px;
    }
    #card-name-reservation.form-control { min-height: 46px; background: #f4f6f9 !important; }
    #reservation-card-errors { min-height: 1.25rem; }

    /* Searchable airline / meet dropdowns (same pattern as public booking_detail) */
    .rlx-select { position: relative; width: 100%; }
    .rlx-select .rlx-trigger {
        display: block; width: 100%; background: #fff; border: 1px solid #ced4da; border-radius: 4px;
        padding: 0.5rem 2.25rem 0.5rem 0.75rem; text-align: left; color: #212529; font-size: 0.9375rem;
        min-height: 38px; cursor: pointer;
    }
    .rlx-select .rlx-value { pointer-events: none; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .rlx-select .rlx-arrow {
        color: #6c757d; position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
        pointer-events: none;
    }
    .rlx-select.open .rlx-arrow { transform: translateY(-50%) rotate(180deg); }
    .rlx-select .rlx-list-wrapper {
        position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: #fff;
        border: 1px solid #dee2e6; border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,.12);
        max-height: 320px; overflow: hidden; z-index: 1050; display: flex; flex-direction: column;
        opacity: 0; transform: translateY(-4px); pointer-events: none;
        transition: opacity .15s ease, transform .15s ease;
    }
    .rlx-select.open .rlx-list-wrapper {
        opacity: 1; transform: translateY(0); pointer-events: auto;
    }
    .rlx-select .rlx-search {
        flex-shrink: 0; width: 100%; padding: 0.5rem 0.75rem; border: none; border-bottom: 1px solid #e9ecef;
        font-size: 0.9375rem; outline: none;
    }
    .rlx-select .rlx-list { max-height: 260px; overflow-y: auto; margin: 0; padding: 0.35rem 0; list-style: none; }
    .rlx-option {
        display: flex; align-items: center; padding: 0.5rem 0.75rem; font-size: 0.9375rem;
        cursor: pointer; color: #212529; margin: 0;
    }
    .rlx-option:hover, .rlx-option[aria-selected="true"] { background: #eef2f7; }
    .rlx-option.selected { background: #e7edf5; }
    .rlx-option-icon { flex-shrink: 0; width: 18px; height: 18px; margin-right: 8px; color: #6c757d; }
    .rlx-option-icon svg { width: 100%; height: 100%; display: block; }
    /* Meet option: list is direct child of .rlx-select (no search wrapper) */
    .rlx-select > .rlx-list {
        position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: #fff;
        border: 1px solid #dee2e6; border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,.12);
        max-height: 260px; overflow-y: auto; z-index: 1050; margin: 0; padding: 0.35rem 0; list-style: none;
        opacity: 0; pointer-events: none; transform: translateY(-4px);
        transition: opacity .15s ease, transform .15s ease;
    }
    .rlx-select.open > .rlx-list { opacity: 1; pointer-events: auto; transform: translateY(0); }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-lg-8">
                <h2 class="mb-1">New reservation</h2>
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

    <form method="post" action="{{ route('reservation.store') }}" id="reservation-form" novalidate>
        @csrf

        <div class="reservation-layout">
            <nav class="reservation-steps d-none d-lg-block" aria-label="Reservation sections">
                <div class="step-label">On this page</div>
                <a href="#section-ride" class="active">Ride info</a>
                <a href="#section-vehicle">Vehicle class</a>
                <a href="#section-contact">Passenger info</a>
                <a href="#section-details">Booking details</a>
                <a href="#section-payment">Payment</a>
            </nav>

            <div class="reservation-main">
                @php
                    $reservationFeatures = [
                        ['text' => 'Real-time updates for every flight', 'icon' => 'bi-airplane-fill'],
                        ['text' => 'Free 30-minute airport waiting time', 'icon' => 'bi-clock-fill'],
                        ['text' => 'Cancel without charge 24 hours prior', 'icon' => 'bi-x-circle-fill'],
                    ];
                @endphp

                <!-- 1 Ride info (matches website step 1 — no vehicle yet) -->
                <div class="card section-card" id="section-ride">
                    <div class="card-header"><span class="badge-step">1</span> Ride info</div>
                    <div class="card-body">
                        @if(empty($googleMapsApiKey))
                            <div class="alert alert-warning mb-3">
                                Add <code>GOOGLE_MAPS_API_KEY</code> to your <code>.env</code> file (same key as the customer site). Enable the <strong>Places API</strong> and <strong>Distance Matrix API</strong> for this key in Google Cloud Console.
                            </div>
                        @endif
                        <div class="form-group">
                            <label class="d-block font-weight-bold">Service type</label>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="service-ptp" name="service_type" value="pointToPoint" class="custom-control-input" @checked(old('service_type', 'pointToPoint') === 'pointToPoint')>
                                <label class="custom-control-label" for="service-ptp">Point-to-point</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="service-hourly" name="service_type" value="hourlyHire" class="custom-control-input" @checked(old('service_type') === 'hourlyHire')>
                                <label class="custom-control-label" for="service-hourly">Hourly hire</label>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12 position-relative">
                                <label for="pickup_location">Pickup location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="pickup_location" name="pickup_location" value="{{ old('pickup_location') }}" required placeholder="Address, airport, hotel…" autocomplete="off" spellcheck="false">
                                <div id="pickup-suggestions-reservation" class="location-suggestions" aria-live="polite"></div>
                            </div>
                            <div class="form-group col-md-12 position-relative" id="wrap-dropoff">
                                <label for="dropoff_location">Drop-off location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="dropoff_location" name="dropoff_location" value="{{ old('dropoff_location') }}" placeholder="Address, airport, hotel…" autocomplete="off" spellcheck="false">
                                <div id="dropoff-suggestions-reservation" class="location-suggestions" aria-live="polite"></div>
                            </div>
                            <div class="form-group col-md-12 d-none" id="wrap-hours">
                                <label for="select_hours">Hours <span class="text-danger">*</span></label>
                                <select class="form-control" id="select_hours" name="select_hours">
                                    @for ($h = 1; $h <= 24; $h++)
                                        <option value="{{ $h }}" @selected(old('select_hours', '3') == $h)>{{ $h }} hour(s)</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="pickup_date">Pickup date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="pickup_date" name="pickup_date" value="{{ old('pickup_date') }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="pickup_time">Pickup time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="pickup_time" name="pickup_time" value="{{ old('pickup_time') }}" required>
                            </div>
                        </div>

                        <div id="wrap-return-trip" class="border rounded p-3 mb-3 bg-light">
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="return_service" name="return_service" value="1" @checked(old('return_service'))>
                                <label class="custom-control-label font-weight-bold" for="return_service">Add a return trip</label>
                            </div>
                            <div id="return-fields" class="{{ old('return_service') ? '' : 'd-none' }}">
                               
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="return_pickup_date">Return pick-up date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="return_pickup_date" name="return_pickup_date" value="{{ old('return_pickup_date') }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="return_pickup_time">Return pick-up time <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" id="return_pickup_time" name="return_pickup_time" value="{{ old('return_pickup_time') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Public site uses a hidden is_airport from address detection, not a visible checkbox --}}
                        <input type="hidden" name="is_airport" id="is_airport" value="{{ old('is_airport') ? '1' : '0' }}">
                    </div>
                </div>

                <!-- 2 Vehicle class (website step 2) -->
                <div class="card section-card" id="section-vehicle">
                    <div class="card-header"><span class="badge-step">2</span> Vehicle class</div>
                    <div class="card-body">
                        <div class="quote-toolbar mb-3">
                            <button type="button" class="btn btn-primary" id="btn-quote">
                                <span id="btn-quote-text"><i class="ik ik-refresh-cw"></i> Calculate vehicle prices</span>
                                <span id="btn-quote-spinner" class="spinner-border spinner-border-sm d-none ml-2 align-middle" role="status" aria-hidden="true"></span>
                            </button>
                            <span class="text-muted small ml-2" id="quote-status"></span>
                        </div>

                        <label class="font-weight-bold d-block mb-2">Select vehicle <span class="text-danger">*</span></label>
                        <div class="vehicle-pick-list" id="vehicle-grid">
                            @foreach ($vehicles as $v)
                                <label class="vehicle-option mb-0" data-vid="{{ $v->id }}">
                                    <input type="radio" name="vehicle_id" value="{{ $v->id }}" @checked(old('vehicle_id') == $v->id)>
                                    <div class="reservation-veh-card row no-gutters align-items-center text-left">
                                        <div class="col-12 col-md-4 mb-3 mb-md-0 text-center text-md-left">
                                            @if ($v->vehicle_image)
                                                <img src="{{ asset('storage/' . $v->vehicle_image) }}" alt="{{ $v->vehicle_name }}" class="reservation-veh-img img-fluid" loading="lazy">
                                            @else
                                                <div class="reservation-veh-img d-flex align-items-center justify-content-center text-muted small" style="min-height:140px;background:#eceff4;">No image</div>
                                            @endif
                                        </div>
                                        <div class="col-12 col-md-4 px-md-3 mb-3 mb-md-0">
                                            <div class="d-flex flex-wrap reservation-veh-meta mb-2">
                                                <span class="mr-3 mb-1"><i class="bi bi-people-fill"></i> Max. {{ $v->number_of_passengers }}</span>
                                                <span class="mb-1"><i class="bi bi-bag-fill"></i> Max. {{ $v->luggage_capacity }}</span>
                                            </div>
                                            <h5 class="font-weight-bold">{{ $v->vehicle_name }}</h5>
                                            <p class="veh-desc mb-2">{{ \Illuminate\Support\Str::limit(strip_tags($v->description ?? ''), 180) }}</p>
                                            <div class="feature_items_cont">
                                                @foreach ($reservationFeatures as $feature)
                                                    <div class="reservation-feature-item">
                                                        <i class="bi {{ $feature['icon'] }}"></i>
                                                        <span>{{ $feature['text'] }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 pl-md-2 text-md-right">
                                            <div class="price text-md-right" data-price-for="{{ $v->id }}">
                                                <div class="price-hint text-muted small mb-2">Run <strong>Calculate vehicle prices</strong> first.</div>
                                                <div class="price-amount text-muted">—</div>
                                            </div>
                                            <div class="small text-muted mt-2 d-none d-md-block">
                                                <i class="bi bi-shield-check"></i> Trip price includes base fare, gratuity and tax
                                            </div>
                                            <span class="btn-reservation-select mt-2 d-inline-block">SELECT</span>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                       
                        <div id="reservation-price-breakdown" class="mt-3 p-3 border rounded bg-white d-none">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <span class="text-muted">Outbound (this vehicle)</span>
                                <span class="font-weight-bold" id="rb-outbound">—</span>
                            </div>
                            <div id="rb-return-row" class="d-none mt-2 pt-2 border-top">
                                <div class="d-flex justify-content-between align-items-baseline">
                                    <span class="text-muted">Return trip <span class="small">(drop-off → pick-up)</span></span>
                                    <span class="font-weight-bold" id="rb-return">—</span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-baseline mt-2 pt-2 border-top">
                                <span class="font-weight-bold">Estimated total</span>
                                <span class="font-weight-bold text-primary" id="rb-total" style="font-size:1.15rem;">—</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3 Passenger info (website step 3) -->
                <div class="card section-card" id="section-contact">
                    <div class="card-header"><span class="badge-step">3</span> Passenger info</div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Passenger first name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="first_name" value="{{ old('first_name') }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Passenger last name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="last_name" value="{{ old('last_name') }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Phone <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="number" value="{{ old('number') }}" required>
                            </div>
                        </div>
                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="booking_for_someone_else" name="booking_for_someone_else" value="1" @checked(old('booking_for_someone_else'))>
                            <label class="custom-control-label" for="booking_for_someone_else">Booking for someone else</label>
                        </div>
                        <div id="booker-fields" class="form-row {{ old('booking_for_someone_else') ? '' : 'd-none' }}">
                            <div class="form-group col-md-6">
                                <label>Booker first name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="booker_first_name" value="{{ old('booker_first_name') }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Booker last name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="booker_last_name" value="{{ old('booker_last_name') }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Booker email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="booker_email" value="{{ old('booker_email') }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Booker phone <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="booker_number" value="{{ old('booker_number') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4 Booking details (website step 4: flight + notes; return is modal on site — here optional section) -->
                <div class="card section-card" id="section-details">
                    <div class="card-header"><span class="badge-step">4</span> Booking details</div>
                    <div class="card-body">
                        <h6 class="text-uppercase text-muted small font-weight-bold mb-2">Flight information</h6>

                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="no-flight-info-checkbox" name="no_flight_info" value="1" @checked(old('no_flight_info', true))>
                            <label class="custom-control-label" for="no-flight-info-checkbox">I have my flight details</label>
                        </div>

                        @php
                            $pickupFlightOld = old('pickup_flight_details');
                            $meetOld = old('meet_option');
                        @endphp
                        <div id="outbound-flight-fields">
                            <div class="form-group fg-pickup-flight">
                                <label>Pickup flight details</label>
                                <div class="rlx-select rlx-select-searchable" id="rlx-pickup-flight" data-name="pickup_flight_details">
                                    <button type="button" class="rlx-trigger" aria-haspopup="listbox" aria-expanded="false">
                                        <span class="rlx-value">{{ $pickupFlightOld ? $pickupFlightOld : 'Select Airline' }}</span>
                                        <svg class="rlx-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6.73 9.27a1 1 0 0 1 1.41 0L12 13.12l3.86-3.85a1 1 0 0 1 1.41 1.41l-4.57 4.57a1 1 0 0 1-1.41 0L6.73 10.68a1 1 0 0 1 0-1.41Z" fill="currentColor"/></svg>
                                    </button>
                                    <div class="rlx-list-wrapper">
                                        <input type="text" class="rlx-search" placeholder="Type to search airlines…" autocomplete="off" aria-label="Search airlines" />
                                        <ul class="rlx-list" role="listbox" tabindex="-1">
                                            <li role="option" class="rlx-option {{ $pickupFlightOld === '' || $pickupFlightOld === null ? 'selected' : '' }}" aria-selected="{{ $pickupFlightOld === '' || $pickupFlightOld === null ? 'true' : 'false' }}" data-value="">
                                                <span class="rlx-option-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/></svg></span>
                                                <span class="rlx-option-text">Select Airline</span>
                                            </li>
                                            @foreach($airports ?? [] as $airport)
                                                @php
                                                    $displayValue = ($airport->iata_code ? $airport->iata_code . ' - ' : '') . $airport->name . ($airport->city ? ' (' . $airport->city . ')' : '');
                                                @endphp
                                                <li role="option"
                                                    class="rlx-option {{ $pickupFlightOld === $displayValue ? 'selected' : '' }}"
                                                    aria-selected="{{ $pickupFlightOld === $displayValue ? 'true' : 'false' }}"
                                                    data-value="{{ $displayValue }}">
                                                    <span class="rlx-option-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/></svg></span>
                                                    <span class="rlx-option-text">{{ $displayValue }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <input type="hidden" name="pickup_flight_details" id="pickup-flight-details" value="{{ $pickupFlightOld }}">
                                </div>
                            </div>
                            <div class="form-group fg-flight-number">
                                <label for="flight_number">Flight number</label>
                                <input type="text" class="form-control" id="flight_number" name="flight_number" value="{{ old('flight_number') }}" placeholder="e.g. AA123">
                            </div>
                            <div class="form-group fg-meet-option">
                                <label>Meet option</label>
                                <div class="rlx-select" id="rlx-meet-option" data-name="meet_option">
                                    <button type="button" class="rlx-trigger" aria-haspopup="listbox" aria-expanded="false">
                                        <span class="rlx-value">
                                            @if($meetOld === 'curbside') Curbside pickup
                                            @elseif($meetOld === 'inside') Inside pickup
                                            @else Select option
                                            @endif
                                        </span>
                                        <svg class="rlx-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6.73 9.27a1 1 0 0 1 1.41 0L12 13.12l3.86-3.85a1 1 0 0 1 1.41 1.41l-4.57 4.57a1 1 0 0 1-1.41 0L6.73 10.68a1 1 0 0 1 0-1.41Z" fill="currentColor"/></svg>
                                    </button>
                                    <ul class="rlx-list" role="listbox" tabindex="-1">
                                        <li role="option" class="rlx-option {{ ($meetOld === null || $meetOld === '') ? 'selected' : '' }}" aria-selected="{{ ($meetOld === null || $meetOld === '') ? 'true' : 'false' }}" data-value="">
                                            <span class="rlx-option-text">Select option</span>
                                        </li>
                                        <li role="option" class="rlx-option {{ $meetOld === 'curbside' ? 'selected' : '' }}" aria-selected="{{ $meetOld === 'curbside' ? 'true' : 'false' }}" data-value="curbside">
                                            <span class="rlx-option-text">Curbside pickup</span>
                                        </li>
                                        <li role="option" class="rlx-option {{ $meetOld === 'inside' ? 'selected' : '' }}" aria-selected="{{ $meetOld === 'inside' ? 'true' : 'false' }}" data-value="inside">
                                            <span class="rlx-option-text">Inside pickup</span>
                                        </li>
                                    </ul>
                                    <input type="hidden" name="meet_option" id="meet-option" value="{{ $meetOld === 'curbside' || $meetOld === 'inside' ? $meetOld : '' }}">
                                </div>
                            </div>
                        </div>

                        <h6 class="text-uppercase text-muted small font-weight-bold mb-3">Additional information <span class="font-weight-normal text-lowercase">(optional)</span></h6>
                        <div class="form-group">
                            <label for="note">Notes for the chauffeur</label>
                            <textarea class="form-control" id="note" name="note" rows="3" placeholder="Special requests, child seats, gate, luggage, etc.">{{ old('note') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- 5 Payment -->
                <div class="card section-card" id="section-payment">
                    <div class="card-header"><span class="badge-step">5</span> Payment</div>
                    <div class="card-body">

                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <div class="text-muted small">Estimated trip total (vehicle selected)</div>
                                <div class="summary-total" id="summary-total-display">$0.00</div>
                            </div>
                        </div>

                        @if(!empty($stripeEnabled))
                            <input type="hidden" name="payment_method_id" id="payment_method_id" value="">
                            <p class="text-muted small mb-2">
                            </p>
                            <div class="form-group">
                                <label for="card-name-reservation">Name on card <span class="text-danger">*</span></label>
                                <input type="text" id="card-name-reservation" class="form-control" autocomplete="cc-name" placeholder="As shown on card" required>
                            </div>
                            <div class="form-group">
                                <label>Card details <span class="text-danger">*</span></label>
                                <div id="reservation-card-element" class="form-control"></div>
                                <div id="reservation-card-errors" class="text-danger small mt-1"></div>
                            </div>
                         
                            <button type="button" class="btn btn-success btn-lg" id="btn-reservation-pay">
                                <span id="btn-reservation-text"><i class="ik ik-check"></i> Pay &amp; create reservation</span>
                                <span id="btn-reservation-spinner" class="spinner-border spinner-border-sm d-none ml-2 align-middle" role="status" aria-hidden="true"></span>
                            </button>
                        @else
                            <div class="alert alert-info small">
                                Add <code>STRIPE_KEY</code> and <code>STRIPE_SECRET</code> to <code>.env</code> to enable card authorization like the website. Without Stripe, the booking is saved as <strong>Pending</strong> and confirmation emails are still sent.
                            </div>
                            <button type="submit" class="btn btn-success btn-lg" id="btn-reservation-submit-fallback">
                                <i class="ik ik-check"></i> Create reservation (pending payment)
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('script')
@if(!empty($googleMapsApiKey))
<script>
/**
 * Same flow as public site assets/js/custom.js setupCustomAutocomplete:
 * AutocompleteService predictions → Places getDetails → input value =
 * main_text + ", " + secondary_text (when secondary exists), else main_text — not raw formatted_address only.
 */
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
                if (t[j] === 'airport') { isAirport = true; break; }
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
        if (!input || !suggestionsContainer) return;
        if (input.getAttribute('data-places-bound') === '1') return;
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
                autocompleteService.getPlacePredictions(
                    {
                        input: query,
                        types: ['geocode', 'establishment'],
                        componentRestrictions: { country: 'us' }
                    },
                    function (predictions, status) {
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
                            var icon = document.createElement('span');
                            icon.className = 'suggestion-icon';
                            icon.innerHTML = '<i class="bi bi-geo-alt" aria-hidden="true"></i>';
                            var wrap = document.createElement('div');
                            var main = document.createElement('span');
                            main.className = 'main-text';
                            main.textContent = mainTxt;
                            var sub = document.createElement('span');
                            sub.className = 'sub-text';
                            sub.textContent = subTxt;
                            wrap.appendChild(main);
                            wrap.appendChild(sub);
                            item.appendChild(icon);
                            item.appendChild(wrap);
                            item.addEventListener('click', function () {
                                placesService.getDetails(
                                    {
                                        placeId: prediction.place_id,
                                        fields: ['formatted_address', 'name', 'address_components', 'types', 'geometry']
                                    },
                                    function (place, st) {
                                        if (st === google.maps.places.PlacesServiceStatus.OK && place) {
                                            var displayText = subTxt ? mainTxt + ', ' + subTxt : mainTxt;
                                            selectPlace(place, displayText);
                                        }
                                    }
                                );
                            });
                            suggestionsContainer.appendChild(item);
                        });
                        suggestionsContainer.style.display = 'block';
                    }
                );
            }, 500);
        });

        document.addEventListener('click', function (e) {
            if (!input.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                suggestionsContainer.style.display = 'none';
            }
        });

        input.addEventListener('focus', function () {
            if (input.value.trim().length >= 2) {
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    }

    setupCustomAutocomplete(
        'pickup_location',
        'pickup-suggestions-reservation',
        'is_airport',
        function () {
            var e = document.getElementById('pickup_location');
            if (e) e.dispatchEvent(new Event('change', { bubbles: true }));
        }
    );
    setupCustomAutocomplete(
        'dropoff_location',
        'dropoff-suggestions-reservation',
        null,
        function () {
            var e = document.getElementById('dropoff_location');
            if (e) e.dispatchEvent(new Event('change', { bubbles: true }));
        }
    );
};
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places&callback=initReservationPlaces" async defer></script>
@endif
<script>
(function () {
    var form = document.getElementById('reservation-form');
    var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var quoteUrl = @json(route('reservation.quote'));
    var returnQuoteUrl = @json(route('reservation.return-quote'));
    var outboundPriceByVid = {};
    var returnPriceCached = null;
    var returnQuoteInFlight = false;

    function serviceType() {
        var el = form.querySelector('input[name="service_type"]:checked');
        return el ? el.value : 'pointToPoint';
    }

    function toggleServiceUi() {
        var hourly = serviceType() === 'hourlyHire';
        document.getElementById('wrap-dropoff').classList.toggle('d-none', hourly);
        document.getElementById('wrap-hours').classList.toggle('d-none', !hourly);
        var dd = document.getElementById('dropoff_location');
        if (hourly) { dd.removeAttribute('required'); } else { dd.setAttribute('required', 'required'); }

        var wrapRet = document.getElementById('wrap-return-trip');
        if (wrapRet) {
            wrapRet.classList.toggle('d-none', hourly);
            if (hourly) {
                var rs = document.getElementById('return_service');
                if (rs && rs.checked) {
                    rs.checked = false;
                    var rf = document.getElementById('return-fields');
                    if (rf) rf.classList.add('d-none');
                    toggleReturnRequired(false);
                }
            }
        }
        returnPriceCached = null;
        syncPricingUi();
    }

    form.querySelectorAll('input[name="service_type"]').forEach(function (r) {
        r.addEventListener('change', toggleServiceUi);
    });
    toggleServiceUi();

    document.getElementById('booking_for_someone_else').addEventListener('change', function () {
        document.getElementById('booker-fields').classList.toggle('d-none', !this.checked);
    });

    function toggleReturnRequired(on) {
        ['return_pickup_date', 'return_pickup_time'].forEach(function (id) {
            var el = document.getElementById(id);
            if (!el) return;
            if (on) el.setAttribute('required', 'required');
            else el.removeAttribute('required');
        });
    }

    document.getElementById('return_service').addEventListener('change', function () {
        document.getElementById('return-fields').classList.toggle('d-none', !this.checked);
        toggleReturnRequired(this.checked);
        returnPriceCached = null;
        fetchReturnQuote();
    });
    toggleReturnRequired(document.getElementById('return_service').checked);

    function resetRlxSelect(id, defaultText, defaultValue) {
        var root = document.getElementById(id);
        if (!root) return;
        var valueEl = root.querySelector('.rlx-value');
        var hidden = root.querySelector('input[type="hidden"]');
        var options = root.querySelectorAll('.rlx-option');
        if (valueEl) valueEl.textContent = defaultText;
        if (hidden) hidden.value = defaultValue;
        options.forEach(function (o) {
            o.classList.remove('selected');
            o.setAttribute('aria-selected', 'false');
            if ((o.getAttribute('data-value') || '') === (defaultValue || '')) {
                o.classList.add('selected');
                o.setAttribute('aria-selected', 'true');
            }
        });
    }

    function initRlxSelect(rootId) {
        var root = document.getElementById(rootId);
        if (!root) return;
        var trigger = root.querySelector('.rlx-trigger');
        var list = root.querySelector('.rlx-list');
        var valueEl = root.querySelector('.rlx-value');
        var hidden = root.querySelector('input[type="hidden"]');
        var searchInput = root.querySelector('.rlx-search');
        var options = Array.from(root.querySelectorAll('.rlx-option'));
        var highlightedIndex = options.findIndex(function (o) { return o.classList.contains('selected'); });

        function getVisibleOptions() {
            if (!searchInput) return options;
            return options.filter(function (o) { return o.style.display !== 'none'; });
        }
        function filterOptions(q) {
            var query = (q || '').toLowerCase().trim();
            options.forEach(function (o) {
                var text = (o.getAttribute('data-value') || o.textContent || '').toLowerCase();
                o.style.display = query === '' || text.indexOf(query) >= 0 ? '' : 'none';
            });
            var visible = getVisibleOptions();
            highlightedIndex = visible.length ? Math.min(highlightedIndex, visible.length - 1) : 0;
            highlight(highlightedIndex);
        }
        function open() {
            root.classList.add('open');
            trigger.setAttribute('aria-expanded', 'true');
            if (searchInput) {
                searchInput.value = '';
                filterOptions('');
                var selectedOpt = options.find(function (o) { return o.classList.contains('selected'); });
                highlightedIndex = selectedOpt ? getVisibleOptions().indexOf(selectedOpt) : 0;
                if (highlightedIndex < 0) highlightedIndex = 0;
                searchInput.focus({ preventScroll: true });
            } else if (list) {
                list.focus({ preventScroll: true });
                if (highlightedIndex < 0) highlightedIndex = 0;
            }
            highlight(highlightedIndex);
        }
        function close() {
            root.classList.remove('open');
            trigger.setAttribute('aria-expanded', 'false');
            if (searchInput) searchInput.value = '';
        }
        function selectByIndex(visibleIdx) {
            var visible = getVisibleOptions();
            var opt = visible[visibleIdx];
            if (!opt) return;
            options.forEach(function (o) {
                o.classList.remove('selected');
                o.setAttribute('aria-selected', 'false');
            });
            opt.classList.add('selected');
            opt.setAttribute('aria-selected', 'true');
            highlightedIndex = visibleIdx;
            var val = opt.getAttribute('data-value');
            if (hidden) hidden.value = val === null ? '' : val;
            var txtEl = opt.querySelector('.rlx-option-text');
            if (valueEl) valueEl.textContent = txtEl ? txtEl.textContent.trim() : opt.textContent.trim();
            if (hidden) hidden.dispatchEvent(new Event('change', { bubbles: true }));
            close();
            trigger.focus();
        }
        function highlight(visibleIdx) {
            var visible = getVisibleOptions();
            if (!visible.length) return;
            highlightedIndex = Math.max(0, Math.min(visible.length - 1, visibleIdx));
            options.forEach(function (o) {
                var vIdx = visible.indexOf(o);
                o.tabIndex = vIdx === highlightedIndex ? 0 : -1;
            });
            var toFocus = visible[highlightedIndex];
            if (toFocus && (!searchInput || document.activeElement !== searchInput)) {
                toFocus.focus({ preventScroll: true });
            }
        }

        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            if (root.classList.contains('open')) { close(); } else { open(); }
        });
        options.forEach(function (opt) {
            opt.addEventListener('click', function (e) {
                e.stopPropagation();
                var visible = getVisibleOptions();
                var vIdx = visible.indexOf(opt);
                selectByIndex(vIdx >= 0 ? vIdx : 0);
            });
            opt.addEventListener('mousemove', function () {
                var visible = getVisibleOptions();
                highlightedIndex = visible.indexOf(opt);
            });
        });
        if (searchInput) {
            searchInput.addEventListener('input', function () { filterOptions(this.value); });
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowDown') { e.preventDefault(); highlight(highlightedIndex + 1); }
                else if (e.key === 'ArrowUp') { e.preventDefault(); highlight(highlightedIndex - 1); }
                else if (e.key === 'Enter') { e.preventDefault(); selectByIndex(highlightedIndex); }
                else if (e.key === 'Escape') { e.preventDefault(); close(); trigger.focus(); }
            });
        }
        document.addEventListener('click', function (e) {
            if (!root.contains(e.target)) close();
        });
        root.addEventListener('keydown', function (e) {
            if (!root.classList.contains('open')) return;
            if (searchInput && document.activeElement === searchInput) return;
            if (e.key === 'ArrowDown') { e.preventDefault(); highlight(highlightedIndex + 1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); highlight(highlightedIndex - 1); }
            else if (e.key === 'Enter') { e.preventDefault(); selectByIndex(highlightedIndex); }
            else if (e.key === 'Escape') { e.preventDefault(); close(); trigger.focus(); }
        });
    }

    function toggleOutboundFlightFields() {
        var cb = document.getElementById('no-flight-info-checkbox');
        var wrap = document.getElementById('outbound-flight-fields');
        if (!cb || !wrap) return;
        var on = cb.checked;
        wrap.style.display = on ? '' : 'none';
        wrap.querySelectorAll('input, select, button.rlx-trigger').forEach(function (el) {
            el.disabled = !on;
        });
        if (!on) {
            var fn = document.getElementById('flight_number');
            if (fn) fn.value = '';
            resetRlxSelect('rlx-pickup-flight', 'Select Airline', '');
            resetRlxSelect('rlx-meet-option', 'Select option', '');
        }
    }

    var nflCb = document.getElementById('no-flight-info-checkbox');
    if (nflCb) {
        nflCb.addEventListener('change', toggleOutboundFlightFields);
        toggleOutboundFlightFields();
    }

    initRlxSelect('rlx-pickup-flight');
    initRlxSelect('rlx-meet-option');

    function getOutboundForVehicle(vid) {
        if (outboundPriceByVid[vid] != null && !isNaN(outboundPriceByVid[vid])) {
            return outboundPriceByVid[vid];
        }
        return null;
    }

    function selectedVehicleTotal() {
        var sel = form.querySelector('input[name="vehicle_id"]:checked');
        if (!sel) return 0;
        var vid = sel.value;
        var ob = getOutboundForVehicle(vid);
        if (ob == null) {
            var lab = form.querySelector('.vehicle-option.selected');
            var amount = lab && lab.querySelector('.price-amount');
            var m = amount && amount.textContent.match(/[\d.]+/);
            ob = m ? parseFloat(m[0]) : 0;
        }
        var rs = document.getElementById('return_service');
        var addReturn = rs && rs.checked && serviceType() === 'pointToPoint';
        if (addReturn && returnPriceCached != null && !isNaN(returnPriceCached)) {
            return ob + returnPriceCached;
        }
        return ob;
    }

    function syncPricingUi() {
        var bd = document.getElementById('reservation-price-breakdown');
        var sumEl = document.getElementById('summary-total-display');
        var sel = form.querySelector('input[name="vehicle_id"]:checked');
        if (!bd || !sumEl) return;

        if (!sel) {
            bd.classList.add('d-none');
            sumEl.textContent = '$0.00';
            return;
        }

        var vid = sel.value;
        var ob = getOutboundForVehicle(vid);
        if (ob == null) {
            var lab = form.querySelector('.vehicle-option.selected');
            var amount = lab && lab.querySelector('.price-amount');
            var m = amount && amount.textContent.match(/[\d.]+/);
            ob = m ? parseFloat(m[0]) : null;
        }

        var rbOut = document.getElementById('rb-outbound');
        var rbRetRow = document.getElementById('rb-return-row');
        var rbRet = document.getElementById('rb-return');
        var rbTot = document.getElementById('rb-total');

        if (ob == null || isNaN(ob)) {
            bd.classList.add('d-none');
            sumEl.textContent = '$0.00';
            return;
        }

        rbOut.textContent = '$' + ob.toFixed(2);

        var rs = document.getElementById('return_service');
        var addReturn = rs && rs.checked && serviceType() === 'pointToPoint';

        if (addReturn) {
            rbRetRow.classList.remove('d-none');
            if (returnQuoteInFlight) {
                rbRet.textContent = '…';
                rbTot.textContent = '…';
                sumEl.textContent = '…';
            } else if (returnPriceCached != null) {
                rbRet.textContent = '$' + returnPriceCached.toFixed(2);
                var tot = ob + returnPriceCached;
                rbTot.textContent = '$' + tot.toFixed(2);
                sumEl.textContent = '$' + tot.toFixed(2);
            } else {
                rbRet.textContent = '—';
                rbTot.textContent = '$' + ob.toFixed(2);
                sumEl.textContent = '$' + ob.toFixed(2);
            }
        } else {
            rbRetRow.classList.add('d-none');
            rbTot.textContent = '$' + ob.toFixed(2);
            sumEl.textContent = '$' + ob.toFixed(2);
        }

        bd.classList.remove('d-none');
    }

    function fetchReturnQuote() {
        var rs = document.getElementById('return_service');
        var sel = form.querySelector('input[name="vehicle_id"]:checked');
        if (!rs || !rs.checked || serviceType() !== 'pointToPoint' || !sel) {
            returnPriceCached = null;
            returnQuoteInFlight = false;
            syncPricingUi();
            return;
        }

        var pu = document.getElementById('pickup_location').value.trim();
        var dr = document.getElementById('dropoff_location').value.trim();
        if (!pu || !dr) {
            returnPriceCached = null;
            returnQuoteInFlight = false;
            syncPricingUi();
            return;
        }

        returnQuoteInFlight = true;
        syncPricingUi();

        var fd = new FormData();
        fd.append('_token', token);
        fd.append('vehicle_id', sel.value);
        fd.append('pickup_location', pu);
        fd.append('dropoff_location', dr);
        fd.append('service_type', 'pointToPoint');

        fetch(returnQuoteUrl, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                returnQuoteInFlight = false;
                if (data.success && typeof data.price !== 'undefined') {
                    returnPriceCached = parseFloat(data.price);
                } else {
                    returnPriceCached = null;
                }
                syncPricingUi();
            })
            .catch(function () {
                returnQuoteInFlight = false;
                returnPriceCached = null;
                syncPricingUi();
            });
    }

    function updateVehicleSelectionStyles() {
        form.querySelectorAll('.vehicle-option').forEach(function (lab) {
            var inp = lab.querySelector('input[name="vehicle_id"]');
            lab.classList.toggle('selected', inp && inp.checked);
        });
    }

    form.querySelectorAll('.vehicle-option').forEach(function (lab) {
        lab.addEventListener('click', function (e) {
            var inp = lab.querySelector('input[name="vehicle_id"]');
            if (inp) { inp.checked = true; }
            updateVehicleSelectionStyles();
            returnPriceCached = null;
            fetchReturnQuote();
        });
    });
    updateVehicleSelectionStyles();

    function setQuoteLoading(on) {
        var btn = document.getElementById('btn-quote');
        var sp = document.getElementById('btn-quote-spinner');
        var tx = document.getElementById('btn-quote-text');
        if (btn) {
            btn.disabled = !!on;
            btn.setAttribute('aria-busy', on ? 'true' : 'false');
        }
        if (sp) sp.classList.toggle('d-none', !on);
        if (tx) {
            tx.innerHTML = on
                ? '<i class="ik ik-refresh-cw"></i> Calculating…'
                : '<i class="ik ik-refresh-cw"></i> Calculate vehicle prices';
        }
    }

    document.getElementById('btn-quote').addEventListener('click', function () {
        var fd = new FormData();
        fd.append('_token', token);
        fd.append('service_type', serviceType());
        fd.append('pickup_location', document.getElementById('pickup_location').value);
        fd.append('pickup_date', document.getElementById('pickup_date').value);
        fd.append('pickup_time', document.getElementById('pickup_time').value);
        var ap = document.getElementById('is_airport');
        if (ap && ap.value === '1') fd.append('is_airport', '1');
        if (serviceType() === 'pointToPoint') {
            fd.append('dropoff_location', document.getElementById('dropoff_location').value);
        } else {
            fd.append('select_hours', document.getElementById('select_hours').value);
        }

        var status = document.getElementById('quote-status');
        status.textContent = '';
        setQuoteLoading(true);

        fetch(quoteUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                outboundPriceByVid = {};
                returnPriceCached = null;
                form.querySelectorAll('[data-price-for]').forEach(function (el) {
                    var vid = el.getAttribute('data-price-for');
                    var row = data.distance && data.distance[vid];
                    var hint = el.querySelector('.price-hint');
                    var amountEl = el.querySelector('.price-amount');
                    if (hint) {
                        hint.style.display = row && !row.error ? 'none' : '';
                    }
                    if (!amountEl) return;
                    if (row && row.error) {
                        amountEl.innerHTML = '<span class="err">' + row.error + '</span>';
                        amountEl.classList.add('text-muted');
                    } else if (row && typeof row.price !== 'undefined') {
                        var p = parseFloat(row.price);
                        outboundPriceByVid[vid] = p;
                        var parts = p.toFixed(2).split('.');
                        amountEl.innerHTML = '<div class="reservation-car-price"><h4 class="mb-0"><span class="pricing_summary_price">$' + parts[0] + '<span class="price-decimal">.' + parts[1] + '</span></span> USD</h4></div><div class="small text-muted mt-1">Outbound</div>';
                        amountEl.classList.remove('text-muted');
                    } else {
                        amountEl.textContent = '—';
                        amountEl.classList.add('text-muted');
                    }
                });
                fetchReturnQuote();
            })
            .catch(function () {
                status.textContent = 'Quote failed. Check API key and network.';
            })
            .finally(function () {
                setQuoteLoading(false);
            });
    });

    ['pickup_location', 'dropoff_location'].forEach(function (id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('change', function () {
            returnPriceCached = null;
            fetchReturnQuote();
        });
    });

    document.querySelectorAll('.reservation-steps a').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            var id = this.getAttribute('href');
            var el = document.querySelector(id);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            document.querySelectorAll('.reservation-steps a').forEach(function (x) { x.classList.remove('active'); });
            this.classList.add('active');
        });
    });

    var reservationStripeEnabled = @json(!empty($stripeEnabled));
    var storeUrl = @json(route('reservation.store'));
    var finalizeUrl = @json(route('reservation.finalize'));

    if (reservationStripeEnabled && typeof Stripe !== 'undefined') {
        var stripe = Stripe(@json($stripePublishableKey ?? ''));
        var elements = stripe.elements();
        var card = elements.create('card', { style: { base: { fontSize: '16px', color: '#32325d' } } });
        var cardEl = document.getElementById('reservation-card-element');
        if (cardEl) {
            card.mount('#reservation-card-element');
        }

        function setPayLoading(on) {
            var btn = document.getElementById('btn-reservation-pay');
            var sp = document.getElementById('btn-reservation-spinner');
            var tx = document.getElementById('btn-reservation-text');
            if (btn) {
                btn.disabled = !!on;
                btn.setAttribute('aria-busy', on ? 'true' : 'false');
            }
            if (sp) sp.classList.toggle('d-none', !on);
            if (tx) {
                tx.innerHTML = on
                    ? 'Processing payment…'
                    : '<i class="ik ik-check"></i> Pay &amp; create reservation';
            }
        }

        async function finalizeAfter3ds(bookingId) {
            var r = await fetch(finalizeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ booking_id: String(bookingId) })
            });
            var data = await r.json();
            if (data.success && data.redirect) {
                window.location.href = data.redirect;
            } else {
                alert(data.message || 'Could not finalize payment.');
                setPayLoading(false);
            }
        }

        var payBtn = document.getElementById('btn-reservation-pay');
        if (payBtn) {
            payBtn.addEventListener('click', async function () {
                var errEl = document.getElementById('reservation-card-errors');
                if (errEl) errEl.textContent = '';
                var nameInput = document.getElementById('card-name-reservation');
                if (!nameInput || !nameInput.value.trim()) {
                    if (errEl) errEl.textContent = 'Enter the name on card.';
                    return;
                }
                if (!form.querySelector('input[name="vehicle_id"]:checked')) {
                    alert('Select a vehicle.');
                    return;
                }
                setPayLoading(true);

                var pmResult = await stripe.createPaymentMethod({
                    type: 'card',
                    card: card,
                    billing_details: { name: nameInput.value.trim() }
                });

                if (pmResult.error) {
                    if (errEl) errEl.textContent = pmResult.error.message;
                    setPayLoading(false);
                    return;
                }

                var fd = new FormData(form);
                fd.set('payment_method_id', pmResult.paymentMethod.id);

                try {
                    var res = await fetch(storeUrl, {
                        method: 'POST',
                        body: fd,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    var raw = await res.text();
                    var data;
                    try {
                        data = JSON.parse(raw);
                    } catch (parseErr) {
                        alert('Unexpected server response. If the page was redirected, try again.');
                        setPayLoading(false);
                        return;
                    }

                    if (res.status === 422 && data.errors) {
                        var msg = Object.values(data.errors).flat().join(' ');
                        alert(msg || data.message || 'Validation failed.');
                        setPayLoading(false);
                        return;
                    }

                    if (data.requires_action && data.payment_intent_client_secret) {
                        var conf = await stripe.confirmCardPayment(data.payment_intent_client_secret, {
                            return_url: data.return_url || window.location.href
                        });
                        if (conf.error) {
                            if (errEl) errEl.textContent = conf.error.message;
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

    syncPricingUi();
    fetchReturnQuote();
})();
</script>
@endpush
