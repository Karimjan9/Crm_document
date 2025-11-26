<!doctype html>
<html class="semi-dark">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
	{{-- <link rel="icon" href="{{ url('logo.png') }}" type="image/png" /> --}}
	<!--plugins-->
	{{-- <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> --}}
	<link href="{{ url('assets/plugins/notifications/css/lobibox.min.css') }}" rel="stylesheet"/>
	<link href="{{ url('assets/plugins/vectormap/jquery-jvectormap-2.0.2.css') }}" rel="stylesheet"/>
	<link href="{{ url('assets/plugins/simplebar/css/simplebar.css') }}" rel="stylesheet" />
	<link href="{{ url('assets/plugins/input-tags/css/tagsinput.css') }}" rel="stylesheet" />
	<link href="{{ url('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
	<link href="{{ url('assets/plugins/select2/css/select2-bootstrap4.css') }}" rel="stylesheet" />
	<link href="{{ url('assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}" rel="stylesheet" />
	<link href="{{ url('assets/plugins/metismenu/css/metisMenu.min.css') }}" rel="stylesheet" />
	<!-- loader-->
	<link href="{{ url('assets/css/pace.min.css') }}" rel="stylesheet"/>
	<script src="{{ url('assets/js/pace.min.js') }}"></script>

	<script src="{{ url('assets/js/jquery.min.js') }}"></script>

	<!-- Bootstrap CSS -->
	<link href="{{ url('assets/css/bootstrap.min.css') }}" rel="stylesheet">
	<link href="{{ url('assets/css/bootstrap-extended.css') }}" rel="stylesheet">
	<link href="{{ url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap') }}" rel="stylesheet">
	<link href="{{ url('https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&display=swap') }}" rel="stylesheet"> 
	<link href="{{ url('assets/css/app.css') }}" rel="stylesheet">
	<link href="{{ url('assets/css/icons.css') }}" rel="stylesheet">
	<!-- Theme Style CSS -->
	<link rel="stylesheet" href="{{ url('assets/css/dark-theme.css') }}" />
	<link rel="stylesheet" href="{{ url('assets/css/semi-dark.css') }}" />
	<link rel="stylesheet" href="{{ url('assets/css/header-colors.css') }}" />

	@yield('style')
	@stack('style')
	@yield('script_include_header')

	<title>{{ config('app.name') }}</title>
</head>

<body>
	<!--wrapper-->
	<div class="wrapper">
		<!--sidebar wrapper -->
		@include('sidebar.index')
		<!--end sidebar wrapper -->
		<!--start header -->
		@include('header')
		<!--end header -->
		<!--start page wrapper -->
		@yield('body')
		<!--end page wrapper -->
		 @yield('script')
		<!--start overlay-->
		<div class="overlay toggle-icon"></div>
		<!--end overlay-->
		<!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
		<!--End Back To Top Button-->
	<footer class="page-footer">
    {{-- <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');

        .page-footer {
            background-color: #1e1e2f; /* sidebar rangiga o'xshash */
            color: #fff;
            padding: 5px 6px;
            font-family: 'Poppins', sans-serif;
            text-align: center;
            overflow: hidden;
            position: relative;
        }

        .page-footer::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, rgba(0,123,255,0.3), rgb(192, 192, 192), rgba(255,0,150,0.3));
            animation: rotate 10s linear infinite;
            z-index: 0;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .footer-content {
            position: relative;
            z-index: 1;
        }

        .footer-copy {
            font-weight: 500;
            font-size: 0.95rem;
        }

        .footer-copy span {
            font-weight: 600;
            color: #00ffc3; /* animatsiya bilan mos rang */
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); color: #00ffc3; }
            50% { transform: scale(1.2); color: #ff4d6d; }
        }
    </style> --}}

    <div class="footer-content">
        <p class="footer-copy">Copyright © <span>2024</span>. All rights reserved.</p>
    </div>
</footer>

	</div>
	<!--end wrapper-->
	<!--start switcher-->
	<!--end switcher-->
	<!-- Bootstrap JS -->
	<script src="{{ url('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}"></script>
	<script src="{{ url('assets/js/bootstrap.bundle.min.js') }}"></script>
	<!--plugins-->
	<script src="{{ url('assets/plugins/simplebar/js/simplebar.min.js') }}"></script>
	<script src="{{ url('assets/plugins/metismenu/js/metisMenu.min.js') }}"></script>
	<script src="{{ url('assets/plugins/vectormap/jquery-jvectormap-2.0.2.min.js') }}"></script>
    <script src="{{ url('assets/plugins/vectormap/jquery-jvectormap-world-mill-en.js') }}"></script>
	<script src="{{ url('assets/plugins/chartjs/js/Chart.min.js') }}"></script>
	<script src="{{ url('assets/plugins/input-tags/js/tagsinput.js') }}"></script>
	<script src="{{ url('assets/plugins/chartjs/js/Chart.extension.js') }}"></script>
	<script src="{{ url('assets/plugins/sparkline-charts/jquery.sparkline.min.js') }}"></script>
	<!--notification js -->
	<script src="{{ url('assets/plugins/notifications/js/lobibox.min.js') }}"></script>
	<script src="{{ url('assets/plugins/notifications/js/notifications.min.js') }}"></script>
	<script src="{{ url('assets/js/index3.js') }}"></script>
	<!--app JS-->
	<script src="{{ url('assets/js/app.js') }}"></script>
	@stack('script_include_end_body')
	@yield('script_include_end_body')
</body>

</html>
