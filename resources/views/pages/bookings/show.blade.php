@extends('layouts.main')
@section('title', 'Booking Details')

@section('content')
<style>
    :root {
        --booking-text: #0f172a;
        --booking-muted: #64748b;
        --booking-border: #dbe4f0;
        --booking-bg: #eef3fa;
        --booking-surface: #ffffff;
        --booking-accent: #0f4c81;
        --booking-accent-soft: #e8f1fb;
        --booking-gold: #b88a44;
    }
    .booking-page {
        background:
            radial-gradient(circle at 8% -10%, rgba(15, 76, 129, 0.14), transparent 42%),
            radial-gradient(circle at 90% 0%, rgba(184, 138, 68, 0.12), transparent 36%),
            var(--booking-bg);
        border-radius: 18px;
        padding: 2rem;
    }
    .booking-surface {
        border: 1px solid #d8e1ee;
        border-radius: 18px;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.1);
        background: var(--booking-surface);
        overflow: hidden;
    }
    .booking-header {
        background: linear-gradient(125deg, #0b3c66 0%, #0f4c81 65%, #2d6aa1 100%);
        color: #fff;
        padding: 1.5rem 1.75rem;
        position: relative;
        overflow: hidden;
    }
    .booking-header::after {
        content: '';
        position: absolute;
        right: -50px;
        top: -50px;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
    }
    .booking-header::before {
        content: '';
        position: absolute;
        left: 25%;
        bottom: -95px;
        width: 260px;
        height: 180px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.08);
    }
    .booking-title {
        font-weight: 800;
        letter-spacing: 0.3px;
        font-size: 1.55rem;
    }
    .booking-subtitle {
        opacity: 0.92;
        font-size: 0.95rem;
    }
    .info-card {
        border: 1px solid var(--booking-border);
        border-radius: 14px;
        background: #fff;
        height: 100%;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
    }
    .info-card .card-header {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        border-bottom: 1px solid #e8eff8;
        border-radius: 14px 14px 0 0;
        font-weight: 700;
        color: #0f2942;
        font-size: 0.93rem;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        padding: 0.95rem 1rem;
    }
    .kv-list li {
        border: none;
        border-bottom: 1px dashed #e2eaf5;
        padding: 0.65rem 0;
    }
    .kv-list li:last-child {
        border-bottom: none;
    }
    .summary-tile {
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        border: 1px solid #dce6f4;
        border-radius: 14px;
        padding: 1.05rem 1.05rem;
        min-height: 92px;
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.05);
    }
    .summary-tile .label {
        font-size: 0.72rem;
        color: var(--booking-muted);
        text-transform: uppercase;
        letter-spacing: 0.09em;
        margin-bottom: 0.25rem;
    }
    .summary-tile .value {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--booking-text);
        line-height: 1.2;
    }
    .badge-soft {
        padding: 0.5rem 0.85rem;
        border-radius: 999px;
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        border: 1px solid transparent;
    }
    .badge-soft-success { background: #e7f8ef; color: #0f7b43; border-color: #cdeedc; }
    .badge-soft-danger { background: #fdeced; color: #bc1c28; border-color: #f9d5d8; }
    .badge-soft-info { background: #e8f3ff; color: #1f5fbf; border-color: #cfe3ff; }
    .badge-soft-warning { background: #fff4d7; color: #a26403; border-color: #f8e5b5; }
    .muted-empty {
        color: var(--booking-muted);
        background: linear-gradient(180deg, #fafcff 0%, #f5f8fc 100%);
        border: 1px dashed #cfd9e8;
        border-radius: 12px;
        padding: 0.95rem 1rem;
    }
    .section-spacing {
        margin-bottom: 1.45rem;
    }
    .booking-page .btn {
        border-radius: 10px;
        font-weight: 700;
        letter-spacing: 0.01em;
    }
    .booking-page .list-group-item {
        color: #1a2a3e;
    }
    .booking-page .text-muted {
        color: var(--booking-muted) !important;
    }
    .booking-page .btn-outline-secondary {
        border-color: #c6d2e3;
        color: #243b53;
        background: #fff;
    }
    .booking-page .btn-outline-secondary:hover {
        background: #f3f7fc;
        color: #102a43;
        border-color: #b6c6dc;
    }
    .booking-page .btn-primary {
        background: linear-gradient(130deg, #0f4c81 0%, #1566a8 100%);
        border: none;
        box-shadow: 0 8px 16px rgba(21, 102, 168, 0.28);
    }
    .booking-page .btn-outline-primary {
        color: #0f4c81;
        border-color: #9fbcdb;
        background: #f7fbff;
    }
    .booking-page .btn-outline-primary:hover {
        color: #0b3c66;
        border-color: #84aad0;
        background: #edf5ff;
    }
    .booking-page .card-body {
        position: relative;
        z-index: 2;
    }
    .booking-page .row > [class*="col-"] {
        display: flex;
    }
    .booking-page .row > [class*="col-"] > .info-card,
    .booking-page .row > [class*="col-"] > .summary-tile {
        width: 100%;
    }
    @media (max-width: 768px) {
        .booking-page {
            padding: 1rem;
            border-radius: 14px;
        }
        .booking-header {
            padding: 1.15rem 1rem;
        }
        .booking-title {
            font-size: 1.25rem;
        }
    }
</style>

<div class="container-fluid mt-4 mb-5 px-md-4">
    <div class="booking-page">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
            <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary">
                <i class="ik ik-arrow-left mr-1"></i> Back to bookings
            </a>
            <div class="d-flex flex-wrap mt-2 mt-md-0">
                <a href="{{ route('bookings.edit', $booking->id) }}" class="btn btn-primary mr-2 mb-2 mb-md-0">
                    <i class="ik ik-edit-2 mr-1"></i> Edit reservation
                </a>
                @if ($booking->from_admin_reservation)
                    <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#modalBookingEmailComposer">
                        <i class="ik ik-mail mr-1"></i> Send booking emails
                    </button>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="booking-surface">
            <div class="booking-header d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h4 class="booking-title mb-1">
                        {{ $booking->booking_id ?? 'N/A' }}
                        <span class="ml-2" style="font-weight: 600; opacity: 0.9;">Record #{{ $booking->id }}</span>
                    </h4>
                    <div class="booking-subtitle">
                        Pickup {{ \Carbon\Carbon::parse($booking->pickup_date)->format('M d, Y') }} at {{ \Carbon\Carbon::parse($booking->pickup_time)->format('h:i A') }}
                    </div>
                </div>
                <div class="d-flex flex-wrap mt-2 mt-md-0">
                    @if($travelInfo)
                        <span class="badge-soft mr-2 {{ $travelInfo['type'] === 'hourly' ? 'badge-soft-warning' : 'badge-soft-info' }}">
                            {{ ucfirst(str_replace('_', ' ', $travelInfo['type'])) }}
                        </span>
                    @endif
                    <span class="badge-soft {{ $booking->payment_status == 'Paid' ? 'badge-soft-success' : 'badge-soft-danger' }}">
                        Payment: {{ $booking->payment_status }}
                    </span>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row section-spacing">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="summary-tile">
                            <div class="label">Total Price</div>
                            <div class="value">${{ number_format($booking->total_price, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="summary-tile">
                            <div class="label">Passengers</div>
                            <div class="value">{{ $booking->passengers ? $booking->passengers->count() : 0 }}</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="summary-tile">
                            <div class="label">Trip Type</div>
                            <div class="value">
                                {{ $travelInfo ? ucfirst(str_replace('_', ' ', $travelInfo['type'])) : 'N/A' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="summary-tile">
                            <div class="label">Return Trip</div>
                            <div class="value">{{ $booking->returnService ? 'Included' : 'No' }}</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-7 section-spacing">
                        <div class="info-card card">
                            <div class="card-header">Outward Trip Details</div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush kv-list">
                                    @if(filled($booking->pickup_location))
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span class="text-muted">Pickup</span>
                                            <span class="text-right">{{ $booking->pickup_location }}</span>
                                        </li>
                                    @endif
                                    @if(filled($booking->dropoff_location))
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span class="text-muted">Dropoff</span>
                                            <span class="text-right">{{ $booking->dropoff_location }}</span>
                                        </li>
                                    @endif
                                    @if(filled($booking->pickup_date))
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span class="text-muted">Pickup Date</span>
                                            <span>{{ \Carbon\Carbon::parse($booking->pickup_date)->format('M d, Y') }}</span>
                                        </li>
                                    @endif
                                    @if(filled($booking->pickup_time))
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span class="text-muted">Pickup Time</span>
                                            <span>{{ \Carbon\Carbon::parse($booking->pickup_time)->format('h:i A') }}</span>
                                        </li>
                                    @endif
                                    @if($travelInfo && $travelInfo['type'] === 'hourly')
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span class="text-muted">Total Hours</span>
                                            <span>{{ $travelInfo['hours'] }}</span>
                                        </li>
                                    @endif
                                    @if($travelInfo && $travelInfo['type'] === 'point_to_point')
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span class="text-muted">Distance</span>
                                            <span>{{ $travelInfo['distance'] }} Miles</span>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5 section-spacing">
                        <div class="info-card card">
                            <div class="card-header">Vehicle Information</div>
                            <div class="card-body">
                                @if($booking->vehicle)
                                    <ul class="list-group list-group-flush kv-list">
                                        @if(filled($booking->vehicle->vehicle_name))
                                            <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Name</span><span>{{ $booking->vehicle->vehicle_name }}</span></li>
                                        @endif
                                        @if(filled($booking->vehicle->vehicle_code))
                                            <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Code</span><span>{{ $booking->vehicle->vehicle_code }}</span></li>
                                        @endif
                                        @if(!is_null($booking->vehicle->number_of_passengers))
                                            <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Passengers</span><span>{{ $booking->vehicle->number_of_passengers }}</span></li>
                                        @endif
                                        @if(!is_null($booking->vehicle->luggage_capacity))
                                            <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Luggage</span><span>{{ $booking->vehicle->luggage_capacity }}</span></li>
                                        @endif
                                        @if(!is_null($booking->vehicle->base_fare))
                                            <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Base Fare</span><span>${{ number_format($booking->vehicle->base_fare, 2) }}</span></li>
                                        @endif
                                        @if(!is_null($booking->vehicle->base_hourly_fare))
                                            <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Hourly Fare</span><span>${{ number_format($booking->vehicle->base_hourly_fare, 2) }}</span></li>
                                        @endif
                                    </ul>
                                    @if($booking->vehicle->vehicle_image)
                                        <div class="text-center mt-3">
                                            <img src="{{ asset('storage/' . $booking->vehicle->vehicle_image) }}" class="img-thumbnail" style="max-width: 230px;" alt="Vehicle Image">
                                        </div>
                                    @endif
                                @else
                                    <div class="muted-empty">No vehicle assigned for this booking.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if($booking->returnService && (
                    filled($booking->returnService->pickup_location) ||
                    filled($booking->returnService->dropoff_location) ||
                    filled($booking->returnService->pickup_date) ||
                    filled($booking->returnService->pickup_time)
                ))
                    <div class="info-card card section-spacing">
                        <div class="card-header">Return Trip Details</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-group list-group-flush kv-list">
                                        @if(filled($booking->returnService->pickup_location))
                                            <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Pickup</span><span class="text-right">{{ $booking->returnService->pickup_location }}</span></li>
                                        @endif
                                        @if(filled($booking->returnService->dropoff_location))
                                            <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Dropoff</span><span class="text-right">{{ $booking->returnService->dropoff_location }}</span></li>
                                        @endif
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-group list-group-flush kv-list">
                                        @if(filled($booking->returnService->pickup_date))
                                            <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Pickup Date</span><span>{{ \Carbon\Carbon::parse($booking->returnService->pickup_date)->format('M d, Y') }}</span></li>
                                        @endif
                                        @if(filled($booking->returnService->pickup_time))
                                            <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Pickup Time</span><span>{{ \Carbon\Carbon::parse($booking->returnService->pickup_time)->format('h:i A') }}</span></li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($booking->note)
                    <div class="info-card card section-spacing">
                        <div class="card-header">Chauffeur Notes</div>
                        <div class="card-body">
                            <p class="mb-0">{{ $booking->note }}</p>
                        </div>
                    </div>
                @endif

                @php
                    $hasBookerData = $booking->booker && (
                        filled(trim(($booking->booker->first_name ?? '') . ' ' . ($booking->booker->last_name ?? ''))) ||
                        filled($booking->booker->email) ||
                        filled($booking->booker->phone_number)
                    );
                @endphp
                <div class="row">
                    <div class="{{ $hasBookerData ? 'col-lg-6' : 'col-lg-12' }} section-spacing">
                        <div class="info-card card h-100">
                            <div class="card-header">Passenger Information</div>
                            <div class="card-body">
                                @forelse($booking->passengers as $passenger)
                                    <div class="border rounded p-3 mb-3">
                                        <div class="font-weight-bold mb-2">{{ $passenger->first_name }} {{ $passenger->last_name }}</div>
                                        @if(filled($passenger->email))
                                            <div class="small text-muted mb-1">Email</div>
                                            <div class="mb-2">{{ $passenger->email }}</div>
                                        @endif
                                        @if(filled($passenger->phone_number))
                                            <div class="small text-muted mb-1">Phone</div>
                                            <div>{{ $passenger->phone_number }}</div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="muted-empty">No passenger information available.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    @if($hasBookerData)
                        <div class="col-lg-6 section-spacing">
                            <div class="info-card card h-100">
                                <div class="card-header">Booker Information</div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush kv-list">
                                        @if(filled(trim(($booking->booker->first_name ?? '') . ' ' . ($booking->booker->last_name ?? ''))))
                                            <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Name</span><span>{{ trim(($booking->booker->first_name ?? '') . ' ' . ($booking->booker->last_name ?? '')) }}</span></li>
                                        @endif
                                        @if(filled($booking->booker->email))
                                            <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Email</span><span>{{ $booking->booker->email }}</span></li>
                                        @endif
                                        @if(filled($booking->booker->phone_number))
                                            <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Phone</span><span>{{ $booking->booker->phone_number }}</span></li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                @if($booking->passengers && $booking->passengers->count())
                    <div class="info-card card section-spacing">
                        <div class="card-header">Flight Details</div>
                        <div class="card-body">
                            @php $hasFlightDetails = false; @endphp
                            @foreach($booking->passengers as $passenger)
                                @if($passenger->flightDetail)
                                    @php $hasFlightDetails = true; @endphp
                                    <div class="border rounded p-3 mb-3">
                                        <h6 class="text-secondary">Passenger: {{ $passenger->first_name }} {{ $passenger->last_name }}</h6>
                                        <ul class="list-group list-group-flush kv-list">
                                            @if(filled($passenger->flightDetail->pickup_flight_details))
                                                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Flight Info</span><span>{{ $passenger->flightDetail->pickup_flight_details }}</span></li>
                                            @endif
                                            @if(filled($passenger->flightDetail->flight_number))
                                                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Flight Number</span><span>{{ $passenger->flightDetail->flight_number }}</span></li>
                                            @endif
                                            @if(filled($passenger->flightDetail->meet_option))
                                                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Meet Option</span><span>{{ $passenger->flightDetail->meet_option }}</span></li>
                                            @endif
                                            <li class="list-group-item d-flex justify-content-between"><span class="text-muted">No Flight Info</span><span>{{ $passenger->flightDetail->no_flight_info ? 'Yes' : 'No' }}</span></li>
                                            @if(!is_null($passenger->flightDetail->inside_pickup_fee))
                                                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Inside Pickup Fee</span><span>${{ number_format($passenger->flightDetail->inside_pickup_fee, 2) }}</span></li>
                                            @endif
                                        </ul>
                                    </div>
                                @endif
                            @endforeach

                            @if(!$hasFlightDetails)
                                <div class="muted-empty">No flight details were provided for passengers.</div>
                            @endif
                        </div>
                    </div>
                @endif

                @if($booking->breakdown)
                    <div class="info-card card">
                        <div class="card-header">Price Breakdown</div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @if(!is_null($booking->breakdown->base_fare))
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Base Fare</span><strong>${{ number_format($booking->breakdown->base_fare, 2) }}</strong>
                                    </li>
                                @endif
                                @if($travelInfo && $travelInfo['type'] === 'point_to_point')
                                    @if(!is_null($booking->breakdown->per_km_rate))
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Per Mile Rate</span><span>${{ number_format($booking->breakdown->per_km_rate, 2) }}</span>
                                        </li>
                                    @endif
                                    @if(!is_null($booking->breakdown->total_kms))
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Total Miles</span><span>{{ $booking->breakdown->total_kms }} Miles</span>
                                        </li>
                                    @endif
                                @elseif($travelInfo && $travelInfo['type'] === 'hourly')
                                    @if(!is_null($booking->breakdown->hourly_fare))
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Hourly Fare</span><span>${{ number_format($booking->breakdown->hourly_fare, 2) }}</span>
                                        </li>
                                    @endif
                                    @if(!is_null($booking->breakdown->total_hours))
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Total Hours</span><span>{{ $booking->breakdown->total_hours }}</span>
                                        </li>
                                    @endif
                                @endif
                                @if($booking->returnService)
                                    @if(!is_null($booking->breakdown->return_base_fare))
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Return Base Fare</span><span>${{ number_format($booking->breakdown->return_base_fare, 2) }}</span>
                                        </li>
                                    @endif
                                    @if(!is_null($booking->breakdown->return_per_km_rate))
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Return Per Mile Rate</span><span>${{ number_format($booking->breakdown->return_per_km_rate, 2) }}</span>
                                        </li>
                                    @endif
                                    @if(!is_null($booking->breakdown->return_total_kms))
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Return Miles</span><span>{{ $booking->breakdown->return_total_kms }} Miles</span>
                                        </li>
                                    @endif
                                @endif
                                <li class="list-group-item d-flex justify-content-between bg-light">
                                    <strong>Total Price</strong><strong class="text-primary">${{ number_format($booking->total_price, 2) }}</strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if ($booking->from_admin_reservation)
    @include('pages.bookings.partials.email-composer-modal')
@endif
@endsection
