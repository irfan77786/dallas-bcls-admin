@extends('layouts.main')
@section('title', 'Dashboard')
@section('content')

@php
    $paidPercent = $stats['totalBookings'] > 0 ? round(($stats['paidBookings'] / $stats['totalBookings']) * 100, 1) : 0;
    $pendingPercent = $stats['totalBookings'] > 0 ? round(($stats['pendingBookings'] / $stats['totalBookings']) * 100, 1) : 0;
@endphp

@push('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/chartist/dist/chartist.min.css') }}">
    <style>
        .quick-actions .btn { margin-right: 8px; margin-bottom: 8px; }
        .ct-series-a .ct-line, .ct-series-a .ct-point { stroke: #3366ff; }
        .ct-series-a .ct-area { fill: rgba(51, 102, 255, 0.15); }
        .ct-series-a .ct-point {
            stroke-width: 9px;
            stroke-linecap: round;
            cursor: pointer;
        }
        #booking-trend-chart {
            position: relative;
            min-height: 280px;
        }
        .trend-tooltip {
            position: absolute;
            z-index: 10;
            pointer-events: none;
            opacity: 0;
            transform: translate(-50%, calc(-100% - 14px));
            transition: opacity 0.18s ease;
            min-width: 180px;
            background: #15283d;
            color: #fff;
            border-radius: 10px;
            padding: 10px 12px;
            box-shadow: 0 12px 28px rgba(21, 40, 61, 0.25);
        }
        .trend-tooltip::after {
            content: "";
            position: absolute;
            left: 50%;
            bottom: -6px;
            transform: translateX(-50%);
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-top: 6px solid #15283d;
        }
        .trend-tooltip-title {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.75);
            margin-bottom: 3px;
        }
        .trend-tooltip-value {
            font-size: 18px;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 4px;
        }
        .trend-tooltip-meta {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.82);
        }
        .trend-insight-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(120px, 1fr));
            gap: 10px;
            margin-top: 16px;
        }
        .trend-insight-item {
            border: 1px solid #e9eef5;
            border-radius: 10px;
            background: #f8fbff;
            padding: 10px;
        }
        .trend-insight-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #66788a;
            margin-bottom: 4px;
            font-weight: 700;
        }
        .trend-insight-value {
            color: #16324b;
            font-size: 18px;
            font-weight: 700;
            line-height: 1.1;
        }
        .bookings-table-wrap { padding: 0.25rem 0 0; }
        .bookings-table { margin-bottom: 0; width: 100%; table-layout: fixed; }
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
        .bookings-table thead th.col-form-submitted {
            white-space: normal;
            line-height: 1.25;
            max-width: 6.5rem;
        }
        .bookings-table tbody td {
            vertical-align: middle;
            padding: 0.8rem 0.55rem;
            border-color: #edf1f6;
            font-size: 0.9rem;
        }
        /* 10 columns: avoid overlap; Form submitted + Payment + Actions need room */
        .bookings-table th:nth-child(1), .bookings-table td:nth-child(1) { width: 9%; }
        .bookings-table th:nth-child(2), .bookings-table td:nth-child(2) { width: 8%; }
        .bookings-table th:nth-child(3), .bookings-table td:nth-child(3) { width: 9%; }
        .bookings-table th:nth-child(4), .bookings-table td:nth-child(4) { width: 8%; }
        .bookings-table th:nth-child(5), .bookings-table td:nth-child(5) { width: 8%; }
        .bookings-table th:nth-child(6), .bookings-table td:nth-child(6) { width: 8%; }
        .bookings-table th:nth-child(7), .bookings-table td:nth-child(7) { width: 11%; min-width: 6.5rem; }
        .bookings-table th:nth-child(8), .bookings-table td:nth-child(8) { width: 6%; }
        .bookings-table th:nth-child(9), .bookings-table td:nth-child(9) { width: 10%; min-width: 6.5rem; }
        .bookings-table th:nth-child(10), .bookings-table td:nth-child(10) { width: 15%; min-width: 10rem; }
        .bookings-table tbody tr:hover { background: #fbfdff; }
        .booking-id { font-weight: 700; color: #16324b; font-size: 0.92rem; line-height: 1.25; }
        .booking-id-sub { color: #7c8b9a; font-size: 0.78rem; }
        .booking-primary { font-weight: 600; color: #1b3552; font-size: 0.88rem; line-height: 1.3; }
        .booking-secondary { color: #718093; font-size: 0.78rem; }
        .booking-location {
            font-weight: 600;
            color: #20364f;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
            max-width: 100%;
        }
        .booking-passengers { display: flex; flex-wrap: wrap; gap: 0.35rem; min-width: 0; max-width: 100%; }
        .bookings-table tbody td:nth-child(3) { overflow: hidden; max-width: 0; }
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
            min-width: 0;
            max-width: 100%;
        }
        .booking-passenger-chip > i, .booking-passenger-chip .bi { flex-shrink: 0; }
        .booking-passenger-chip__name {
            flex: 1 1 0; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .booking-passenger-chip--count { flex-shrink: 0; }
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
        .booking-status-paid { background: rgba(25, 135, 84, 0.12); color: #157347; }
        .booking-status-pending { background: rgba(255, 193, 7, 0.18); color: #8f6500; }
        .booking-status-authorized { background: rgba(13, 110, 253, 0.12); color: #0a58ca; }
        .booking-status-default { background: rgba(108, 117, 125, 0.12); color: #495057; }
        .bookings-table .table-actions { text-align: right; }
        .bookings-table .table-actions a { color: #bcc1c6; display: inline-block; margin-left: 8px; font-size: 16px; line-height: 1; }
        .bookings-table .table-actions a:first-child { margin-left: 0; }
        .bookings-empty { padding: 2rem 1rem; text-align: center; color: #768597; }
    </style>
@endpush

<div class="container-fluid">
    @php
        $truncateWords = fn ($value, $limit = 3) => \Illuminate\Support\Str::words(trim((string) $value), $limit, '...');
        $paymentBadgeClass = function ($status) {
            return match (strtolower((string) $status)) {
                'paid' => 'booking-status-paid',
                'pending' => 'booking-status-pending',
                'authorized' => 'booking-status-authorized',
                default => 'booking-status-default',
            };
        };
    @endphp

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Recent Reservations (Latest 10)</h3>
                    <a href="{{ route('bookings.index') }}" class="btn btn-sm btn-outline-primary">See All</a>
                </div>
                <div class="card-body">
                    <div class="bookings-table-wrap">
                    <div class="table-responsive">
                        <table class="table bookings-table align-middle">
                            <thead>
                                <tr>
                                    <th>Reservation</th>
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
                                @forelse($recentBookings as $booking)
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
                                                        <i class="bi bi-person" aria-hidden="true"></i>
                                                        <span class="booking-passenger-chip__name">{{ $booking->passengers->first()->first_name }}</span>
                                                    </span>
                                                    @if($booking->passengers->count() > 1)
                                                        <span class="booking-passenger-chip booking-passenger-chip--count">+{{ $booking->passengers->count() - 1 }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="booking-secondary">No passengers</span>
                                            @endif
                                        </td>
                                        <td><span class="booking-location" title="{{ $booking->pickup_location }}">{{ $truncateWords($booking->pickup_location, 3) }}</span></td>
                                        <td><span class="booking-location" title="{{ $booking->dropoff_location }}">{{ $truncateWords($booking->dropoff_location, 3) }}</span></td>
                                        <td>
                                            <div class="booking-primary">{{ $booking->pickup_date ? \Carbon\Carbon::parse($booking->pickup_date)->format('M d, Y') : 'N/A' }}</div>
                                            <div class="booking-secondary">{{ $booking->pickup_time ? substr((string) $booking->pickup_time, 0, 5) : '--:--' }}</div>
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
                                        <td><div class="booking-primary">${{ number_format((float) $booking->total_price, 2) }}</div></td>
                                        <td>
                                            <span class="booking-status-badge {{ $paymentBadgeClass($booking->payment_status) }}">
                                                <i class="bi bi-credit-card-2-front"></i>
                                                {{ $booking->payment_status ?: 'Unknown' }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <div class="table-actions">
                                                <a href="{{ route('bookings.show', $booking->id) }}" title="{{ __('View') }}">
                                                    <i class="ik ik-eye f-16"></i>
                                                </a>
                                                <a href="{{ route('bookings.edit', $booking->id) }}" title="{{ __('Edit') }}">
                                                    <i class="ik ik-edit-2 f-16 text-primary"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10">
                                            <div class="bookings-empty">
                                                <div class="font-weight-bold mb-1">No reservations found</div>
                                                <div>Recent reservations will appear here automatically.</div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Reservation Trend (Last 6 Months)</h3>
                    <span class="badge badge-primary">Live Data</span>
                </div>
                <div class="card-body">
                    <div id="booking-trend-chart" class="ct-chart ct-major-twelfth"></div>
                    <div id="booking-trend-tooltip" class="trend-tooltip" aria-hidden="true"></div>
                    <div class="trend-insight-grid">
                        <div class="trend-insight-item">
                            <div class="trend-insight-label">6-Month Total</div>
                            <div class="trend-insight-value" id="trend-total-value">0</div>
                        </div>
                        <div class="trend-insight-item">
                            <div class="trend-insight-label">Best Month</div>
                            <div class="trend-insight-value" id="trend-peak-value">-</div>
                        </div>
                        <div class="trend-insight-item">
                            <div class="trend-insight-label">Monthly Average</div>
                            <div class="trend-insight-value" id="trend-avg-value">0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="mb-0">Payments Snapshot</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Paid</span>
                            <span>{{ $stats['paidBookings'] }} ({{ $paidPercent }}%)</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $paidPercent }}%"></div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between">
                            <span>Pending / Unpaid</span>
                            <span>{{ $stats['pendingBookings'] }} ({{ $pendingPercent }}%)</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $pendingPercent }}%"></div>
                        </div>
                    </div>

                    <hr>

                    <div class="quick-actions mt-3">
                        <h6 class="mb-3">Quick Actions</h6>
                        <a href="{{ route('reservation.create') }}" class="btn btn-primary btn-sm">New Reservation</a>
                        <a href="{{ route('bookings.index') }}" class="btn btn-outline-primary btn-sm">View Reservations</a>
                        <a href="{{ route('vehicle') }}" class="btn btn-outline-secondary btn-sm">Manage Vehicles</a>
                        <a href="{{ route('reviews.index') }}" class="btn btn-outline-info btn-sm">Customer Reviews</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script')
    <script src="{{ asset('plugins/chartist/dist/chartist.min.js') }}"></script>
    <script>
        (function () {
            var labels = @json($monthlyBookingLabels);
            var data = @json($monthlyBookingCounts);
            var totalReservations = data.reduce(function (sum, value) { return sum + Number(value || 0); }, 0);
            var peakValue = Math.max.apply(null, data.length ? data : [0]);
            var peakIndex = data.indexOf(peakValue);
            var avgValue = data.length ? (totalReservations / data.length) : 0;

            var totalElement = document.getElementById('trend-total-value');
            var peakElement = document.getElementById('trend-peak-value');
            var avgElement = document.getElementById('trend-avg-value');
            if (totalElement) totalElement.textContent = totalReservations.toLocaleString();
            if (peakElement) peakElement.textContent = (peakIndex >= 0 ? labels[peakIndex] : '-') + ' (' + peakValue + ')';
            if (avgElement) avgElement.textContent = avgValue.toFixed(1);

            var chart = new Chartist.Line('#booking-trend-chart', {
                labels: labels,
                series: [data]
            }, {
                fullWidth: true,
                showArea: true,
                showPoint: true,
                chartPadding: { right: 20 },
                axisY: {
                    onlyInteger: true,
                    offset: 30
                },
                lineSmooth: Chartist.Interpolation.simple({
                    divisor: 2
                })
            });

            var tooltip = document.getElementById('booking-trend-tooltip');
            var chartContainer = document.getElementById('booking-trend-chart');

            chart.on('draw', function (context) {
                if (context.type !== 'point') return;

                var node = context.element && context.element._node;
                if (!node || !tooltip || !chartContainer) return;

                var index = context.index;
                node.setAttribute('data-month', labels[index]);
                node.setAttribute('data-value', data[index]);
                node.setAttribute('data-share', totalReservations > 0 ? ((data[index] / totalReservations) * 100).toFixed(1) : '0.0');

                node.addEventListener('mouseenter', function () {
                    tooltip.innerHTML =
                        '<div class="trend-tooltip-title">' + node.getAttribute('data-month') + '</div>' +
                        '<div class="trend-tooltip-value">' + node.getAttribute('data-value') + ' reservations</div>' +
                        '<div class="trend-tooltip-meta">Share of 6-month total: ' + node.getAttribute('data-share') + '%</div>';
                    tooltip.style.opacity = '1';
                });

                node.addEventListener('mousemove', function (event) {
                    var bounds = chartContainer.getBoundingClientRect();
                    var x = event.clientX - bounds.left;
                    var y = event.clientY - bounds.top;
                    tooltip.style.left = x + 'px';
                    tooltip.style.top = y + 'px';
                });

                node.addEventListener('mouseleave', function () {
                    tooltip.style.opacity = '0';
                });
            });
        })();
    </script>
    <script>
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

@endsection
