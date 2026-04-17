@extends('layouts.main')
@section('title', 'Vehicle')
@section('content')

@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ asset('plugins/weather-icons/css/weather-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/owl.carousel/dist/assets/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/owl.carousel/dist/assets/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/chartist/dist/chartist.min.css') }}">
    <style>
        .vehicles-reorder-hint {
            font-size: 0.875rem;
            color: #5f7083;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .vehicles-reorder-hint i { color: #3d5a80; }
        .vehicle-drag-cell { width: 3rem; text-align: center; vertical-align: middle !important; }
        .vehicle-drag-handle {
            cursor: grab;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.4rem 0.35rem;
            border-radius: 8px;
            color: #7a8a9a;
            line-height: 0;
            transition: background 0.15s ease, color 0.15s ease;
        }
        .vehicle-drag-handle:hover {
            background: #eef2f7;
            color: #2c4768;
        }
        .vehicle-drag-handle:active { cursor: grabbing; }
        tr.vehicle-row-ghost {
            opacity: 0.45;
            background: #f0f4f9 !important;
        }
        tr.vehicle-row-chosen {
            background: #e8f0fc !important;
            box-shadow: inset 0 0 0 1px rgba(61, 90, 128, 0.2);
        }
        tr.vehicle-row-dragging {
            opacity: 1;
            background: #fff !important;
            box-shadow: 0 8px 28px rgba(21, 40, 61, 0.12);
        }
        #vehicles-table tbody tr { transition: background 0.12s ease; }
        .vehicle-row-index { font-weight: 600; color: #5f7083; font-variant-numeric: tabular-nums; }
    </style>
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
                    <div class="card-header" style="display:flex;justify-content:space-between"><h3>All Vehicles</h3>
                    
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#vehicleModal" type="button">Add New</button>
                    
                    </div>
                    
                    
                    <div class="card-body">
                        @if(!empty($canReorderVehicles) && $vehicles->isNotEmpty())
                            <p class="vehicles-reorder-hint mb-3">
                                <i class="ik ik-move"></i>
                                {{ __('Drag any row by the grip handle to change the display order. Your changes save automatically.') }}
                            </p>
                        @endif
                        <table id="vehicles-table" class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="nosort" style="width:3rem;">#</th>
                                    @if(!empty($canReorderVehicles))
                                        <th class="nosort text-center vehicle-drag-cell" title="{{ __('Reorder') }}"></th>
                                    @endif
                                    <th>{{ __('Id')}}</th>
                                    <th class="nosort">Name</th>
                                        <th>{{ __('Image')}}</th>
                                    <th>{{ __('Passengers')}}</th>
                                    <th>{{ __('Luggage Capacity')}}</th>
                                    <th>{{ __('Greenting Fee')}}</th>
                                   
                                    <th class="nosort">{{ __('Action')}}</th>
                                </tr>
                            </thead>
                            
                            <tbody @if(!empty($canReorderVehicles)) id="vehicles-sortable-body" @endif>
                                @foreach($vehicles as $key => $value)
                                <tr data-vehicle-id="{{ $value->id }}">
                                    <td class="vehicle-row-index">{{ $loop->iteration }}</td>
                                    @if(!empty($canReorderVehicles))
                                        <td class="vehicle-drag-cell">
                                            <span class="vehicle-drag-handle" title="{{ __('Drag to reorder') }}" role="button" tabindex="0">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                    <circle cx="9" cy="7" r="1.6"/><circle cx="15" cy="7" r="1.6"/>
                                                    <circle cx="9" cy="12" r="1.6"/><circle cx="15" cy="12" r="1.6"/>
                                                    <circle cx="9" cy="17" r="1.6"/><circle cx="15" cy="17" r="1.6"/>
                                                </svg>
                                            </span>
                                        </td>
                                    @endif
                                    <td>{{$value->id}}</td>
                                    <td>{{$value->vehicle_name}}</td>
                                    <td>
                                     @if($value->vehicle_image)
    <img src="{{ asset('storage/' . $value->vehicle_image) }}" alt="Vehicle Image" style="width: 50px;">
@endif

                                    </td>
                                    <td>{{$value->number_of_passengers}}</td>
                                    <td>{{$value->luggage_capacity}}</td>
                                    <td>{{$value->greeting_fee}}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="#"><i class="ik ik-eye"></i></a>
                                            
                                            <a href="#" class="editVehicleBtn" data-id='{{$value->id}}'><i class="ik ik-edit-2"></i></a>
                                           <a href="#" class="deleteVehicleBtn" data-id="{{$value->id}}"><i class="ik ik-trash-2 text-danger"></i></a>
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
<!--Vehicle edit Modal-->
<!-- Edit Vehicle Modal -->
<div class="modal fade" id="editVehicleModal" tabindex="-1" aria-labelledby="editVehicleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" enctype="multipart/form-data" id="editForm" action="/update-vehicle">
      @csrf
      @method('POST') <!-- Change to PUT if using route::put -->
      <input type="hidden" name="vehicle_id" id="edit_vehicle_id">

      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editVehicleModalLabel">Edit Vehicle</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <!-- Tabs -->
          <ul class="nav nav-tabs" id="vehicleEditTab" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#editVehicleInfo" type="button">Vehicle Info</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#editSeatInfo" type="button">Car Seat Info</button></li>
          </ul>

          <div class="tab-content mt-3">
            <!-- Vehicle Info Tab -->
            <div class="tab-pane fade show active" id="editVehicleInfo">
              <div class="mb-3"><label>Vehicle Name</label><input type="text" name="vehicle_name" id="edit_vehicle_name" class="form-control" required></div>
              <div class="mb-3"><label>Vehicle Code</label><input type="text" name="vehicle_code" id="edit_vehicle_code" class="form-control" required></div>
              <div class="mb-3"><label>Passengers</label><input type="number" name="number_of_passengers" id="edit_passengers" class="form-control" required></div>
              <div class="mb-3"><label>Luggage Capacity</label><input type="number" name="luggage_capacity" id="edit_luggage" class="form-control" required></div>
              <div class="mb-3"><label>Greeting Fee</label><input type="number" name="greeting_fee" id="edit_greeting_fee" class="form-control" required></div>
              <div class="mb-3"><label>Base Fare</label><input type="number" name="base_fare" id="edit_base_fare" class="form-control" required step="0.01"></div>
              <div class="mb-3"><label>Hourly Fare</label><input type="number" name="base_hourly_fare" id="edit_hourly_fare" class="form-control" required step="0.01"></div>
              <div class="mb-3"><label>Per Mile Rate</label><input type="number" name="per_km_rate" id="edit_per_km_rate" class="form-control" required step="0.01"></div>
              <div class="mb-3"><label>Image</label><input type="file" name="vehicle_image" class="form-control"></div>
              <div class="mb-3"><label>Description</label><textarea name="description" id="edit_description" class="form-control" required></textarea></div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="active" id="edit_active" value="1">
                <label class="form-check-label">Active</label>
              </div>
            </div>

            <!-- Seat Info Tab -->
            <div class="tab-pane fade" id="editSeatInfo">
              <div id="editSeatRepeater"></div>
              <button type="button" class="btn btn-primary btn-sm mt-2" id="editAddSeatBtn">+ Add Seat Info</button>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Update</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </form>
  </div>
</div>


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
                  <label class="form-label">Base Fare</label>
                  <input type="number" class="form-control" name="base_fare" step="0.01" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Base Hourly Fare</label>
                  <input type="number" class="form-control" name="base_hourly_fare" step="0.01" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Per mile Rate</label>
                  <input type="number" class="form-control" name="per_km_rate" step="0.01" required>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
    
  document.addEventListener('DOMContentLoaded', function () {
      

    $(document).ready(function () {
        @if(!empty($canReorderVehicles))
        (function initVehicleSortable() {
            var tbody = document.getElementById('vehicles-sortable-body');
            if (!tbody || typeof Sortable === 'undefined') {
                return;
            }
            var token = $('meta[name="csrf-token"]').attr('content');
            var saving = false;

            function renumberRows() {
                $(tbody).find('tr').each(function (idx) {
                    $(this).find('td.vehicle-row-index').first().text(idx + 1);
                });
            }

            Sortable.create(tbody, {
                animation: 180,
                handle: '.vehicle-drag-handle',
                ghostClass: 'vehicle-row-ghost',
                chosenClass: 'vehicle-row-chosen',
                dragClass: 'vehicle-row-dragging',
                forceFallback: false,
                onEnd: function (evt) {
                    if (evt.oldIndex === evt.newIndex || saving) {
                        return;
                    }
                    var ids = Array.prototype.map.call(
                        tbody.querySelectorAll('tr[data-vehicle-id]'),
                        function (tr) {
                            return parseInt(tr.getAttribute('data-vehicle-id'), 10);
                        }
                    );
                    saving = true;
                    $.ajax({
                        url: '{{ url('/vehicle/reorder') }}',
                        type: 'POST',
                        data: {
                            _token: token,
                            vehicle_ids: ids
                        },
                        success: function () {
                            renumberRows();
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: '{{ __('Order saved') }}',
                                showConfirmButton: false,
                                timer: 1800,
                                timerProgressBar: true
                            });
                        },
                        error: function (xhr) {
                            var msg = (xhr.responseJSON && xhr.responseJSON.message)
                                ? xhr.responseJSON.message
                                : '{{ __('Could not save order.') }}';
                            Swal.fire('{{ __('Error') }}', msg, 'error').then(function () {
                                window.location.reload();
                            });
                        },
                        complete: function () {
                            saving = false;
                        }
                    });
                }
            });
        })();
        @endif

        $('.deleteVehicleBtn').click(function (e) {
            e.preventDefault();
            var vehicleId = $(this).data('id');
            var token = $('meta[name="csrf-token"]').attr('content');

            Swal.fire({
                title: 'Are you sure?',
                text: "You won’t be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/vehicle/' + vehicleId,
                        type: 'DELETE',
                        data: {
                            _token: token,
                        },
                        success: function (response) {
                            Swal.fire(
                                'Deleted!',
                                'Vehicle has been deleted.',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        },
                        error: function (xhr) {
                            Swal.fire(
                                'Error!',
                                'Something went wrong.',
                                'error'
                            );
                        }
                    });
                }
            });
        });
    });


      
