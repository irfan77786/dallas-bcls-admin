@extends('layouts.main')
@section('title', 'Bookings')
@section('content')

@push('head')
    <link rel="stylesheet" href="{{ asset('plugins/weather-icons/css/weather-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/owl.carousel/dist/assets/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/owl.carousel/dist/assets/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/chartist/dist/chartist.min.css') }}">
@endpush


<div class="container-fluid">
     <div class="row">
         <!--<div class="col-12 col-md-6">-->
         <!--    <h4>All Vehicles</h4>-->
         <!--</div>-->
         <!--<div class="col-md-6 col-12" style="display:flex;justify-content:end">-->
         <!--    <button class="btn">Add New</button>-->
         <!--</div>-->
         
         <div class="col-12">
             <div class="card">
                    <div class="card-header" style="display:flex;justify-content:space-between"><h3>All Bookings</h3>
                    
                    <!--<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#vehicleModal" type="button">Add New</button>-->
                    
                    </div>
                    
                    
                    <div class="card-body">
                        <table id="data_table" class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Id')}}</th>
                                    <th class="nosort">Pickup Location</th>
                                        <th>Dropoff Location</th>
                                    <th>{{ __('Pickup Date')}}</th>
                                    <th>{{ __('Total Price')}}</th>
                                    <th>{{ __('Payment Status')}}</th>
                                    <th class="nosort">{{ __('Action')}}</th>
                                </tr>
                            </thead>
                            
                            <tbody>
                                @foreach($bookings as $key => $value)
                                <tr>
                                    <td>{{$value->id}}</td>
                                    <td>{{$value->pickup_location}}</td>
                                    <td>{{$value->dropoff_location}}</td>
                                    <td>{{$value->pickup_date}}</td>
                                    <td>{{$value->total_price}}</td>
                                    <td>{{$value->payment_status}}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="#"><i class="ik ik-eye"></i></a>
                                            <!--<a href="#"><i class="ik ik-edit-2"></i></a>-->
                                            <!--<a href="#"><i class="ik ik-trash-2"></i></a>-->
                                        </div>
                                    </td>
                                </tr>
                               @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
         </div>
     </div>
