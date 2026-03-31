<!doctype html>
<html class="no-js" lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>Viva Med Private Portal</title>
        <meta name="description" content="">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="icon" href="{{ asset('favicon.png') }}" type="image/x-icon" />

        <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:300,400,600,700,800" rel="stylesheet">

        <script src="{{ asset('js/app.js') }}"></script>

        <!-- Theme assets -->
        <link rel="stylesheet" href="{{ asset('all.css') }}">
        <link rel="stylesheet" href="{{ asset('dist/css/theme.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/icon-kit/dist/css/iconkit.min.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/ionicons/dist/css/ionicons.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    </head>

    <body class="home-gradient-bg">
		<div class="container">
			<div class="d-flex justify-content-between my-5">
				<div>
		            <img height="30" src="https://i.ibb.co/4RXcLJkg/premiercls-logo.jpg" class="header-brand-img" title="Viva Med">
				</div>
				<div>
					<a class="btn btn-success btn-rounded mr-3" href="{{ url('login') }}">Login</a>
					<a class="btn btn-warning btn-rounded" href="#">Help</a>
				</div>
			</div>

	    	<div class="banner-text m-4 d-relative">
	    		<img height="50" class="d-absolute left-0" src="{{ asset('/img/p1.png') }}">
	    		<img height="300" class="d-absolute" src="{{ asset('/img/s1-2.png') }}">
	    		<img height="50" class="d-absolute right-0" src="{{ asset('/img/s2-2.png') }}">
	    		<strong>Your Secure Employee Portal</strong>
	    	</div>

		    <div class="row justify-content-center">
		        <div class="my-5">
		        	<p class="text-center">Need Assistance?</p>
		        	<div class="card-body template-demo text-center">
                        <a href="#" class="btn social-btn text-white btn-google"><i class="ik ik-globe"></i></a>
                        <a href="#" class="btn social-btn text-white btn-facebook"><i class="fab fa-github"></i></a>
                        <a href="#" class="btn social-btn text-white btn-twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="btn social-btn text-white btn-linkedin"><i class="fab fa-linkedin"></i></a>
                    </div>
		        </div>
		    </div>
		</div>

		<script src="{{ asset('all.js') }}"></script>
    </body>
</html>
