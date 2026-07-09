<header class="header-top header-top-nav" header-theme="light">
    <div class="container-fluid">
        <div class="header-top-nav-inner">
            <div class="header-top-left d-flex align-items-center">
                <button type="button" class="btn-icon mobile-nav-toggle d-lg-none" id="header-nav-toggle" aria-label="Toggle menu" aria-expanded="false" aria-controls="header-main-nav">
                    <span></span>
                </button>
                <a class="header-brand-link" href="{{ url('/dashboard') }}" title="Dallas Black Cars Limo Service">
                    <img
                        src="https://dallasblackcarslimoservice.com/img/black-car-service-dallas-logo.webp"
                        alt="Dallas Black Cars Limo Service"
                        class="header-brand-img"
                        width="180"
                        height="42"
                        decoding="async"
                    >
                </a>
            </div>

            @include('include.main-nav')

            <div class="header-top-right top-menu d-flex align-items-center">
                <button class="nav-link" title="Clear Cache">
                    <a href="{{ url('clear-cache') }}">
                        <i class="ik ik-battery-charging"></i>
                    </a>
                </button>
                <div class="dropdown">
                    <a class="dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img class="avatar" src="{{ asset('img/avatar-default.svg') }}" alt="{{ __('Profile') }}">
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="{{ url('logout') }}">
                            <i class="ik ik-power dropdown-icon"></i> {{ __('Logout') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
