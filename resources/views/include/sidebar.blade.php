<div class="app-sidebar colored">
    <div class="sidebar-header">
        <a class="header-brand" href="#">
            <div class="logo-img">
                <img height="30" src="https://i.ibb.co/4RXcLJkg/premiercls-logo.jpg" class="header-brand-img" title="RADMIN">
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

                <!-- Manage Cars -->
                <div class="nav-item {{ ($segment1 == 'manage-cars') ? 'active' : '' }}">
                    <a href="/vehicle">
                        <i class="ik ik-bar-chart-2"></i>
                        <span>{{ __('Manage Vehicle') }}</span>
                    </a>
                </div>

                <!-- Manage Bookings -->
                <div class="nav-item {{ ($segment1 == 'manage-bookings') ? 'active' : '' }}">
                    <a href="/bookings">
                        <i class="ik ik-calendar"></i>
                        <span>{{ __('Manage Bookings') }}</span>
                    </a>
                </div>

                <!-- Reservation (single-page booking) -->
                <div class="nav-item {{ in_array($segment1, ['reservation', 'reservation-v2'], true) ? 'active' : '' }}">
                    <a href="{{ route('reservation.create') }}">
                        <i class="ik ik-plus-square"></i>
                        <span>{{ __('Reservation') }}</span>
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

            </nav>
        </div>
    </div>
</div>
