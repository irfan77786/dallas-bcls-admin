@extends('layouts.main')
@section('title', __('Accounts'))

@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('plugins/DataTables/datatables.min.css') }}">
    <style>
        .acc-shell { max-width: 1400px; margin: 0 auto; }
        .acc-hero { margin-bottom: 1.1rem; }
        .acc-title { font-size: 1.85rem; font-weight: 700; color: #17324d; margin-bottom: 0.3rem; }
        .acc-sub { color: #6b7b8d; font-size: 0.95rem; margin: 0; }
        .acc-panel { border: 1px solid #e4eaf2; border-radius: 16px; background: #fff; box-shadow: 0 8px 28px rgba(18, 38, 63, 0.06); overflow: visible; }
        .acc-panel .card-header { background: linear-gradient(180deg, #fafcff 0%, #f5f8fc 100%); border-bottom: 1px solid #e8eef5; }
        .acc-dt-body { padding: 0.75rem 0.5rem 1rem; }
        #accountsTable_wrapper .dataTables_filter { float: right; }
        #accountsTable_wrapper .dataTables_filter input { border-radius: 8px; min-width: 12rem; }
        #accountsTable_wrapper .dataTables_length { padding-top: 0.35rem; }
        #accountsTable_wrapper .dataTables_info { padding-top: 0.75rem; }
        #accountsTable_wrapper .dataTables_paginate { padding-top: 0.5rem; }
        .acc-mono-dt { font-family: ui-monospace, "SFMono-Regular", Menlo, Consolas, monospace; font-size: 0.88rem; font-weight: 600; color: #1b3552; }
        #accountsTable { width: 100% !important; }
        #accountsTable thead th { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: #5f7083; }
        @media (max-width: 767.98px) {
            .acc-dt-body .dataTables_wrapper .row .col-sm-6 { flex: 0 0 100%; max-width: 100%; text-align: left !important; }
            #accountsTable_wrapper .dataTables_filter { float: none; margin-bottom: 0.75rem; }
            #accountsTable_wrapper .dataTables_filter input { min-width: 0; width: 100% !important; }
        }
        .accounts-billing-card { background: #f5f8fc; border: 1px solid #e2eaf4; border-radius: 12px; padding: 1rem; margin-top: 0.5rem; }
        .accounts-billing-title { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.08em; color: #5f7083; font-weight: 700; margin-bottom: 0.9rem; }
        .accounts-readonly { background: #eef3f8 !important; color: #2c3e50; font-weight: 600; }
        .acc-view-modal .modal-header { background: linear-gradient(90deg, #17324d 0%, #2a4a66 100%); color: #fff; border-radius: 14px 14px 0 0; }
        .acc-view-body { background: #fcfdff; }
        .acc-view-section { margin-bottom: 1.25rem; }
        .acc-view-section h6 { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.08em; color: #6b7b8d; font-weight: 700; margin-bottom: 0.5rem; }
        .acc-view-dl { margin: 0; }
        .acc-view-dl dt { clear: left; float: left; width: 7.5rem; color: #8a9aaa; font-size: 0.8rem; font-weight: 600; margin: 0 0 0.4rem 0; }
        .acc-view-dl dd { margin: 0 0 0.5rem 7.5rem; color: #1b3552; font-size: 0.95rem; word-break: break-word; }
        .acc-view-dl dd.acc-mono-b { font-family: ui-monospace, "SFMono-Regular", Menlo, Consolas, monospace; font-weight: 600; }
        .acc-view-billing { background: #f3f7fc; border: 1px solid #e2eaf4; border-radius: 12px; padding: 1rem; }
        .table-actions.acc-actions a { color: #bcc1c6; display: inline-block; margin-left: 8px; font-size: 16px; }
        .table-actions.acc-actions a:first-child { margin-left: 0; }
        .table-actions.acc-actions a.acc-view { color: #5f7083; }
    </style>
@endpush

@section('content')
    <div class="container-fluid acc-shell">
        <div class="row">
            <div class="col-12" style="padding-top:0;">
                <div class="d-flex flex-wrap align-items-end justify-content-between acc-hero">
                    <div>
                        <h1 class="acc-title">{{ __('Accounts') }}</h1>
                        <!-- <p class="acc-sub">{{ __('Search, sort, and paginate. Company # is auto-generated.') }}</p> -->
                    </div>
                    <nav class="breadcrumb-container" aria-label="breadcrumb" style="margin:0;">
                        <ol class="breadcrumb" style="margin:0; background:transparent; padding:0;">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ik ik-home"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Accounts') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card acc-panel">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                        <h3 class="mb-0" style="color:#1b3552; font-size:1.1rem; font-weight:700;">{{ __('All accounts') }}</h3>
                        <button type="button" class="btn btn-primary rounded-pill px-3" id="btnNewAccount">
                            <i class="ik ik-plus"></i> {{ __('Add account') }}
                        </button>
                    </div>
                    <div class="card-body acc-dt-body">
                        <p class="small text-muted mb-2 d-md-none">{{ __('Use search and the table toolbars below. Swipe table horizontally on small screens.') }}</p>
                        <div class="table-responsive" style="overflow-x:auto; -webkit-overflow-scrolling:touch;">
                            <table id="accountsTable" class="table table-hover table-bordered align-middle mb-0" style="width:100%; min-width:720px;" aria-describedby="accTableHelp">
                            </table>
                        </div>
                        <p id="accTableHelp" class="sr-only">{{ __('Data table: change page length, search, and use column headers to sort where enabled.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="accountModal" tabindex="-1" aria-labelledby="accountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content" style="border-radius: 14px; border: none; box-shadow: 0 16px 48px rgba(20, 40, 70, 0.15);">
                <form id="accountForm" novalidate>
                    <input type="hidden" name="account_id" id="form_account_id" value="">
                    <div class="modal-header" style="background: linear-gradient(90deg, #1c3a5a 0%, #2d5a80 100%); color: #fff;">
                        <h5 class="modal-title" id="accountModalLabel">{{ __('Account') }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity:1; text-shadow:none;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="background: #fcfdff;">
                        <p class="small text-muted mb-3">{{ __('Company # is auto-generated. Phone uses US 10-digit format with on-screen (555) style.') }}</p>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="form-label">{{ __('Company number') }} <span class="text-muted">({{ __('read-only') }})</span></label>
                                <input type="text" class="form-control accounts-readonly" id="f_company_number" name="f_company_number" readonly placeholder="{{ __('Will be generated') }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label class="form-label">{{ __('Company name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="company_name" id="f_company_name" required maxlength="255">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="form-label">{{ __('Email') }} <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" id="f_email" required maxlength="255">
                            </div>
                            <div class="form-group col-md-6">
                                <label class="form-label">{{ __('Phone') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control acc-phone" name="phone" id="f_phone" inputmode="numeric" autocomplete="tel" required placeholder="(555) 555-0100" maxlength="20">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('Address') }} <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="address" id="f_address" rows="2" required maxlength="2000" placeholder="{{ __('Street, city, state, ZIP') }}"></textarea>
                        </div>
                        <div class="accounts-billing-card">
                            <div class="accounts-billing-title">{{ __('Billing contact') }}</div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="billing_name" id="f_billing_name" required maxlength="255">
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="form-label">{{ __('Email') }} <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="billing_email" id="f_billing_email" required maxlength="255">
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="form-label">{{ __('Phone') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control acc-phone" name="billing_phone" id="f_billing_phone" inputmode="numeric" autocomplete="tel" required placeholder="(555) 555-0100" maxlength="20">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="background: #f6f8fb; border-top: 1px solid #e5eaf0;">
                        <button type="button" class="btn btn-light border" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary" id="accountSaveBtn">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade acc-view-modal" id="accountViewModal" tabindex="-1" role="dialog" aria-labelledby="accountViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content" style="border-radius: 14px; border: none; box-shadow: 0 16px 48px rgba(20, 40, 70, 0.15);">
                <div class="modal-header">
                    <h5 class="modal-title" id="accountViewModalLabel">{{ __('Account details') }}</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="{{ __('Close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body acc-view-body" id="accountViewBody">
                    <div class="acc-view-section">
                        <h6>{{ __('Company') }}</h6>
                        <dl class="acc-view-dl" id="accViewCompany"></dl>
                    </div>
                    <div class="acc-view-billing acc-view-section">
                        <h6 class="text-secondary">{{ __('Billing contact') }}</h6>
                        <dl class="acc-view-dl" id="accViewBilling"></dl>
                    </div>
                    <div class="acc-view-section">
                        <h6>{{ __('Record') }}</h6>
                        <dl class="acc-view-dl" id="accViewRecord"></dl>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f6f8fb; border-top: 1px solid #e5eaf0;">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">{{ __('Close') }}</button>
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
        'use strict';
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const routes = {
            data: @json(route('accounts.data')),
            store: @json(route('accounts.store')),
            updateBase: @json(url('/accounts')) + '/',
            deleteBase: @json(url('/accounts')) + '/',
        };
        const editDataUrl = (id) => '{{ url('/accounts') }}/' + id + '/edit-data';

        if (typeof $ === 'undefined' || !$.fn.DataTable) {
            return;
        }

        const accTable = $('#accountsTable').DataTable({
            processing: true,
            serverSide: true,
            stateSave: false,
            order: [[0, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            ajax: { url: routes.data, type: 'GET' },
            columns: [
                { data: 'company_number', name: 'company_number', title: @json(__('Company #')) },
                { data: 'company_name', name: 'company_name', title: @json(__('Company')) },
                { data: 'email', name: 'email', orderable: true, title: @json(__('Email')) },
                { data: 'phone', name: 'phone', orderable: true, title: @json(__('Phone')) },
                { data: 'address', name: 'address', orderable: true, title: @json(__('Address')) },
                { data: 'billing_name', name: 'billing_name', orderable: false, searchable: false, title: @json(__('Billing name')) },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right', title: @json(__('Actions')) }
            ],
            language: {
                search: @json(__('Search')) + ':',
                lengthMenu: @json('Show _MENU_ entries'),
                info: @json('Showing _START_ to _END_ of _TOTAL_ entries'),
                infoEmpty: @json('Showing 0 to 0 of 0 entries'),
                infoFiltered: @json('(filtered from _MAX_ total entries)'),
                zeroRecords: @json('No matching accounts found'),
                processing: @json('Loading') + '…',
                paginate: { previous: '«', next: '»' }
            }
        });

        function digitsOnly10(val) {
            const d = String(val || '').replace(/\D/g, '');
            if (d.length === 11 && d[0] === '1') return d.slice(1);
            return d.length >= 10 ? d.slice(0, 10) : d;
        }

        function formatUsDisplay(digits) {
            const d = digitsOnly10(digits);
            if (d.length !== 10) return String(digits == null ? '' : digits);
            return '(' + d.slice(0,3) + ') ' + d.slice(3,6) + '-' + d.slice(6);
        }

        function bindPhoneInputs(root) {
            (root || document).querySelectorAll('.acc-phone').forEach((el) => {
                if (el._accPhoneBound) return;
                el._accPhoneBound = true;
                el.addEventListener('input', function () {
                    const d = String(el.value).replace(/\D/g, '');
                    if (d.length > 10) { el.value = formatUsDisplay(d.slice(0,11)); return; }
                    if (d.length === 0) { el.value = ''; return; }
                    if (d.length <= 3) el.value = '(' + d;
                    else if (d.length <= 6) el.value = '(' + d.slice(0,3) + ') ' + d.slice(3);
                    else el.value = '(' + d.slice(0,3) + ') ' + d.slice(3,6) + '-' + d.slice(6,10);
                });
            });
        }
        bindPhoneInputs(document);

        const form = document.getElementById('accountForm');

        function showModal(mode) {
            document.getElementById('accountModalLabel').textContent = mode === 'create'
                ? @json(__('New account'))
                : @json(__('Edit account'));
            if (mode === 'create') {
                form.reset();
                document.getElementById('form_account_id').value = '';
                document.getElementById('f_company_number').value = '';
                document.getElementById('f_company_number').setAttribute('placeholder', @json(__('Auto-assigned on save')));
            }
            bindPhoneInputs(document.getElementById('accountModal'));
            $('#accountModal').modal('show');
        }

        document.getElementById('btnNewAccount').addEventListener('click', function () { showModal('create'); });

        const vLabels = {
            companyNo: @json(__('Company #')),
            company: @json(__('Company name')),
            email: @json(__('Email')),
            phone: @json(__('Phone')),
            address: @json(__('Address')),
            bName: @json(__('Name')),
            created: @json(__('Created')),
            updated: @json(__('Last updated')),
        };

        function accEsc(s) {
            if (s == null) return '';
            return String(s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function accFormatLocal(iso) {
            if (!iso) {
                return '—';
            }
            const d = new Date(iso);
            return isNaN(d.getTime()) ? '—' : d.toLocaleString();
        }

        function showViewModal(a) {
            const c = document.getElementById('accViewCompany');
            c.innerHTML = ''
                + '<dt>' + vLabels.companyNo + '</dt><dd class="acc-mono-b">' + accEsc(a.company_number != null && a.company_number !== '' ? a.company_number : '—') + '</dd>'
                + '<dt>' + vLabels.company + '</dt><dd>' + accEsc(a.company_name) + '</dd>'
                + '<dt>' + vLabels.email + '</dt><dd><a href="mailto:' + accEsc(a.email) + '">' + accEsc(a.email) + '</a></dd>'
                + '<dt>' + vLabels.phone + '</dt><dd>' + accEsc(a.phone || '—') + '</dd>'
                + '<dt>' + vLabels.address + '</dt><dd style="white-space:pre-wrap;">' + accEsc(a.address || '—') + '</dd>';
            const b = document.getElementById('accViewBilling');
            if (a.billing) {
                b.innerHTML = ''
                    + '<dt>' + vLabels.bName + '</dt><dd>' + accEsc(a.billing.name) + '</dd>'
                    + '<dt>' + vLabels.email + '</dt><dd><a href="mailto:' + accEsc(a.billing.email) + '">' + accEsc(a.billing.email) + '</a></dd>'
                    + '<dt>' + vLabels.phone + '</dt><dd>' + accEsc(a.billing.phone || '—') + '</dd>';
            } else {
                b.innerHTML = '<p class="text-muted small mb-0">' + @json(__('No billing contact on file.')) + '</p>';
            }
            const r = document.getElementById('accViewRecord');
            r.innerHTML = ''
                + '<dt>' + vLabels.created + '</dt><dd>' + accFormatLocal(a.created_at) + '</dd>'
                + '<dt>' + vLabels.updated + '</dt><dd>' + accFormatLocal(a.updated_at) + '</dd>';
            document.getElementById('accountViewModalLabel').textContent
                = @json(__('Account')) + ' – ' + (a.company_name || ('#' + a.id));
        }

        document.addEventListener('click', function (e) {
            const view = e.target.closest('.acc-view');
            const edit = e.target.closest('.acc-edit');
            const del = e.target.closest('.acc-delete');
            if (view) {
                e.preventDefault();
                const id = view.getAttribute('data-id');
                fetch(editDataUrl(id), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                    .then(function (r) { if (!r.ok) { throw new Error('Failed'); } return r.json(); })
                    .then(function (payload) {
                        showViewModal(payload.account);
                        $('#accountViewModal').modal('show');
                    })
                    .catch(function () { Swal.fire('Error', @json(__('Could not load account.')), 'error'); });
                return;
            }
            if (edit) {
                e.preventDefault();
                const id = edit.getAttribute('data-id');
                fetch(editDataUrl(id), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                    .then((r) => { if (!r.ok) throw new Error('Failed'); return r.json(); })
                    .then((payload) => {
                        const a = payload.account;
                        document.getElementById('form_account_id').value = a.id;
                        document.getElementById('f_company_number').value = a.company_number || '';
                        document.getElementById('f_company_name').value = a.company_name;
                        document.getElementById('f_email').value = a.email;
                        document.getElementById('f_address').value = a.address;
                        document.getElementById('f_phone').value = a.phone || '';
                        if (a.billing) {
                            document.getElementById('f_billing_name').value = a.billing.name;
                            document.getElementById('f_billing_email').value = a.billing.email;
                            document.getElementById('f_billing_phone').value = a.billing.phone || '';
                        } else {
                            document.getElementById('f_billing_name').value = '';
                            document.getElementById('f_billing_email').value = '';
                            document.getElementById('f_billing_phone').value = '';
                        }
                        showModal('edit');
                    })
                    .catch(() => { Swal.fire('Error', @json(__('Could not load account.')), 'error'); });
                return;
            }
            if (del) {
                e.preventDefault();
                const id = del.getAttribute('data-id');
                Swal.fire({
                    title: @json(__('Delete this account?')),
                    text: @json(__('The billing contact will be removed as well. This action cannot be undone.')),
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: @json(__('Yes, delete')),
                    cancelButtonText: @json(__('Cancel')),
                    confirmButtonColor: '#c75c5c'
                }).then((result) => {
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
                    .then((r) => r.json().then((body) => ({ ok: r.ok, body })))
                    .then(({ ok, body }) => {
                        if (ok) {
                            Swal.fire({ icon: 'success', title: @json(__('Deleted')), text: body.message || '', timer: 1500, showConfirmButton: false });
                            accTable.ajax.reload(null, false);
                        } else {
                            Swal.fire('Error', body.message || @json(__('Delete failed.')), 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error', @json(__('Request failed.')), 'error'));
                });
            }
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const id = document.getElementById('form_account_id').value;
            const isEdit = !!id;
            const phone = digitsOnly10(document.getElementById('f_phone').value);
            const bphone = digitsOnly10(document.getElementById('f_billing_phone').value);
            const payload = {
                company_name: document.getElementById('f_company_name').value.trim(),
                email: document.getElementById('f_email').value.trim(),
                address: document.getElementById('f_address').value.trim(),
                phone: formatUsDisplay(phone),
                billing_name: document.getElementById('f_billing_name').value.trim(),
                billing_email: document.getElementById('f_billing_email').value.trim(),
                billing_phone: formatUsDisplay(bphone)
            };
            if (phone.length !== 10) {
                Swal.fire('Validation', @json(__('Please enter a valid 10-digit US phone for company.')), 'warning');
                return;
            }
            if (bphone.length !== 10) {
                Swal.fire('Validation', @json(__('Please enter a valid 10-digit US phone for billing.')), 'warning');
                return;
            }

            const url = isEdit ? routes.updateBase + id : routes.store;
            const method = isEdit ? 'PUT' : 'POST';
            const btn = document.getElementById('accountSaveBtn');
            btn.disabled = true;

            fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then((r) => r.json().then((body) => ({ ok: r.ok, body })))
            .then(({ ok, body }) => {
                btn.disabled = false;
                if (ok) {
                    $('#accountModal').modal('hide');
                    Swal.fire({ icon: 'success', title: body.message || 'OK', timer: 1500, showConfirmButton: false });
                    accTable.ajax.reload(null, false);
                } else {
                    let msg = body.message || 'Error';
                    if (body.errors) {
                        const first = Object.values(body.errors)[0];
                        if (Array.isArray(first) && first[0]) msg = first[0];
                    }
                    Swal.fire('Error', msg, 'error');
                }
            })
            .catch(() => { btn.disabled = false; Swal.fire('Error', @json(__('Request failed.')), 'error'); });
        });
    })();
    </script>
@endpush
