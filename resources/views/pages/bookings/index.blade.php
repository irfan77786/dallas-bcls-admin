@extends('layouts.main')
@section('title', 'Bookings')

@push('head')
<link rel="stylesheet" href="{{ asset('plugins/weather-icons/css/weather-icons.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/owl.carousel/dist/assets/owl.carousel.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/owl.carousel/dist/assets/owl.theme.default.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/chartist/dist/chartist.min.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3>All Bookings</h3>
                    <!-- Future button can go here -->
                </div>

                <div class="card-body">
                    <table id="data_table" class="table table-striped">
                        <thead>
    <tr>
        <th>ID</th>
        <th>Vehicle</th> <!-- New -->
        <th>Passenger(s)</th> <!-- New -->
        <th>Pickup Location</th>
        <th>Dropoff Location</th>
        <th>Pickup Date</th>
        <th>Total Price</th>
        <th>Payment Status</th>
        <th>Action</th>
    </tr>
</thead>
                       <tbody>
    @forelse($bookings as $booking)
    <tr>
        <td>{{ $booking->id }}</td>
        <td>{{ $booking->vehicle->vehicle_name ?? 'N/A' }}</td> <!-- Vehicle relation assumed -->
        <td>
            
                <ul class="mb-0 ps-3">
                    @foreach($booking->passengers as $passenger)
                        <li>{{ $passenger->first_name }} {{ $passenger->last_name }}</li>
                    @endforeach
                </ul>
            
        </td>
        <td>{{ $booking->pickup_location }}</td>
        <td>{{ $booking->dropoff_location }}</td>
        <td>{{ $booking->pickup_date }}</td>
        <td>{{ number_format($booking->total_price, 2) }}</td>
        <td>
            <span class="badge bg-{{ $booking->payment_status == 'Paid' ? 'success' : 'danger' }}">
                {{ $booking->payment_status }}
            </span>
        </td>
        <td>
             <a href="{{ route('bookings.show', $booking->id) }}" class="btn btn-sm btn-primary">
               <i class="ik ik-eye"></i>
            </a>
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="9" class="text-center">No bookings found.</td>
    </tr>
    @endforelse
</tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Flash Messages -->
@if (session('success'))
<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
    <strong>Whoops!</strong> There were some problems with your input.
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
@endsection

@push('script')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('plugins/owl.carousel/dist/owl.carousel.min.js') }}"></script>
<script src="{{ asset('plugins/chartist/dist/chartist.min.js') }}"></script>
<script src="{{ asset('plugins/flot-charts/jquery.flot.js') }}"></script>
<script src="{{ asset('plugins/flot-charts/curvedLines.js') }}"></script>
<script src="{{ asset('plugins/flot-charts/jquery.flot.tooltip.min.js') }}"></script>
<script src="{{ asset('plugins/amcharts/amcharts.js') }}"></script>
<script src="{{ asset('plugins/amcharts/serial.js') }}"></script>
<script src="{{ asset('plugins/amcharts/themes/light.js') }}"></script>
<script src="{{ asset('js/widget-statistic.js') }}"></script>
<script src="{{ asset('js/widget-data.js') }}"></script>
<script src="{{ asset('js/dashboard-charts.js') }}"></script>
@endpush
