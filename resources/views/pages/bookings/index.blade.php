@extends('layouts.main')
@section('title', 'Reservations')

@push('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .bookings-shell {
        max-width: 1440px;
        margin: 0 auto;
    }
    .bookings-hero {
        margin-bottom: 1.5rem;
    }
    .bookings-title {
        font-size: 1.9rem;
        font-weight: 700;
        color: #17324d;
        margin-bottom: 0.35rem;
    }
    .bookings-subtitle {
        color: #6b7b8d;
        margin-bottom: 0;
    }
    .bookings-panel {
        border: none;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 12px 35px rgba(18, 38, 63, 0.08);
    }
    .bookings-filter-bar {
        background: linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
        border-bottom: 1px solid #e8eef5;
        padding: 1.25rem 1.25rem 0.5rem;
    }
    .bookings-filter-bar .form-control,
    .bookings-filter-bar .custom-select {
        min-height: 44px;
        border-radius: 12px;
    }
    .bookings-filter-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }
    .bookings-filter-meta {
        color: #607082;
        font-size: 0.92rem;
    }
    .bookings-table-wrap {
        padding: 0.85rem 1rem 1rem;
    }
    .bookings-table {
        margin-bottom: 0;
        width: 100%;
        table-layout: fixed;
    }
    .bookings-table thead th {
        border-top: 0;
        border-bottom: 1px solid #e9eef5;
        color: #5f7083;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        white-space: nowrap;
        padding: 0.8rem 0.55rem;
    }
    .bookings-table tbody td {
        vertical-align: middle;
        padding: 0.8rem 0.55rem;
        border-color: #edf1f6;
        font-size: 0.9rem;
    }
    .bookings-table th:nth-child(1),
    .bookings-table td:nth-child(1) { width: 10%; }
    .bookings-table th:nth-child(2),
    .bookings-table td:nth-child(2) { width: 9%; }
    .bookings-table th:nth-child(3),
    .bookings-table td:nth-child(3) { width: 10%; }
    .bookings-table th:nth-child(4),
    .bookings-table td:nth-child(4) { width: 10%; }
    .bookings-table th:nth-child(5),
    .bookings-table td:nth-child(5) { width: 10%; }
    .bookings-table th:nth-child(6),
    .bookings-table td:nth-child(6) { width: 9%; }
    .bookings-table th:nth-child(7),
    .bookings-table td:nth-child(7) { width: 11%; }
    .bookings-table th:nth-child(8),
    .bookings-table td:nth-child(8) { width: 7%; }
    .bookings-table th:nth-child(9),
    .bookings-table td:nth-child(9) { width: 8%; }
    .bookings-table th:nth-child(10),
    .bookings-table td:nth-child(10) { width: 16%; }
    .bookings-table thead th.col-form-submitted {
        white-space: normal;
        line-height: 1.25;
        max-width: 6.5rem;
    }
    .bookings-table tbody tr:hover {
        background: #fbfdff;
    }
    .booking-id {
        font-weight: 700;
        color: #16324b;
        font-size: 0.92rem;
        line-height: 1.25;
    }
    .booking-id-sub {
        color: #7c8b9a;
        font-size: 0.78rem;
    }
    .booking-primary {
        font-weight: 600;
        color: #1b3552;
        font-size: 0.88rem;
        line-height: 1.3;
    }
    .booking-secondary {
        color: #718093;
        font-size: 0.78rem;
    }
    .booking-location {
        font-weight: 600;
        color: #20364f;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
        max-width: 100%;
    }
    .booking-passengers {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }
    .booking-passenger-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.24rem 0.5rem;
        border-radius: 999px;
        background: #eef5fb;
        color: #24476b;
        font-size: 0.74rem;
        font-weight: 600;
        max-width: 100%;
        white-space: nowrap;
    }
    .booking-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.22rem 0.5rem;
        border-radius: 999px;
        font-size: 0.64rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        white-space: nowrap;
        line-height: 1.1;
    }
    .booking-status-paid {
        background: rgba(25, 135, 84, 0.12);
        color: #157347;
    }
    .booking-status-pending {
        background: rgba(255, 193, 7, 0.18);
        color: #8f6500;
    }
    .booking-status-authorized {
        background: rgba(13, 110, 253, 0.12);
        color: #0a58ca;
    }
    .booking-status-default {
        background: rgba(108, 117, 125, 0.12);
        color: #495057;
    }
    .booking-actions {
        display: flex;
        flex-wrap: nowrap;
        gap: 0.5rem;
        align-items: center;
    }
    .booking-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0;
        border-radius: 10px;
        padding: 0;
        font-weight: 600;
        font-size: 0.76rem;
        line-height: 1;
        width: 42px;
        height: 36px;
        white-space: nowrap;
    }
    .booking-action-label {
        display: none;
        line-height: 1;
    }
    .booking-action-btn i {
        font-size: 0.82rem;
    }
    .bookings-empty {
        padding: 2.5rem 1rem;
        text-align: center;
        color: #768597;
    }
    .bookings-pagination {
        padding-top: 1rem;
        border-top: 1px solid #edf1f6;
    }
    .bookings-pagination .pagination {
        justify-content: flex-end;
        margin-bottom: 0;
    }
    .bookings-pagination .page-link {
        border-radius: 10px;
        margin: 0 0.18rem;
        border: 1px solid #dce6f0;
        color: #20476e;
    }
    .bookings-mobile-list {
        display: none;
    }
    .booking-mobile-card {
        border: 1px solid #e8eef5;
        border-radius: 16px;
        padding: 1rem;
        background: #fff;
        box-shadow: 0 10px 30px rgba(17, 37, 62, 0.05);
    }
    .booking-mobile-row {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.5rem;
    }
    .booking-mobile-label {
        font-size: 0.78rem;
        color: #708090;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .booking-mobile-value {
        font-weight: 600;
        color: #1b3552;
        text-align: right;
    }
    .bookings-mobile-list .booking-action-btn {
        width: auto;
        height: auto;
        min-width: 0;
        gap: 0.45rem;
        padding: 0.55rem 0.8rem;
    }
    .bookings-mobile-list .booking-action-label {
        display: inline;
    }
    @media (max-width: 991.98px) {
        .bookings-table-desktop {
            display: none;
        }
        .bookings-mobile-list {
            display: grid;
            gap: 1rem;
        }
        .bookings-filter-actions {
            align-items: flex-start;
            flex-direction: column;
        }
        .bookings-pagination .pagination {
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
@php
    $truncateWords = fn ($value, $limit = 3) => \Illuminate\Support\Str::words(trim((string) $value), $limit, '...');
    $formatServiceOption = function ($value) {
        return match ((string) $value) {
            'from_airport' => 'From Airport',
            'to_airport' => 'To Airport',
            'point_to_point' => 'Point to Point',
            'hourly_as_directed' => 'Hourly / As Directed',
            default => filled($value) ? \Illuminate\Support\Str::headline((string) $value) : 'N/A',
        };
    };
    $paymentBadgeClass = function ($status) {
        return match (strtolower((string) $status)) {
            'paid' => 'booking-status-paid',
            'pending' => 'booking-status-pending',
            'authorized' => 'booking-status-authorized',
            default => 'booking-status-default',
        };
    };
    $showingFrom = $bookings->count() ? $bookings->firstItem() : 0;
    $showingTo = $bookings->count() ? $bookings->lastItem() : 0;
    $activeFilterCount = collect([
        request('search'),
        request('payment_status'),
        request('service_option'),
        request('vehicle_id'),
        request('date_from'),
        request('date_to'),
    ])->filter(fn ($value) => filled($value))->count();
@endphp

<div class="container-fluid bookings-shell">
    <div class="bookings-hero">
        <h2 class="bookings-title">Reservations</h2>
        <!-- <p class="bookings-subtitle">A cleaner and faster way to review, search, and manage reservations.</p> -->
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Whoops!</strong> There were some problems with your input.
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <div class="card bookings-panel">
        <div class="bookings-filter-bar">
            <div class="bookings-filter-actions">
                <div>
                    <div class="font-weight-bold text-dark">Search, filter, and browse reservations</div>
                    <div class="bookings-filter-meta">
                        Showing {{ $showingFrom }}-{{ $showingTo }} of {{ $bookings->total() }} reservations
                        @if($activeFilterCount > 0)
                            with {{ $activeFilterCount }} active filter{{ $activeFilterCount > 1 ? 's' : '' }}
                        @endif
                    </div>
                </div>
                <div class="d-flex flex-wrap" style="gap: 0.5rem;">
                    <a href="{{ route('reservation.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> New reservation
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('bookings.index') }}" id="bookings-filters-form">
                <div class="form-row">
                    <div class="form-group col-xl-3 col-lg-6">
                        <label for="booking-search" class="small text-muted font-weight-bold">Search</label>
                        <input
                            type="text"
                            id="booking-search"
                            name="search"
                            class="form-control"
                            value="{{ request('search') }}"
                            placeholder="Reservation ID, passenger, location, vehicle..."
                        >
                    </div>
                    <div class="form-group col-xl-2 col-lg-3 col-md-6">
                        <label for="payment_status" class="small text-muted font-weight-bold">Payment</label>
                        <select name="payment_status" id="payment_status" class="form-control custom-select js-auto-filter">
                            <option value="">All statuses</option>
                            @foreach($paymentStatuses as $status)
                                <option value="{{ $status }}" @selected(request('payment_status') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-xl-2 col-lg-3 col-md-6">
                        <label for="service_option" class="small text-muted font-weight-bold">Service type</label>
                        <select name="service_option" id="service_option" class="form-control custom-select js-auto-filter">
                            <option value="">All services</option>
                            <option value="from_airport" @selected(request('service_option') === 'from_airport')>From Airport</option>
                            <option value="to_airport" @selected(request('service_option') === 'to_airport')>To Airport</option>
                            <option value="point_to_point" @selected(request('service_option') === 'point_to_point')>Point to Point</option>
                            <option value="hourly_as_directed" @selected(request('service_option') === 'hourly_as_directed')>Hourly / As Directed</option>
                        </select>
                    </div>
                    <div class="form-group col-xl-2 col-lg-4 col-md-6">
                        <label for="vehicle_id" class="small text-muted font-weight-bold">Vehicle</label>
                        <select name="vehicle_id" id="vehicle_id" class="form-control custom-select js-auto-filter">
                            <option value="">All vehicles</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" @selected((string) request('vehicle_id') === (string) $vehicle->id)>{{ $vehicle->vehicle_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-xl-1 col-lg-4 col-md-6">
                        <label for="date_from" class="small text-muted font-weight-bold">From</label>
                        <input type="date" name="date_from" id="date_from" class="form-control js-auto-filter" value="{{ request('date_from') }}">
                    </div>
                    <div class="form-group col-xl-1 col-lg-4 col-md-6">
                        <label for="date_to" class="small text-muted font-weight-bold">To</label>
                        <input type="date" name="date_to" id="date_to" class="form-control js-auto-filter" value="{{ request('date_to') }}">
                    </div>
                    <div class="form-group col-xl-1 col-lg-4 col-md-6">
                        <label for="per_page" class="small text-muted font-weight-bold">Rows</label>
                        <select name="per_page" id="per_page" class="form-control custom-select js-auto-filter">
                            @foreach([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}" @selected((int) request('per_page', 10) === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 0.75rem;">
                    <div class="bookings-filter-meta">
                        Search works across booking ID, passenger name, vehicle, locations, payment status, and service type.
                    </div>
                    <div class="d-flex flex-wrap" style="gap: 0.5rem;">
                        <button type="submit" class="btn btn-dark">
                            <i class="bi bi-funnel"></i> Apply filters
                        </button>
                        <a href="{{ route('bookings.index') }}" class="btn btn-light border">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="bookings-table-wrap">
            <div class="table-responsive bookings-table-desktop">
                <table class="table bookings-table align-middle">
                    <thead>
                        <tr>
                            <th>Booking</th>
                            <th>Vehicle</th>
                            <th>Passenger(s)</th>
                            <th>Pickup</th>
                            <th>Drop-off</th>
                            <th>Date &amp; Time</th>
                            <th class="col-form-submitted">Form submitted date and time</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            <tr>
                                <td>
                                    <div class="booking-id">{{ $booking->booking_id ?: 'Reservation #' . $booking->id }}</div>
                                    <div class="booking-id-sub">Record #{{ $booking->id }}</div>
                                </td>
                                <td>
                                    <div class="booking-primary">{{ $booking->vehicle->vehicle_name ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    @if($booking->passengers->isNotEmpty())
                                        <div class="booking-passengers">
                                            <span class="booking-passenger-chip" title="{{ $booking->passengers->map(fn ($passenger) => trim($passenger->first_name . ' ' . $passenger->last_name))->implode(', ') }}">
                                                <i class="bi bi-person"></i>
                                                {{ trim($booking->passengers->first()->first_name . ' ' . $booking->passengers->first()->last_name) }}
                                            </span>
                                            @if($booking->passengers->count() > 1)
                                                <span class="booking-passenger-chip">
                                                    +{{ $booking->passengers->count() - 1 }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="booking-secondary">No passengers</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="booking-location" title="{{ $booking->pickup_location }}">{{ $truncateWords($booking->pickup_location, 3) }}</span>
                                </td>
                                <td>
                                    <span class="booking-location" title="{{ $booking->dropoff_location }}">{{ $truncateWords($booking->dropoff_location, 3) }}</span>
                                </td>
                                <td>
                                    <div class="booking-primary">{{ \Carbon\Carbon::parse($booking->pickup_date)->format('M d, Y') }}</div>
                                    <div class="booking-secondary">{{ substr((string) $booking->pickup_time, 0, 5) }}</div>
                                </td>
                                <td>
                                    @if($booking->created_at)
                                        <div
                                            class="js-form-submitted-local"
                                            data-utc="{{ $booking->created_at->toIso8601String() }}"
                                            data-layout="split"
                                        ></div>
                                    @else
                                        <span class="booking-secondary">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="booking-primary">${{ number_format((float) $booking->total_price, 2) }}</div>
                                </td>
                                <td>
                                    <span class="booking-status-badge {{ $paymentBadgeClass($booking->payment_status) }}">
                                        <i class="bi bi-credit-card-2-front"></i>
                                        {{ $booking->payment_status ?: 'Unknown' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="booking-actions justify-content-end">
                                        <a href="{{ route('bookings.show', $booking->id) }}" class="btn btn-outline-primary booking-action-btn" title="View reservation">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('bookings.edit', $booking->id) }}" class="btn btn-primary booking-action-btn" title="Edit reservation">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form method="POST" action="{{ route('bookings.destroy', $booking->id) }}" onsubmit="return confirm('Delete this reservation permanently?');" class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger booking-action-btn" title="Delete reservation">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10">
                                    <div class="bookings-empty">
                                        <div class="font-weight-bold mb-1">No reservations found</div>
                                        <div>Try changing your search or clearing one or more filters.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bookings-mobile-list">
                @forelse($bookings as $booking)
                    <div class="booking-mobile-card">
                        <div class="d-flex justify-content-between align-items-start mb-3" style="gap: 0.75rem;">
                            <div>
                                <div class="booking-id">{{ $booking->booking_id ?: 'Reservation #' . $booking->id }}</div>
                                <div class="booking-id-sub">Record #{{ $booking->id }}</div>
                            </div>
                            <span class="booking-status-badge {{ $paymentBadgeClass($booking->payment_status) }}">
                                <i class="bi bi-credit-card-2-front"></i>
                                {{ $booking->payment_status ?: 'Unknown' }}
                            </span>
                        </div>

                        <div class="booking-mobile-row">
                            <span class="booking-mobile-label">Vehicle</span>
                            <span class="booking-mobile-value">{{ $booking->vehicle->vehicle_name ?? 'N/A' }}</span>
                        </div>
                        <div class="booking-mobile-row">
                            <span class="booking-mobile-label">Pickup</span>
                            <span class="booking-mobile-value" title="{{ $booking->pickup_location }}">{{ $truncateWords($booking->pickup_location, 3) }}</span>
                        </div>
                        <div class="booking-mobile-row">
                            <span class="booking-mobile-label">Drop-off</span>
                            <span class="booking-mobile-value" title="{{ $booking->dropoff_location }}">{{ $truncateWords($booking->dropoff_location, 3) }}</span>
                        </div>
                        <div class="booking-mobile-row">
                            <span class="booking-mobile-label">Date</span>
                            <span class="booking-mobile-value">{{ \Carbon\Carbon::parse($booking->pickup_date)->format('M d, Y') }} {{ substr((string) $booking->pickup_time, 0, 5) }}</span>
                        </div>
                        <div class="booking-mobile-row">
                            <span class="booking-mobile-label">Form submitted time and date</span>
                            <span class="booking-mobile-value">
                                @if($booking->created_at)
                                    <span
                                        class="js-form-submitted-local"
                                        data-utc="{{ $booking->created_at->toIso8601String() }}"
                                        data-layout="inline"
                                    ></span>
                                @else
                                    —
                                @endif
                            </span>
                        </div>
                        <div class="booking-mobile-row">
                            <span class="booking-mobile-label">Service</span>
                            <span class="booking-mobile-value">{{ $formatServiceOption($booking->service_option) }}</span>
                        </div>
                        <div class="booking-mobile-row">
                            <span class="booking-mobile-label">Total</span>
                            <span class="booking-mobile-value">${{ number_format((float) $booking->total_price, 2) }}</span>
                        </div>
                        <div class="mb-3">
                            <div class="booking-mobile-label mb-2">Passenger(s)</div>
                            @if($booking->passengers->isNotEmpty())
                                <div class="booking-passengers">
                                    @foreach($booking->passengers as $passenger)
                                        <span class="booking-passenger-chip">
                                            <i class="bi bi-person"></i>
                                            {{ trim($passenger->first_name . ' ' . $passenger->last_name) }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="booking-secondary">No passengers</span>
                            @endif
                        </div>
                        <div class="booking-actions">
                            <a href="{{ route('bookings.show', $booking->id) }}" class="btn btn-outline-primary booking-action-btn flex-fill">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('bookings.edit', $booking->id) }}" class="btn btn-primary booking-action-btn flex-fill">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form method="POST" action="{{ route('bookings.destroy', $booking->id) }}" onsubmit="return confirm('Delete this reservation permanently?');" class="m-0 flex-fill">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger booking-action-btn w-100">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="bookings-empty">
                        <div class="font-weight-bold mb-1">No reservations found</div>
                        <div>Try changing your search or clearing one or more filters.</div>
                    </div>
                @endforelse
            </div>

            @if($bookings->hasPages())
                <div class="bookings-pagination mt-4">
                    {{ $bookings->onEachSide(1)->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
(function () {
    var form = document.getElementById('bookings-filters-form');
    if (!form) return;

    var autoSubmitFields = form.querySelectorAll('.js-auto-filter');
    autoSubmitFields.forEach(function (field) {
        field.addEventListener('change', function () {
            form.submit();
        });
    });
})();

(function () {
    function formatFormSubmittedLocal(el) {
        var iso = el.getAttribute('data-utc');
        if (!iso) return;
        var d = new Date(iso);
        if (isNaN(d.getTime())) return;

        var dateStr = d.toLocaleDateString(undefined, {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        });
        var timeStr = d.toLocaleTimeString(undefined, {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true,
        });
        var layout = el.getAttribute('data-layout') || 'inline';

        if (layout === 'split') {
            el.innerHTML =
                '<div class="booking-primary">' + dateStr + '</div>' +
                '<div class="booking-secondary">' + timeStr + '</div>';
        } else {
            el.textContent = dateStr + ' ' + timeStr;
        }
    }

    document.querySelectorAll('.js-form-submitted-local').forEach(formatFormSubmittedLocal);
})();
</script>
@endpush
