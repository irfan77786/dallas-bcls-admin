@extends('layouts.main')
@section('title', 'Reviews List')

@push('head')
    <link rel="stylesheet" href="{{ asset('plugins/DataTables/datatables.min.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-lg-8">
                <div class="page-header-title">
                    <i class="ik ik-star bg-blue"></i>
                    <div class="d-inline">
                        <h5>{{ __('Reviews List') }}</h5>
                        <span>{{ __('Overview of all employee reviews.') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <nav class="breadcrumb-container" aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}"><i class="ik ik-home"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Reviews') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row clearfix">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3>{{ __('Employee Reviews') }}</h3>
                </div>
                <div class="card-body">
                    <table id="reviewsTable" class="table table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Rating') }}</th>
                                <th>{{ __('Comments') }}</th>
                                <th>{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reviews as $review)
                                <tr>
                                    <td>{{ $review->employee->name }}</td>
                                    <td>
                                        <span class="badge badge-{{ $review->rating >= 4 ? 'success' : ($review->rating >= 3 ? 'warning' : 'danger') }}">
                                            {{ $review->rating }} / 5
                                        </span>
                                    </td>
                                    <td>{{ $review->comment ?: 'N/A' }}</td>
                                    <td>{{ $review->created_at->format('Y-m-d') }}</td>
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
            $('#reviewsTable').DataTable({
                "order": [[3, "desc"]],
                "pageLength": 10
            });
        });
    </script>
@endpush

@endsection
