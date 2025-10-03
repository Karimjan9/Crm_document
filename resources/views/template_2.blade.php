
<!doctype html>
<html lang="en" class="semi-dark">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
	<link rel="icon" href="assets/images/favicon-32x32.png" type="image/png" />
	<!--plugins-->
	<link href="{{ url('assets_2/plugins/notifications/css/lobibox.min.css') }}" rel="stylesheet"/>
	<link href="{{ url('assets_2/plugins/vectormap/jquery-jvectormap-2.0.2.css') }}" rel="stylesheet"/>
	<link href="{{ url('assets_2/plugins/simplebar/css/simplebar.css') }}" rel="stylesheet" />
	<link href="{{ url('assets_2/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}" rel="stylesheet" />
	<link href="{{ url('assets_2/plugins/metismenu/css/metisMenu.min.css') }}" rel="stylesheet" />
	<!-- loader-->
	<link href="{{ url('assets_2/css/pace.min.css') }}" rel="stylesheet"/>
	<script src="{{ url('assets_2/js/pace.min.js') }}"></script>
	<!-- Bootstrap CSS -->
	<link href="{{ url('assets_2/css/bootstrap.min.css') }}" rel="stylesheet">
	<link href="{{ url('assets_2/css/bootstrap-extended.css') }}" rel="stylesheet">
	<link href="{{ url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap') }}" rel="stylesheet">
	<link href="{{ url('assets_2/css/app.css') }}" rel="stylesheet">
	<link href="{{ url('assets_2/css/icons.css') }}" rel="stylesheet">
	<!-- Theme Style CSS -->
	<link rel="stylesheet" href="{{ url('assets_2/css/dark-theme.css') }}" />
	<link rel="stylesheet" href="{{ url('assets_2/css/semi-dark.css') }}" />
	<link rel="stylesheet" href="{{ url('assets_2/css/header-colors.css') }}" />
    @yield('style')
	@stack('style')
	@yield('script_include_header')

	<title>{{ config('app.name') }}</title>
</head>

<body onload="info_noti()">
	<!--wrapper-->
	<div class="wrapper">
		
		<!--end sidebar wrapper -->
		<!--start header -->
		@include('sidebar.index')
		<!--end sidebar wrapper -->
		<!--start header -->
		@include('header.header')
		<!--end header -->
		<!--start page wrapper -->
		@yield('body')
		<!--end header -->
		<!--start page wrapper -->
		@include('barn.switcher.switcher')
		<!--end page wrapper -->
		<!--start overlay-->
		<div class="overlay toggle-icon"></div>
		<!--end overlay-->
		<!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
		<!--End Back To Top Button-->
		<footer class="page-footer">
			<p class="mb-0">Copyright © 2024. All right reserved.</p>
		</footer>
	</div>
	<!--end wrapper-->
	<!--start switcher-->

	<!--end switcher-->
	<!-- Bootstrap JS -->
	<script src="{{ url('assets_2/js/bootstrap.bundle.min.js') }}"></script>
	<!--plugins-->
	<script src="{{ url('assets_2/js/jquery.min.js') }}"></script>
	<script src="{{ url('assets_2/plugins/simplebar/js/simplebar.min.js') }}"></script>
	<script src="{{ url('assets_2/plugins/metismenu/js/metisMenu.min.js') }}"></script>
	<script src="{{ url('assets_2/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}"></script>
	<script src="{{ url('assets_2/plugins/vectormap/jquery-jvectormap-2.0.2.min.js') }}"></script>
    <script src="{{ url('assets_2/plugins/vectormap/jquery-jvectormap-world-mill-en.js') }}"></script>
	<script src="{{ url('assets_2/plugins/chartjs/js/Chart.min.js') }}"></script>
	<script src="{{ url('assets_2/plugins/chartjs/js/Chart.extension.js') }}"></script>
	<script src="{{ url('assets_2/plugins/sparkline-charts/jquery.sparkline.min.js') }}"></script>
	<!--notification js -->
	<script src="{{ url('assets_2/plugins/notifications/js/lobibox.min.js') }}"></script>

	<script src="{{ url('assets_2/plugins/notifications/js/notifications.min.js') }}"></script>
	<script src="{{ url('assets_2/js/index3.js') }}"></script>
	<!--app JS-->
	<script src="{{ url('assets_2/js/app.js') }}"></script>
    @stack('scripte_include_end_body')
	@yield('scripte_include_end_body')
</body>

</html>