@extends('template')

@section('style')
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@endsection

@section('content')
<div class="page-wrapper package-admin-shell">
    <div class="page-content">
        <div class="package-admin-hero">
            <span class="package-admin-hero__eyebrow">Superadmin Builder</span>
            <h1>Shablonni yangilang</h1>
            <p>Paket tarkibini, promoli narxini va xodim ko'radigan tavsifni shu sahifadan professional tarzda boshqaring.</p>
        </div>

        <form action="{{ route('superadmin.template_package.update', $templatePackage) }}" method="POST" class="mt-4">
            @csrf
            @method('PUT')
            @include('super_admin.package_templates._form')
        </form>
    </div>
</div>

@include('super_admin.package_templates._builder_assets')
@endsection