$(document).on('click', '.editVehicleBtn', function () {
    const vehicleId = $(this).data('id');

    $.ajax({
        url: `/vehicle/${vehicleId}/edit`,
        method: 'GET',
        success: function (response) {
            if (response.status) {
                const v = response.vehicle;
console.log(response.vehicle);
                $('#edit_vehicle_id').val(v.id);
                $('#edit_vehicle_name').val(v.vehicle_name);
                $('#edit_vehicle_code').val(v.vehicle_code);
                $('#edit_passengers').val(v.number_of_passengers);
                $('#edit_luggage').val(v.luggage_capacity);
                $('#edit_greeting_fee').val(v.greeting_fee);
                $('#edit_base_fare').val(v.base_fare);
                $('#edit_hourly_fare').val(v.base_hourly_fare);
                $('#edit_per_km_rate').val(v.per_km_rate);
                $('#edit_description').val(v.description);
                $('#edit_active').prop('checked', v.active == 1);

                // Fill car seats
                let seatHtml = '';
                v.car_seat.forEach((seat, i) => {
                    seatHtml += `
                        <div class="car-seat-group mb-3 border rounded p-3">
                            <div class="mb-2">
                                <label>Category</label>
                                <input type="text" name="car_seats[${i}][category]" class="form-control" value="${seat.category}" required>
                            </div>
                            <div class="mb-2">
                                <label>Quantity</label>
                                <input type="number" name="car_seats[${i}][quantity]" class="form-control" value="${seat.quantity}" required>
                            </div>
                            <div class="mb-2">
                                <label>Rate</label>
                                <input type="number" name="car_seats[${i}][rate]" class="form-control" value="${seat.rate}" required>
                            </div>
                        </div>`;
                });
                $('#editSeatRepeater').html(seatHtml);

                $('#editVehicleModal').modal('show');
            }
        },
        error: function () {
            alert('Failed to fetch vehicle data');
        }
    });
});

      
      
        document.querySelectorAll('.editVehicleBtn').forEach(button => {
          button.addEventListener('click', function () {
            const vehicle = this.dataset;
    
            document.getElementById('edit_vehicle_id').value = vehicle.id;
            document.getElementById('edit_vehicle_name').value = vehicle.name;
            document.getElementById('edit_vehicle_code').value = vehicle.code;
            document.getElementById('edit_passengers').value = vehicle.passengers;
            document.getElementById('edit_luggage').value = vehicle.luggage;
            document.getElementById('edit_greeting_fee').value = vehicle.greetingFee;
            document.getElementById('edit_base_fare').value = vehicle.baseFare;
            document.getElementById('edit_base_hourly_fare').value = vehicle.baseHourlyFare;
            document.getElementById('edit_per_km_rate').value = vehicle.perKmRate;
            document.getElementById('edit_description').value = vehicle.description;
            document.getElementById('edit_active').checked = vehicle.active === "1";
          });
        });
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
