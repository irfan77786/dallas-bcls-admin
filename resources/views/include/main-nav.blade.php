@php
    $segment1 = request()->segment(1);
@endphp

<nav class="header-main-nav" id="header-main-nav" aria-label="Main navigation">
    <a href="{{ url('/dashboard') }}" class="header-nav-item {{ $segment1 === 'dashboard' ? 'active' : '' }}">
        <i class="ik ik-bar-chart-2"></i>
        <span>{{ __('Dashboard') }}</span>
    </a>
    <a href="{{ url('/bookings') }}" class="header-nav-item {{ in_array($segment1, ['bookings', 'manage-bookings'], true) ? 'active' : '' }}">
        <i class="ik ik-calendar"></i>
        <span>{{ __('Reservations') }}</span>
    </a>
    <a href="{{ route('reservation.create') }}" class="header-nav-item {{ in_array($segment1, ['reservation', 'reservation-v2'], true) ? 'active' : '' }}">
        <i class="ik ik-plus-square"></i>
        <span>{{ __('Add Reservation') }}</span>
    </a>
    <a href="{{ route('reservation.la') }}" class="header-nav-item {{ $segment1 === 'reservation-la' ? 'active' : '' }}">
        <i class="ik ik-file-text"></i>
        <span>{{ __('New Reservation ') }}</span>
    </a>
    <a href="{{ route('dispatches.index') }}" class="header-nav-item {{ $segment1 === 'dispatches' ? 'active' : '' }}">
        <i class="ik ik-activity"></i>
        <span>{{ __('Dispatches') }}</span>
    </a>
    <a href="{{ route('accounts.index') }}" class="header-nav-item {{ $segment1 === 'accounts' ? 'active' : '' }}">
        <i class="ik ik-briefcase"></i>
        <span>{{ __('Accounts') }}</span>
    </a>
    <a href="{{ route('drivers.index') }}" class="header-nav-item {{ $segment1 === 'drivers' ? 'active' : '' }}">
        <i class="ik ik-users"></i>
        <span>{{ __('Drivers') }}</span>
    </a>
    <a href="{{ url('/vehicle') }}" class="header-nav-item {{ in_array($segment1, ['vehicle', 'manage-cars'], true) ? 'active' : '' }}">
        <i class="ik ik-truck"></i>
        <span>{{ __('Manage Vehicles') }}</span>
    </a>
</nav>
