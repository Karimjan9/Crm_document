@extends('template_error')

@section('body')

<div class="error-404 d-flex align-items-center justify-content-center">
    <div class="container">
        <div class="card py-5">
            <div class="row g-0">
                <div class="col col-xl-5">
                    <div class="card-body p-4">
                        <h1 class="display-1"><span class="text-success">200</span></h1>
                        <h2 class="font-weight-bold display-4">Загрузка первычных скриптов завершена</h2>
                            Загрузка скриптов выполнено успешно !!!
                        <div class="mt-5"> <a href="{{ route('destroy') }}" class="btn btn-primary btn-lg px-md-5 radius-30">{{ __('return_home_page') }}</a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-7">
                    <img src="{{ url('shutterstock_1338315902.png') }}" class="img-fluid" alt="">
                </div>
            </div>
            <!--end row-->
        </div>
    </div>
</div>

@endsection