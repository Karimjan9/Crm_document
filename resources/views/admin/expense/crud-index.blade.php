@extends('template')

@section('body')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Xarajatlar</h3>
            <p class="text-muted mb-0">Filial xarajatlarini boshqarish.</p>
        </div>
        <a class="btn btn-primary" href="{{ route($routePrefix . '.expense.create') }}">+ Xarajat qo'shish</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route($routePrefix . '.expense.index') }}" class="card card-body shadow-sm mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Filial</label>
                <select name="filial_id" class="form-select">
                    <option value="">Barchasi</option>
                    @foreach($filials as $filial)
                        <option value="{{ $filial->id }}" @selected((string) request('filial_id') === (string) $filial->id)>{{ $filial->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Foydalanuvchi</label>
                <select name="user_id" class="form-select">
                    <option value="">Barchasi</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }} ({{ $user->login }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Dan</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Gacha</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-outline-primary flex-grow-1" type="submit">Filtrlash</button>
                <a class="btn btn-light" href="{{ route($routePrefix . '.expense.index') }}">Tozalash</a>
            </div>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Sana</th>
                        <th>Filial</th>
                        <th>Foydalanuvchi</th>
                        <th>Kategoriya</th>
                        <th>Vendor</th>
                        <th>Payment</th>
                        <th>Summa</th>
                        <th>Tasdiq</th>
                        <th>Izoh</th>
                        <th class="text-end">Amallar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td>{{ $expense->id }}</td>
                            <td>{{ optional($expense->expense_date ?: $expense->created_at)->format('d.m.Y H:i') }}</td>
                            <td>{{ $expense->filial?->name ?? "Noma'lum" }}</td>
                            <td>{{ $expense->user?->name ?? "Noma'lum" }}</td>
                            <td>{{ $expense->category?->name ?? 'Umumiy' }}</td>
                            <td>{{ $expense->vendor?->name ?? '—' }}</td>
                            <td>{{ match ($expense->payment_method) {
                                'bank_transfer' => 'Bank',
                                'card' => 'Karta',
                                'online' => 'Online',
                                'other' => 'Boshqa',
                                default => 'Naqd',
                            } }}</td>
                            <td>{{ number_format((float) $expense->amount, 2, '.', ' ') }} {{ $expense->currency ?: 'UZS' }}</td>
                            <td>
                                <span class="badge text-bg-{{ $expense->approval_status === 'approved' ? 'success' : ($expense->approval_status === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ $expense->approval_status === 'approved' ? 'Tasdiqlangan' : ($expense->approval_status === 'rejected' ? 'Rad etilgan' : 'Kutilmoqda') }}
                                </span>
                            </td>
                            <td>{{ $expense->description ?: '—' }}</td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route($routePrefix . '.expense.show', $expense) }}">Ko'rish</a>
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route($routePrefix . '.expense.edit', $expense) }}">Tahrirlash</a>
                                    <form method="POST" action="{{ route($routePrefix . '.expense.destroy', $expense) }}" onsubmit="return confirm('Xarajatni o\'chirishni tasdiqlaysizmi?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">O'chirish</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="text-center text-muted py-4">Xarajatlar topilmadi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $expenses->links() }}</div>
    </div>
</div>
@endsection
