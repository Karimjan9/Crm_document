<!DOCTYPE html>
<html lang="en">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
	<link rel="icon" href="{{ url('assets/images/favicon-32x32.png') }}" type="image/png" />
	<!-- loader-->
	<link href="{{ url('assets/css/pace.min.css') }}" rel="stylesheet" />
	<script src="{{ url('assets/js/pace.min.js') }}"></script>
	<!-- Bootstrap CSS -->
	<link href="{{ url('assets/css/bootstrap.min.css') }}" rel="stylesheet">
	<link href="{{ url('assets/css/bootstrap-extended.css') }}" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
	<link href="{{ url('assets/css/app.css') }}" rel="stylesheet">
	<link href="{{ url('assets/css/icons.css') }}" rel="stylesheet">
	<title>{{ config('app.name') }}</title>
</head>

<body>
	<!-- wrapper -->
	<div class="wrapper">
		<nav class="navbar navbar-expand-lg navbar-light bg-white rounded fixed-top rounded-0 shadow-sm">
			<div class="container-fluid">
				<a class="navbar-brand" href="#">

                    <div class="row">

                        <div class="col-md-5">
                            <img src="{{ url('logo.png') }}" width="10" height="50" class="logo-icon" alt="logo icon">
                        </div>
    
                        <div class="col-md-5 align-self-center">
                            <h4 class="align-middle">
                                {{ config('app.name') }}
                            </h4>   
                        </div>
                    </div>
				</a>
			</div>
		</nav>

        @yield('body')

		<div class="bg-white p-3 fixed-bottom border-top shadow">
			<div class="d-flex align-items-center justify-content-between flex-wrap">
				<ul class="list-inline mb-0">
				</ul>
				<p class="mb-0">Copyright © 2023.</p>
			</div>
		</div>
	</div>
	<!-- end wrapper -->
	<!-- Bootstrap JS -->
	<script src="{{ url('assets/js/bootstrap.bundle.min.js') }}"></script>
</body>

</html>