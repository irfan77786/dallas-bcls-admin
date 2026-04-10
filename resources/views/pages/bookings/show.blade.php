@extends('layouts.main')
@section('title', 'Booking Details')

@section('content')
<div class="container mt-4">
    <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary mb-3">← Back to Bookings</a>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center flex-wrap">
            <h4 class="mb-0">Booking #{{ $booking->id }}</h4>
            <div class="d-flex flex-wrap align-items-center">
                @if($travelInfo)
                    <span class="mr-2 badge bg-{{ $travelInfo['type'] == 'hourly' ? 'warning' : 'info' }} text-dark">
                        {{ ucfirst(str_replace('_', ' ', $travelInfo['type'])) }} Booking
                    </span>
                @endif
                <span class="ml-2 badge bg-{{ $booking->payment_status == 'Paid' ? 'success' : 'danger' }}">
                    {{ $booking->payment_status }}
                </span>
                @if ($booking->from_admin_reservation)
                    <button type="button" class="btn btn-sm btn-light text-primary font-weight-bold ml-2 mt-2 mt-md-0" data-toggle="modal" data-target="#modalBookingEmailComposer">
                        <i class="ik ik-mail"></i> Send booking emails
                    </button>
                @endif
            </div>
            
        </div>

        <div class="card-body">
            {{-- Booking & Vehicle Info --}}
            <div class="row mb-4">
                {{-- Booking Info --}}
               <div class="col-md-6">
                <h5 class="text-primary">Booking Info</h5>
            
                {{-- Outward Trip Info --}}
                <h6 class="text-secondary">Outward Trip Info</h6>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Pickup:</strong> {{ $booking->pickup_location }}</li>
                    <li class="list-group-item"><strong>Dropoff:</strong> {{ $booking->dropoff_location }}</li>
                    <li class="list-group-item"><strong>Pickup Date:</strong> {{ \Carbon\Carbon::parse($booking->pickup_date)->format('m-d-Y') }} </li>
                    <li class="list-group-item"><strong>Pickup Time:</strong> {{ \Carbon\Carbon::parse($booking->pickup_time)->format('h:i A') }} </li>
            
                    @if($travelInfo)
                        @if($travelInfo['type'] === 'hourly')
                            <li class="list-group-item"><strong>Total Hours:</strong> {{ $travelInfo['hours'] }}</li>
                       
                        @endif
                    @endif
            
                    
                    
                </ul>
                
            
                {{-- Return Trip Info --}}
                @if($booking->returnService)
                    <h6 class="text-secondary mt-3">Return Trip Info</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Pickup:</strong> {{ $booking->returnService->pickup_location }}</li>
                        <li class="list-group-item"><strong>Dropoff:</strong> {{ $booking->returnService->dropoff_location }}</li>
                        <li class="list-group-item"><strong>Pickup Date:</strong> {{ \Carbon\Carbon::parse($booking->returnService->pickup_date)->format('m-d-Y') }}</li>
                        <li class="list-group-item"><strong>Pickup Time:</strong> {{ \Carbon\Carbon::parse($booking->returnService->pickup_time)->format('h:i A') }} </li>
                        
                    </ul>
                @endif
            </div>
            @if($booking->note)
            <div class="col-md-12 note">
                 <h5 class="text-primary">Notes For Chauffer</h5>
                    <p>{{$booking->note}}</p>
            </div>
            @endif

                {{-- Vehicle Info --}}
                <div class="col-md-6">
                    <h5 class="text-primary">Vehicle Info</h5>
                    @if($booking->vehicle)
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Name:</strong> {{ $booking->vehicle->vehicle_name }}</li>
                            <li class="list-group-item"><strong>Code:</strong> {{ $booking->vehicle->vehicle_code }}</li>
                            <li class="list-group-item"><strong>Passengers:</strong> {{ $booking->vehicle->number_of_passengers }}</li>
                            <li class="list-group-item"><strong>Luggage:</strong> {{ $booking->vehicle->luggage_capacity }}</li>
                            <li class="list-group-item"><strong>Greeting Fee:</strong> ${{ number_format($booking->vehicle->greeting_fee, 2) }}</li>
                            <li class="list-group-item"><strong>Base Fare:</strong> ${{ number_format($booking->vehicle->base_fare, 2) }}</li>
                            <li class="list-group-item"><strong>Hourly Fare:</strong> ${{ number_format($booking->vehicle->base_hourly_fare, 2) }}</li>
                            <li class="list-group-item"><strong>Per Mile Rate:</strong> ${{ number_format($booking->vehicle->per_km_rate, 2) }}</li>
                            <li class="list-group-item"><strong>Description:</strong> {{ $booking->vehicle->description }}</li>
                        </ul>
                        @if($booking->vehicle->vehicle_image)
                            <div class="text-center mt-3">
                                <img src="{{ asset('storage/' . $booking->vehicle->vehicle_image) }}" class="img-thumbnail" style="max-width: 250px;" alt="Vehicle Image">
                            </div>
                        @endif
                    @else
                        <p class="text-danger">No vehicle assigned.</p>
                    @endif
                </div>
            </div>

            {{-- Travel Summary --}}
            @if($travelInfo)
            <hr>
            <div class="mb-4">
                <h5 class="text-primary">Travel Summary</h5>
                <ul class="list-group">
                    @if($travelInfo['type'] === 'point_to_point')
                        <li class="list-group-item"><strong>Total Distance:</strong> {{ $travelInfo['distance'] }} Miles</li>
                    @elseif($travelInfo['type'] === 'hourly')
                        <li class="list-group-item"><strong>Total Hours:</strong> {{ $travelInfo['hours'] }}</li>
                    @endif
                    <!--<li class="list-group-item"><strong>Fare:</strong> <span class="badge bg-success">${{ number_format($travelInfo['fare'], 2) }}</span></li>-->
                </ul>
            </div>
            @endif

            {{-- Passenger Info --}}
            <hr>
            <div class="mb-4">
                <h5 class="text-primary">Passenger Info</h5>
                @forelse($booking->passengers as $passenger)
                <div class="border rounded p-3 mb-3">
                    <p><strong>Name:</strong> {{ $passenger->first_name }} {{ $passenger->last_name }}</p>
                    <p><strong>Email:</strong> {{ $passenger->email }}</p>
                    <p><strong>Phone:</strong> {{ $passenger->phone_number }}</p>
                </div>
                @empty
                    <p class="text-warning">No passenger information available.</p>
                @endforelse
            </div>

            {{-- Return Trip Info --}}
            @if($booking->returnService)
            <hr>
            <div class="mb-4">
                <h5 class="text-primary">Return Trip Info</h5>
                <ul class="list-group">
                    <li class="list-group-item"><strong>Pickup:</strong> {{ $booking->returnService->pickup_location }}</li>
                    <li class="list-group-item"><strong>Dropoff:</strong> {{ $booking->returnService->dropoff_location }}</li>
                    <li class="list-group-item"><strong>Date:</strong> {{ $booking->returnService->pickup_date }}</li>
                    <li class="list-group-item"><strong>Time:</strong> {{ $booking->returnService->pickup_time }}</li>
                    @if($booking->returnService->vehicle)
                        <li class="list-group-item"><strong>Vehicle:</strong> {{ $booking->returnService->vehicle->vehicle_name }}</li>
                    @endif
                </ul>
            </div>
            @endif

            {{-- Booker Info --}}
            @if($booking->booker)
            <hr>
            <div class="mb-4">
                <h5 class="text-primary">Booker Info</h5>
                <ul class="list-group">
                    <li class="list-group-item"><strong>Name:</strong> {{ $booking->booker->first_name }} {{ $booking->booker->last_name }}</li>
                    <li class="list-group-item"><strong>Email:</strong> {{ $booking->booker->email }}</li>
                    <li class="list-group-item"><strong>Phone:</strong> {{ $booking->booker->phone_number }}</li>
                </ul>
            </div>
            @endif

            {{-- Flight Info --}}
            @if($booking->passengers && $booking->passengers->count())
            <hr>
            <div class="mb-4">
                <h5 class="text-primary">Flight Details</h5>
                @foreach($booking->passengers as $passenger)
                    @if($passenger->flightDetail)
                    <div class="border rounded p-3 mb-3">
                        <h6 class="text-secondary">Passenger: {{ $passenger->first_name }} {{ $passenger->last_name }}</h6>
                        <ul class="list-group">
                            <li class="list-group-item"><strong>Flight Info:</strong> {{ $passenger->flightDetail->pickup_flight_details }}</li>
                            <li class="list-group-item"><strong>Flight Number:</strong> {{ $passenger->flightDetail->flight_number }}</li>
                            <li class="list-group-item"><strong>Meet Option:</strong> {{ $passenger->flightDetail->meet_option }}</li>
                            <li class="list-group-item"><strong>No Flight Info:</strong> {{ $passenger->flightDetail->no_flight_info ? 'Yes' : 'No' }}</li>
                            <li class="list-group-item"><strong>Inside Pickup Fee:</strong> ${{ number_format($passenger->flightDetail->inside_pickup_fee, 2) }}</li>
                        </ul>
                    </div>
                    @endif
                @endforeach
            </div>
            @endif

            {{-- Price Breakdown --}}
            @if($booking->breakdown)
            <hr>
            <div class="mb-4">
                <h5 class="text-primary">Booking Breakdown</h5>
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Base Fare:</span> <span>${{ number_format($booking->breakdown->base_fare ?? 0, 2) }}</span>
                    </li>
                    @if($travelInfo['type'] === 'point_to_point')
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Per Mile Rate:</span> <span>${{ number_format($booking->breakdown->per_km_rate ?? 0, 2) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Total Miles:</span> <span>{{ $booking->breakdown->total_kms ?? 'N/A' }} Miles</span>
                        </li>
                    @elseif($travelInfo['type'] === 'hourly')
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Hourly Fare:</span> <span>${{ number_format($booking->breakdown->hourly_fare ?? 0, 2) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Total Hours:</span> <span>{{ $booking->breakdown->total_hours ?? 'N/A' }}</span>
                        </li>
                    @endif
                    @if($booking->returnService)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Return Base Fare:</span> <span>${{ number_format($booking->breakdown->return_base_fare ?? 0, 2) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Return Per Mile Rate:</span> <span>${{ number_format($booking->breakdown->return_per_km_rate ?? 0, 2) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Return Miles:</span> <span>{{ $booking->breakdown->return_total_kms ?? 'N/A' }} Miles</span>
                        </li>
                    @endif
                    <li class="list-group-item d-flex justify-content-between fw-bold">
                        <span>Total Price:</span> <span>${{ number_format($booking->total_price, 2) }}</span>
                    </li>
                </ul>
            </div>
            @endif
        </div>
    </div>
</div>

@if ($booking->from_admin_reservation)
    @include('pages.bookings.partials.email-composer-modal')
@endif
@endsection
