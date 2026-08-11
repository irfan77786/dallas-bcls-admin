@extends('layouts.main')
@section('title', __('Drivers'))

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ asset('plugins/DataTables/datatables.min.css') }}">
<style>
    .drv-shell { max-width: 1400px; margin: 0 auto; }
    .drv-hero { margin-bottom: 1.1rem; }
    .drv-title { font-size: 1.85rem; font-weight: 700; color: #17324d; margin-bottom: 0.3rem; }
    .drv-panel { border: 1px solid #e4eaf2; border-radius: 16px; background: #fff; box-shadow: 0 8px 28px rgba(18, 38, 63, 0.06); overflow: visible; }
    .drv-panel .card-header { background: linear-gradient(180deg, #fafcff 0%, #f5f8fc 100%); border-bottom: 1px solid #e8eef5; }
    .drv-dt-body { padding: 0.75rem 0.5rem 1rem; }
    #driversTable_wrapper .dataTables_filter { float: right; }
    #driversTable_wrapper .dataTables_filter input { border-radius: 8px; min-width: 12rem; }
    #driversTable { width: 100% !important; }
    #driversTable thead th { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: #5f7083; }
    .drv-avatar {
        width: 40px; height: 40px; border-radius: 50%; object-fit: cover;
        border: 1px solid #e2e8f0; display: inline-block; vertical-align: middle;
        background: #f1f5f9;
    }
    .drv-avatar--empty {
        display: inline-flex; align-items: center; justify-content: center;
        color: #94a3b8; font-size: 18px;
    }
    .drv-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-weight: 600; color: #1b3552; }
    .table-actions.drv-actions {
        display: inline-flex; align-items: center; justify-content: flex-end; gap: 4px; font-size: 0;
    }
    .table-actions.drv-actions a {
        display: inline-flex; align-items: center; justify-content: center;
        width: 20px; height: 20px; color: #bcc1c6; font-size: 16px; line-height: 1;
    }
    .table-actions.drv-actions a.drv-view { color: #5f7083; }
    .drv-preview {
        width: 72px; height: 72px; border-radius: 12px; object-fit: cover;
        border: 1px solid #e2e8f0; background: #f8fafc;
    }
    .drv-preview-empty {
        width: 72px; height: 72px; border-radius: 12px; border: 1px dashed #cbd5e1;
        display: inline-flex; align-items: center; justify-content: center; color: #94a3b8; background: #f8fafc;
    }
    .drv-view-modal .modal-header { background: linear-gradient(90deg, #17324d 0%, #2a4a66 100%); color: #fff; border-radius: 14px 14px 0 0; }
    .drv-view-dl { margin: 0; }
    .drv-view-dl dt { clear: left; float: left; width: 8rem; color: #8a9aaa; font-size: 0.8rem; font-weight: 600; margin: 0 0 0.4rem; }
    .drv-view-dl dd { margin: 0 0 0.5rem 8rem; color: #1b3552; font-size: 0.95rem; word-break: break-word; }
    @media (max-width: 767.98px) {
        #driversTable_wrapper .dataTables_filter { float: none; margin-bottom: 0.75rem; }
        #driversTable_wrapper .dataTables_filter input { min-width: 0; width: 100% !important; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid drv-shell">
    <div class="row">
        <div class="col-12" style="padding-top:0;">
            <div class="d-flex flex-wrap align-items-end justify-content-between drv-hero">
                <div>
                    <h1 class="drv-title">{{ __('Drivers') }}</h1>
                </div>
                <nav class="breadcrumb-container" aria-label="breadcrumb" style="margin:0;">
                    <ol class="breadcrumb" style="margin:0; background:transparent; padding:0;">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ik ik-home"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Drivers') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card drv-panel">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <h3 class="mb-0" style="color:#1b3552; font-size:1.1rem; font-weight:700;">{{ __('All drivers') }}</h3>
                    <button type="button" class="btn btn-primary rounded-pill px-3" id="btnNewDriver">
                        <i class="ik ik-plus"></i> {{ __('Add driver') }}
                    </button>
                </div>
                <div class="card-body drv-dt-body">
                    <div class="table-responsive" style="overflow-x:auto; -webkit-overflow-scrolling:touch;">
                        <table id="driversTable" class="table table-hover table-bordered align-middle mb-0" style="width:100%; min-width:720px;"></table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Create / Edit modal --}}
<div class="modal fade" id="driverModal" tabindex="-1" aria-labelledby="driverModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 14px; border: none; box-shadow: 0 16px 48px rgba(20, 40, 70, 0.15);">
            <form id="driverForm" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="driver_id" id="form_driver_id" value="">
                <input type="hidden" name="remove_picture" id="form_remove_picture" value="0">
                <div class="modal-header" style="background: linear-gradient(90deg, #1c3a5a 0%, #2d5a80 100%); color: #fff;">
                    <h5 class="modal-title" id="driverModalLabel">{{ __('Driver') }}</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity:1; text-shadow:none;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="background: #fcfdff;">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="f_name">{{ __('Driver Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="f_name" name="name" required maxlength="255" placeholder="Full name">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="f_phone">{{ __('Phone Number') }}</label>
                            <input type="text" class="form-control" id="f_phone" name="phone" maxlength="30" placeholder="(555) 123-4567">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="f_address">{{ __('Address') }}</label>
                        <textarea class="form-control" id="f_address" name="address" rows="2" maxlength="500" placeholder="Street, city, state, ZIP"></textarea>
                    </div>
                    <div class="form-row align-items-end">
                        <div class="form-group col-md-8">
                            <label for="f_picture">{{ __('Driver Profile Pic') }}</label>
                            <input type="file" class="form-control-file" id="f_picture" name="picture" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                            <small class="form-text text-muted">{{ __('JPEG, PNG, GIF or WebP. Max 2 MB.') }}</small>
                        </div>
                        <div class="form-group col-md-4 text-md-right mb-0">
                            <div id="drvPicturePreviewWrap" class="d-inline-block text-center">
                                <div id="drvPicturePreviewEmpty" class="drv-preview-empty"><i class="ik ik-user"></i></div>
                                <img id="drvPicturePreview" class="drv-preview d-none" alt="Preview">
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary d-none" id="btnRemovePicture">{{ __('Remove photo') }}</button>
                            </div>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="small text-uppercase text-muted font-weight-bold mb-2" style="letter-spacing:.06em;">{{ __('Vehicle') }}</div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="f_vehicle_type">{{ __('Vehicle Type') }}</label>
                            <input type="text" class="form-control" id="f_vehicle_type" name="vehicle_type" maxlength="255" placeholder="e.g. SUV, Sedan, Sprinter">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="f_car_make">{{ __('Car Make') }}</label>
                            <input type="text" class="form-control" id="f_car_make" name="car_make" maxlength="255" placeholder="e.g. Cadillac">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="f_car_model">{{ __('Car Model') }}</label>
                            <input type="text" class="form-control" id="f_car_model" name="car_model" maxlength="255" placeholder="e.g. Escalade">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="f_year">{{ __('Year') }}</label>
                            <input type="text" class="form-control" id="f_year" name="year" maxlength="10" placeholder="2024">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="f_color">{{ __('Color') }}</label>
                            <input type="text" class="form-control" id="f_color" name="color" maxlength="50" placeholder="Black">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="f_capacity">{{ __('Capacity') }}</label>
                            <input type="number" class="form-control" id="f_capacity" name="capacity" min="1" max="100" placeholder="Passengers">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="f_active">{{ __('Status') }}</label>
                            <select class="form-control custom-select" id="f_active" name="active">
                                <option value="1">{{ __('Active') }}</option>
                                <option value="0">{{ __('Inactive') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6 mb-md-0">
                            <label for="f_plate_number">{{ __('Plate Number') }}</label>
                            <input type="text" class="form-control" id="f_plate_number" name="plate_number" maxlength="50" placeholder="License plate">
                        </div>
                        <div class="form-group col-md-6 mb-0">
                            <label for="f_vin">{{ __('VIN') }}</label>
                            <input type="text" class="form-control" id="f_vin" name="vin" maxlength="64" placeholder="Vehicle identification number">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary px-4" id="driverSaveBtn">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- View modal --}}
<div class="modal fade drv-view-modal" id="driverViewModal" tabindex="-1" aria-labelledby="driverViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 14px; border: none;">
            <div class="modal-header">
                <h5 class="modal-title" id="driverViewModalLabel">{{ __('Driver') }}</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity:1; text-shadow:none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <img id="drvViewPicture" class="drv-preview d-none" alt="">
                    <div id="drvViewPictureEmpty" class="drv-preview-empty mx-auto"><i class="ik ik-user"></i></div>
                </div>
                <dl class="drv-view-dl" id="drvViewDetails"></dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('plugins/DataTables/datatables.min.js') }}"></script>
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const routes = {
        data: @json(route('drivers.data')),
        store: @json(route('drivers.store')),
        updateBase: @json(url('drivers')),
        deleteBase: @json(url('drivers')) + '/',
        editDataBase: @json(url('drivers')),
    };

    function editDataUrl(id) {
        return routes.editDataBase + '/' + id + '/edit-data';
    }

    const drvTable = $('#driversTable').DataTable({
        processing: true,
        serverSide: true,
        order: [[1, 'desc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        ajax: { url: routes.data, type: 'GET' },
        columns: [
            { data: 'picture_html', name: 'picture', orderable: false, searchable: false, title: @json(__('Photo')), className: 'text-center' },
            { data: 'name', name: 'name', title: @json(__('Driver Name')) },
            { data: 'phone', name: 'phone', title: @json(__('Phone')) },
            { data: 'address', name: 'address', title: @json(__('Address')) },
            { data: 'vehicle_type', name: 'vehicle_type', title: @json(__('Vehicle Type')) },
            { data: 'vehicle_info', name: 'car_model', orderable: false, title: @json(__('Make / Model')) },
            { data: 'plate_number', name: 'plate_number', title: @json(__('Plate')) },
            { data: 'capacity', name: 'capacity', title: @json(__('Capacity')) },
            { data: 'status', name: 'active', orderable: true, searchable: false, title: @json(__('Status')) },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right', title: @json(__('Actions')) }
        ],
        language: {
            search: @json(__('Search')) + ':',
            lengthMenu: @json('Show _MENU_ entries'),
            info: @json('Showing _START_ to _END_ of _TOTAL_ entries'),
            zeroRecords: @json('No matching drivers found'),
            processing: @json('Loading') + '…',
            paginate: { previous: '«', next: '»' }
        }
    });

    const form = document.getElementById('driverForm');
    const previewImg = document.getElementById('drvPicturePreview');
    const previewEmpty = document.getElementById('drvPicturePreviewEmpty');
    const btnRemovePicture = document.getElementById('btnRemovePicture');
    const pictureInput = document.getElementById('f_picture');

    function setPreview(url) {
        if (url) {
            previewImg.src = url;
            previewImg.classList.remove('d-none');
            previewEmpty.classList.add('d-none');
            btnRemovePicture.classList.remove('d-none');
        } else {
            previewImg.removeAttribute('src');
            previewImg.classList.add('d-none');
            previewEmpty.classList.remove('d-none');
            btnRemovePicture.classList.add('d-none');
        }
    }

    function showModal(mode) {
        document.getElementById('driverModalLabel').textContent = mode === 'create'
            ? @json(__('New driver'))
            : @json(__('Edit driver'));
        if (mode === 'create') {
            form.reset();
            document.getElementById('form_driver_id').value = '';
            document.getElementById('form_remove_picture').value = '0';
            document.getElementById('f_active').value = '1';
            setPreview(null);
        }
        $('#driverModal').modal('show');
    }

    document.getElementById('btnNewDriver').addEventListener('click', function () {
        showModal('create');
    });

    pictureInput.addEventListener('change', function () {
        const file = pictureInput.files && pictureInput.files[0];
        if (!file) return;
        document.getElementById('form_remove_picture').value = '0';
        const reader = new FileReader();
        reader.onload = function (ev) { setPreview(ev.target.result); };
        reader.readAsDataURL(file);
    });

    btnRemovePicture.addEventListener('click', function () {
        pictureInput.value = '';
        document.getElementById('form_remove_picture').value = '1';
        setPreview(null);
    });

    function esc(s) {
        if (s == null) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function showViewModal(d) {
        const img = document.getElementById('drvViewPicture');
        const empty = document.getElementById('drvViewPictureEmpty');
        if (d.picture_url) {
            img.src = d.picture_url;
            img.classList.remove('d-none');
            empty.classList.add('d-none');
        } else {
            img.classList.add('d-none');
            empty.classList.remove('d-none');
        }
        document.getElementById('drvViewDetails').innerHTML = ''
            + '<dt>' + @json(__('Driver Name')) + '</dt><dd>' + esc(d.name) + '</dd>'
            + '<dt>' + @json(__('Phone Number')) + '</dt><dd>' + esc(d.phone || '—') + '</dd>'
            + '<dt>' + @json(__('Address')) + '</dt><dd style="white-space:pre-wrap;">' + esc(d.address || '—') + '</dd>'
            + '<dt>' + @json(__('Vehicle Type')) + '</dt><dd>' + esc(d.vehicle_type || '—') + '</dd>'
            + '<dt>' + @json(__('Car Make')) + '</dt><dd>' + esc(d.car_make || '—') + '</dd>'
            + '<dt>' + @json(__('Car Model')) + '</dt><dd>' + esc(d.car_model || '—') + '</dd>'
            + '<dt>' + @json(__('Year')) + '</dt><dd>' + esc(d.year || '—') + '</dd>'
            + '<dt>' + @json(__('Color')) + '</dt><dd>' + esc(d.color || '—') + '</dd>'
            + '<dt>' + @json(__('Capacity')) + '</dt><dd>' + esc(d.capacity != null ? d.capacity : '—') + '</dd>'
            + '<dt>' + @json(__('Plate Number')) + '</dt><dd>' + esc(d.plate_number || '—') + '</dd>'
            + '<dt>' + @json(__('VIN')) + '</dt><dd>' + esc(d.vin || '—') + '</dd>'
            + '<dt>' + @json(__('Status')) + '</dt><dd>' + (d.active ? @json(__('Active')) : @json(__('Inactive'))) + '</dd>';
        document.getElementById('driverViewModalLabel').textContent = @json(__('Driver')) + ' – ' + (d.name || ('#' + d.id));
        $('#driverViewModal').modal('show');
    }

    document.addEventListener('click', function (e) {
        const view = e.target.closest('.drv-view');
        const edit = e.target.closest('.drv-edit');
        const del = e.target.closest('.drv-delete');

        if (view) {
            e.preventDefault();
            fetch(editDataUrl(view.getAttribute('data-id')), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
                .then(function (r) { if (!r.ok) throw new Error('Failed'); return r.json(); })
                .then(function (payload) { showViewModal(payload.driver); })
                .catch(function () { Swal.fire('Error', @json(__('Could not load driver.')), 'error'); });
            return;
        }

        if (edit) {
            e.preventDefault();
            fetch(editDataUrl(edit.getAttribute('data-id')), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
                .then(function (r) { if (!r.ok) throw new Error('Failed'); return r.json(); })
                .then(function (payload) {
                    const d = payload.driver;
                    document.getElementById('form_driver_id').value = d.id;
                    document.getElementById('form_remove_picture').value = '0';
                    document.getElementById('f_name').value = d.name || '';
                    document.getElementById('f_phone').value = d.phone || '';
                    document.getElementById('f_address').value = d.address || '';
                    document.getElementById('f_vehicle_type').value = d.vehicle_type || '';
                    document.getElementById('f_car_make').value = d.car_make || '';
                    document.getElementById('f_car_model').value = d.car_model || '';
                    document.getElementById('f_year').value = d.year || '';
                    document.getElementById('f_color').value = d.color || '';
                    document.getElementById('f_capacity').value = d.capacity != null ? d.capacity : '';
                    document.getElementById('f_plate_number').value = d.plate_number || '';
                    document.getElementById('f_vin').value = d.vin || '';
                    document.getElementById('f_active').value = d.active ? '1' : '0';
                    pictureInput.value = '';
                    setPreview(d.picture_url || null);
                    showModal('edit');
                })
                .catch(function () { Swal.fire('Error', @json(__('Could not load driver.')), 'error'); });
            return;
        }

        if (del) {
            e.preventDefault();
            const id = del.getAttribute('data-id');
            Swal.fire({
                title: @json(__('Delete this driver?')),
                text: @json(__('This action cannot be undone.')),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: @json(__('Yes, delete')),
                cancelButtonText: @json(__('Cancel')),
                confirmButtonColor: '#c75c5c'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                fetch(routes.deleteBase + id, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                    .then(function (r) { return r.json().then(function (body) { return { ok: r.ok, body: body }; }); })
                    .then(function (res) {
                        if (res.ok) {
                            Swal.fire({ icon: 'success', title: @json(__('Deleted')), text: res.body.message || '', timer: 1500, showConfirmButton: false });
                            drvTable.ajax.reload(null, false);
                        } else {
                            Swal.fire('Error', res.body.message || @json(__('Delete failed.')), 'error');
                        }
                    })
                    .catch(function () { Swal.fire('Error', @json(__('Request failed.')), 'error'); });
            });
        }
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const id = document.getElementById('form_driver_id').value;
        const isEdit = !!id;
        const fd = new FormData();
        fd.append('name', document.getElementById('f_name').value.trim());
        fd.append('phone', document.getElementById('f_phone').value.trim());
        fd.append('address', document.getElementById('f_address').value.trim());
        fd.append('vehicle_type', document.getElementById('f_vehicle_type').value.trim());
        fd.append('car_make', document.getElementById('f_car_make').value.trim());
        fd.append('car_model', document.getElementById('f_car_model').value.trim());
        fd.append('year', document.getElementById('f_year').value.trim());
        fd.append('color', document.getElementById('f_color').value.trim());
        fd.append('capacity', document.getElementById('f_capacity').value.trim());
        fd.append('plate_number', document.getElementById('f_plate_number').value.trim());
        fd.append('vin', document.getElementById('f_vin').value.trim());
        fd.append('active', document.getElementById('f_active').value);
        fd.append('remove_picture', document.getElementById('form_remove_picture').value);
        if (pictureInput.files && pictureInput.files[0]) {
            fd.append('picture', pictureInput.files[0]);
        }

        const url = isEdit ? (routes.updateBase + '/' + id + '/update') : routes.store;
        const btn = document.getElementById('driverSaveBtn');
        btn.disabled = true;

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: fd,
            credentials: 'same-origin'
        })
            .then(function (r) { return r.json().then(function (body) { return { ok: r.ok, status: r.status, body: body }; }); })
            .then(function (res) {
                if (res.ok) {
                    $('#driverModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: isEdit ? @json(__('Updated')) : @json(__('Created')),
                        text: res.body.message || '',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    drvTable.ajax.reload(null, false);
                } else {
                    let msg = res.body.message || @json(__('Save failed.'));
                    if (res.body.errors) {
                        const first = Object.keys(res.body.errors)[0];
                        if (first && res.body.errors[first][0]) msg = res.body.errors[first][0];
                    }
                    Swal.fire('Error', msg, 'error');
                }
            })
            .catch(function () { Swal.fire('Error', @json(__('Request failed.')), 'error'); })
            .finally(function () { btn.disabled = false; });
    });
})();
</script>
@endpush
