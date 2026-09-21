@extends('template')

@section('body')
<div class="page-wrapper">
    <div class="page-content">
        <div class="card border-0 shadow-sm" style="max-width:760px">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light text-primary" style="width:52px;height:52px;font-size:25px"><i class="bx bx-user"></i></span>
                    <div>
                        <h4 class="mb-1">Xush kelibsiz, {{ auth()->user()->name }}</h4>
                        <p class="text-muted mb-0">Umumiy foydalanuvchi kabineti</p>
                    </div>
                </div>

                <div class="alert alert-info mb-0">
                    Ushbu hisob tizimga muvaffaqiyatli kirdi. Hozircha unga alohida ishchi bo'limlar biriktirilmagan.
                    Kerakli ruxsatlar berilgach, shu yerda tegishli menyular ko'rinadi.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
