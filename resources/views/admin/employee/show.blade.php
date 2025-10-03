@extends('template')

@section('body')

<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Xodimlar</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route("superadmin.employees.index") }}"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $user->full_name }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->
        <div class="container">
            <div class="main-body">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-column align-items-center text-center">
                                    <img src="{{ url('avatar-1.png') }}" alt="Admin" class="rounded-circle p-1 bg-primary" width="110">
                                    <div class="mt-3">
                                        <h4>{{ $user->full_name }}</h4>
                                    </div>
                                </div>
                                <hr class="my-4">
                                <div class="row">
                                    <div class="col-4">
                                        <h4>Ariza</h4>
                                    </div>
                                    <div class="col-2">
                                        <h4 class="text-secondary">
                                            {{ $user->announcement_count }}
                                        </h4>
                                    </div>
                                    <div class="col-2">
                                        <h4 class="text-success">
                                            {{ $user->announcement_accept_count }}
                                        </h4>
                                    </div>
                                    <div class="col-2">
                                        <h4 class="text-danger">
                                            {{ $user->announcement_reject_count }}
                                        </h4>
                                    </div>
                                </div>
                                <hr class="my-4">
                                <div class="row">
                                    <div class="col-4">
                                        <h4>Bildirgi</h4>
                                    </div>
                                    <div class="col-2">
                                        <h4 class="text-secondary">
                                            {{ $user->statement_count }}
                                        </h4>
                                    </div>
                                    <div class="col-2">
                                        <h4 class="text-success">
                                            {{ $user->statement_accept_count }}
                                        </h4>
                                    </div>
                                    <div class="col-2">
                                        <h4 class="text-danger">
                                            {{ $user->statement_reject_count }}
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">{{ __("full_name") }}</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" class="form-control" value="{{ $user->full_name }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="button" class="btn btn-primary px-4" value="{{ __("save") }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="d-flex align-items-center mb-3">Ko'rsatkichlar</h5>
                                        <h3>{{ $user->get_percent_teacher() }}%</h3>
                                        <div class="progress mb-3" style="height: 5px">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $user->get_percent_teacher() }}%" aria-valuenow="{{ $user->get_percent_teacher() }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>

                                        <div class="row">
                                            <div class="col-12">
                                                
                                                @foreach ($user->on_criteria_active as $num => $item)

                                                    @if ($item->increase == 'positive')
                                                        <div class="mb-4">
                                                            <p class="mb-2"> {{ $item->criteria->name }} <span class="float-end">{{ $item->data }}%</span></p>
                                                            <div class="progress" style="height: 7px;">
                                                                <div class="progress-bar bg-primary progress-bar-striped" role="progressbar" style="width: {{ $item->data }}%"></div>
                                                            </div>
                                                        </div>
                                                    @else
                                                    
                                                        <div class="mb-4">
                                                            <p class="mb-2"> {{ $item->criteria->name }} <span class="float-end">-{{ $item->data }}%</span></p>
                                                            <div class="progress" style="height: 7px;">
                                                                <div class="progress-bar bg-danger progress-bar-striped" role="progressbar" style="width: {{ $item->data }}%"></div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                
                                                @endforeach
            
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    
@endsection