</div>
<!-- Success message -->
@if (session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

<!-- Error message -->
@if ($errors->any())
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>Whoops!</strong> There were some problems with your input.
    <ul>
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

<!-- Modal -->
<div class="modal fade" id="vehicleModal" tabindex="-1" aria-labelledby="vehicleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" enctype="multipart/form-data" action="/saveVehicle">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="vehicleModalLabel">Add Vehicle</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        
        <div class="modal-body">
          <!-- Tabs -->
          <ul class="nav nav-tabs" id="vehicleTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="vehicle-tab" data-bs-toggle="tab" data-bs-target="#vehicleInfo" type="button" role="tab">Vehicle Info</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="seat-tab" data-bs-toggle="tab" data-bs-target="#seatInfo" type="button" role="tab">Car Seat Info</button>
            </li>
          </ul>

          <div class="tab-content mt-3" id="vehicleTabContent">
            <!-- Vehicle Info Tab -->
            <div class="tab-pane fade show active" id="vehicleInfo" role="tabpanel">
              <div class="mb-3">
                <label class="form-label">Vehicle Name</label>
                <input type="text" class="form-control" name="vehicle_name" id="vehicle_name" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Vehicle Code</label>
                <input type="text" class="form-control" name="vehicle_code" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Number of Passengers</label>
                <input type="number" class="form-control" name="number_of_passengers" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Luggage Capacity</label>
                <input type="number" class="form-control" name="luggage_capacity" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Greeting Fee</label>
                <input type="number" class="form-control" name="greeting_fee" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Image</label>
                <input type="file" class="form-control" name="vehicle_image" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" required></textarea>
              </div>
              <!--<div class="mb-3">-->
              <!--  <label class="form-label">Slug</label>-->
              <!--  <input type="text" class="form-control" name="slug" id="slug" required>-->
              <!--</div>-->
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="active" value="1" checked>
                <label class="form-check-label">Active</label>
              </div>
            </div>

            <!-- Car Seat Info Tab -->
            <div class="tab-pane fade" id="seatInfo" role="tabpanel">
              <div id="seatRepeater">
                <div class="car-seat-group mb-3 border rounded p-3">
                  <div class="mb-2">
                    <label class="form-label">Category</label>
                    <input type="text" class="form-control" name="car_seats[0][category]" required>
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Quantity</label>
                    <input type="number" class="form-control" name="car_seats[0][quantity]" required>
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Rate</label>
                    <input type="number" class="form-control" name="car_seats[0][rate]" required>
                  </div>
                  <button type="button" class="btn btn-danger btn-sm remove-seat mt-2">Remove</button>
                </div>
              </div>
              <button type="button" id="addSeatBtn" class="btn btn-primary btn-sm mt-2">+ Add Seat Info</button>
            </div>
          </div>
        </div>

        <div class="modal-footer">
  <button type="button" id="nextBtn" class="btn btn-primary">Next</button>
  <button type="submit" id="saveBtn" class="btn btn-success d-none">Save</button>
  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>
      </div>
    </form>
  </div>
</div>


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
    <script>
  document.addEventListener('DOMContentLoaded', function () {
    const nextBtn = document.getElementById('nextBtn');
    const saveBtn = document.getElementById('saveBtn');

    const vehicleTabButton = document.getElementById('vehicle-tab');
    const seatTabButton = document.getElementById('seat-tab');

    const vehicleTabPane = document.getElementById('vehicleInfo');
    const vehicleModal = document.getElementById('vehicleModal');

    function updateFooterButtons(activeTabId) {
      if (activeTabId === 'vehicleInfo') {
        nextBtn.classList.remove('d-none');
        saveBtn.classList.add('d-none');
      } else if (activeTabId === 'seatInfo') {
        nextBtn.classList.add('d-none');
        saveBtn.classList.remove('d-none');
      }
    }

    // Validate Vehicle Info tab
    function validateVehicleTab() {
      const inputs = vehicleTabPane.querySelectorAll('input, textarea');
      for (const input of inputs) {
        if (!input.value.trim()) {
          input.classList.add('is-invalid');
          input.focus();
          return false;
        } else {
          input.classList.remove('is-invalid');
        }
      }
      return true;
    }

    nextBtn.addEventListener('click', function () {
      if (validateVehicleTab()) {
        seatTabButton.click();
      }
    });

    vehicleTabButton.addEventListener('click', function () {
      updateFooterButtons('vehicleInfo');
    });

    seatTabButton.addEventListener('click', function () {
      updateFooterButtons('seatInfo');
    });

    vehicleModal.addEventListener('shown.bs.modal', function () {
      updateFooterButtons('vehicleInfo');
    });
  });
</script>

    <script>
  let seatIndex = 1;

  document.getElementById('addSeatBtn').addEventListener('click', function () {
    const container = document.getElementById('seatRepeater');
    const newGroup = document.createElement('div');
    newGroup.className = 'car-seat-group mb-3 border rounded p-3';
    newGroup.innerHTML = `
      <div class="mb-2">
        <label class="form-label">Category</label>
        <input type="text" class="form-control" name="car_seats[${seatIndex}][category]" required>
      </div>
      <div class="mb-2">
        <label class="form-label">Quantity</label>
        <input type="number" class="form-control" name="car_seats[${seatIndex}][quantity]" required>
      </div>
      <div class="mb-2">
        <label class="form-label">Rate</label>
        <input type="number" class="form-control" name="car_seats[${seatIndex}][rate]" required>
      </div>
      <button type="button" class="btn btn-danger btn-sm remove-seat mt-2">Remove</button>
    `;
    container.appendChild(newGroup);
    seatIndex++;
  });

  document.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-seat')) {
      e.target.closest('.car-seat-group').remove();
    }
  });

  // Auto generate slug from vehicle name
  document.getElementById('vehicle_name').addEventListener('input', function () {
    const name = this.value;
    const slug = name.toLowerCase()
                     .replace(/[^a-z0-9\s-]/g, '')
                     .replace(/\s+/g, '-')
                     .replace(/-+/g, '-');
    document.getElementById('slug').value = slug;
  });
</script>
@endpush

@endsection
