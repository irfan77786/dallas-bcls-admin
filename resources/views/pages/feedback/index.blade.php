@extends('layouts.main')
@section('title', 'Feedbacks List')

@push('head')
    <link rel="stylesheet" href="{{ asset('plugins/DataTables/datatables.min.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-lg-8">
                <div class="page-header-title">
                    <i class="ik ik-comment bg-blue"></i>
                    <div class="d-inline">
                        <h5>{{ __('Feedbacks List') }}</h5>
                        <span>{{ __('Overview of all employee feedbacks.') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <nav class="breadcrumb-container" aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}"><i class="ik ik-home"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Feedbacks') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row clearfix">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3>{{ __('Employee Feedbacks') }}</h3>
                </div>
                <div class="card-body">
                    <table id="feedbacksTable" class="table table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Feedback Type') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($feedbacks as $feedback)
                                <tr>
                                    <td>{{ $feedback->employee->name }}</td>
                                    <td>
                                        <span class="badge badge-{{ $feedback->type == 'complaint' ? 'danger' : 'success' }}">
                                            {{ ucfirst($feedback->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $feedback->description ?: 'N/A' }}</td>
                                    <td>{{ $feedback->created_at->format('Y-m-d') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script')
    <script src="{{ asset('plugins/DataTables/datatables.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#feedbacksTable').DataTable({
                "order": [[3, "desc"]],
                "pageLength": 10
            });
        });
    </script>
@endpush

@endsection
