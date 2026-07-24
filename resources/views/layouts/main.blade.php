<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if(request()->boolean('embed')) class="la-embed-pending"@endif>
<head>
	<title>@yield('title','') | Viva Med Private Portal</title>
	<!-- initiate head with meta tags, css and script -->
	@include('include.head')

</head>
<body id="app" @if(request()->boolean('embed')) class="la-embed-mode"@endif>
    <div class="wrapper layout-topnav">
    	@unless(request()->boolean('embed'))
    		<!-- initiate header-->
    		@include('include.header')
    	@endunless
    	<div class="page-wrap">
	    	<div class="main-content">
	    		<!-- yeild contents here -->
	    		@yield('content')
	    	</div>

	    	@unless(request()->boolean('embed'))
	    		<!-- initiate chat section-->
	    		@include('include.chat')

	    		<!-- initiate footer section-->
	    		@include('include.footer')
	    	@endunless

    	</div>
    </div>

	@unless(request()->boolean('embed'))
		<!-- initiate modal menu section-->
		@include('include.modalmenu')
	@endunless

	<!-- initiate scripts-->
	@include('include.script')
</body>
</html>
