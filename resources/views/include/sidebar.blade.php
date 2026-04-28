<div class="app-sidebar colored">
    <div class="sidebar-header">
        <a class="header-brand" href="{{ url('/dashboard') }}" title="Dallas Black Cars Limo Service">
            <div class="logo-img">
                <img
                    src="https://dallasblackcarslimoservice.com/img/black-car-service-dallas-logo.webp"
                    alt="Dallas Black Cars Limo Service"
                    class="header-brand-img"
                    width="200"
                    height="48"
                    decoding="async"
                >
            </div>
        </a>
        <div class="sidebar-action"><i class="ik ik-arrow-left-circle"></i></div>
        <button id="sidebarClose" class="nav-close"><i class="ik ik-x"></i></button>
    </div>

    @php
        $segment1 = request()->segment(1);
    @endphp

    <div class="sidebar-content">
        <div class="nav-container">
            <nav id="main-menu-navigation" class="navigation-main">

                <!-- Dashboard (Common link in all routes) -->
                <div class="nav-item {{ ($segment1 == 'dashboard') ? 'active' : '' }}">
                    <a href="/dashboard">
                        <i class="ik ik-bar-chart-2"></i>
                        <span>{{ __('Dashboard') }}</span>
                    </a>
                </div>

                <!-- Reservations -->
                <div class="nav-item {{ ($segment1 == 'manage-bookings') ? 'active' : '' }}">
                    <a href="/bookings">
                        <i class="ik ik-calendar"></i>
                        <span>{{ __('Reservations') }}</span>
                    </a>
                </div>

                <!-- Reservation (single-page booking) -->
                <div class="nav-item {{ in_array($segment1, ['reservation', 'reservation-v2'], true) ? 'active' : '' }}">
                    <a href="{{ route('reservation.create') }}">
                        <i class="ik ik-plus-square"></i>
                        <span>{{ __('Add Reservation') }}</span>
                    </a>
                </div>

                <!-- Manage Drivers -->
                <!--<div class="nav-item {{ ($segment1 == 'manage-drivers') ? 'active' : '' }}">-->
                <!--    <a href="#">-->
                <!--        <i class="ik ik-user"></i>-->
                <!--        <span>{{ __('Manage Cars') }}</span>-->
                <!--    </a>-->
                <!--</div>-->

                <!-- Complaints & Suggestions -->

                <!-- Accounts -->
                <div class="nav-item {{ ($segment1 == 'accounts') ? 'active' : '' }}">
                    <a href="{{ route('accounts.index') }}">
                        <i class="ik ik-briefcase"></i>
                        <span>{{ __('Accounts') }}</span>
                    </a>
                </div>

                <!-- Manage Vehicles (last) -->
                <div class="nav-item {{ ($segment1 == 'vehicle' || $segment1 == 'manage-cars') ? 'active' : '' }}">
                    <a href="/vehicle">
                        <i class="ik ik-truck"></i>
                        <span>{{ __('Manage Vehicles') }}</span>
                    </a>
                </div>

            </nav>
        </div>
    </div>
</div>
