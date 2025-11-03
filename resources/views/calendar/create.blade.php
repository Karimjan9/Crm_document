@extends('template')

@section('body')
<div class="page-wrapper">
  <div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1>📅 Calendar</h1>
      <a href="{{ route('admin.calendar.create') }}" class="btn btn-primary">
        <i class="bx bx-plus"></i> Yangi qo'shish
      </a>
    </div>
    
    <!-- Calendar content goes here -->
    <div class="card">
      <div class="card-body">
        <p>Calendar ma'lumotlari bu yerda ko'rsatiladi</p>
      </div>
    </div>
  </div>
</div>
@endsection