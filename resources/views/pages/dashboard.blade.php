@extends('layouts.main')
@section('title', 'Dashboard')
@section('content')

@push('head')
    <link rel="stylesheet" href="{{ asset('plugins/weather-icons/css/weather-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/owl.carousel/dist/assets/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/owl.carousel/dist/assets/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/chartist/dist/chartist.min.css') }}">
@endpush

<div class="container-fluid">
    <div class="row">
        <!-- Total Bookings -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card card-blue text-white">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h4 class="mb-0">500</h4>
                            <p class="mb-0">Total Bookings</p>
                        </div>
                        <div class="col-4 text-right">
                            <i class="ik ik-calendar f-30"></i>
                        </div>
                    </div>
                    <div id="Widget-line-chart2" class="chart-line chart-shadow"></div>
                </div>
            </div>
        </div>

        <!-- Total Payments -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card card-green text-white">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h4 class="mb-0">$20,000</h4>
                            <p class="mb-0">Total Payments</p>
                        </div>
                        <div class="col-4 text-right">
                            <i class="ik ik-dollar-sign f-30"></i>
                        </div>
                    </div>
                    <div id="Widget-line-chart3" class="chart-line chart-shadow"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Total Cars -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card card-yellow text-white">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h4 class="mb-0">3</h4>
                            <p class="mb-0">Total Cars</p>
                        </div>
                        <div class="col-4 text-right">
                            <i class="ik ik-car f-30"></i>
                        </div>
                    </div>
                    <div id="Widget-line-chart4" class="chart-line chart-shadow"></div>
                </div>
            </div>
        </div>

        <!-- Total Drivers -->

        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card card-red text-white">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h4 class="mb-0">5</h4>
                            <p class="mb-0">Total Drivers</p>
                        </div>
                        <div class="col-4 text-right">
                            <i class="ik ik-user f-30"></i>
                        </div>
                    </div>
                    <div id="Widget-line-chart4" class="chart-line chart-shadow"></div>
                </div>
            </div>
        </div>


</div>

   <!-- Recent Bookings Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3>Recent Bookings</h3>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Car</th>
                            <th>Driver</th>
                            <th>Booking Date</th>
                            <th>Pickup Location</th>
                            <th>Dropoff Location</th>
                            <th>Time</th>
                            <th>Payment Status</th>
                            <th>Trip Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(range(1, 5) as $index) <!-- Dummy Data for Recent Bookings -->
                            <tr>
                                <td>{{ $index }}</td>
                                <td>
                                    @if($index == 1)
                                        Business Sedan
                                    @elseif($index == 2)
                                        EliteX SUV
                                    @else
                                        Luxury SUV
                                    @endif
                                </td>
                                <td>
                                    @if($index == 1)
                                        John Doe
                                    @elseif($index == 2)
                                        David Smith
                                    @elseif($index == 3)
                                        Michael Brown
                                    @elseif($index == 4)
                                        Sarah Lee
                                    @else
                                        James Wilson
                                    @endif
                                </td>
                                <td>{{ now()->subDays($index)->format('M d, Y') }}</td>
                                <td>Dallas, TX - Pickup</td>
                                <td>Dallas, TX - Dropoff</td>
                                <td>{{ now()->subHours($index)->format('h:i A') }}</td>
                                <td>Paid</td>
                                <td>
                                    @if($index == 1)
                                        $130.56
                                    @elseif($index == 2)
                                        $143.36
                                    @else
                                        $166.40
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('script')
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

@endsection
