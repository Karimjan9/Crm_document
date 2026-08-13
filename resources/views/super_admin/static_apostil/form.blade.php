@extends('template')

@section('body')
@php($editing = $apostil->exists)
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>{{ $editing ? 'Apostilni tahrirlash' : 'Yangi apostil' }}</h3>
        <a class="btn btn-light" href="{{ route('superadmin.apostil.index') }}">Orqaga</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ $editing ? route('superadmin.apostil.update', $apostil) : route('superadmin.apostil.store') }}" class="card card-body shadow-sm">
        @csrf
        @if($editing) @method('PUT') @endif

        <div class="mb-3">
            <label class="form-label">Nomi</label>
            <input type="text" name="name" class="form-control" required value="{{ old('name', $apostil->name) }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Guruh</label>
            <select name="group_id" class="form-select" required>
                <option value="1" @selected((int) old('group_id', $apostil->group_id) === 1)>1-guruh</option>
                <option value="2" @selected((int) old('group_id', $apostil->group_id) === 2)>2-guruh</option>
            </select>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">Narx</label>
                <input type="number" name="price" min="0" step="0.01" class="form-control" required value="{{ old('price', $apostil->price) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Kun</label>
                <input type="number" name="days" min="0" step="1" class="form-control" required value="{{ old('days', $apostil->days ?? 0) }}">
            </div>
        </div>
        <button class="btn btn-primary" type="submit">Saqlash</button>
    </form>
</div>
@endsection
