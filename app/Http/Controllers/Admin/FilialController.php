<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilialCreateRequest;
use App\Http\Requests\Admin\FilialUpdateRequest;
use App\Models\DocumentsModel;
use App\Models\ExpenseAdminModel;
use App\Models\FilialModel;
use App\Models\User;

class FilialController extends Controller
{
    protected function routePrefix(): string
    {
        return request()->routeIs('superadmin.*') ? 'superadmin' : 'admin';
    }

    public function index()
    {
        $filials = FilialModel::query()->orderBy('name')->get();

        return view('admin.filial.index', compact('filials'));
    }

    public function create()
    {
        return view('admin.filial.create', ['managers' => $this->managerOptions(true)]);
    }

    public function store(FilialCreateRequest $request)
    {
        FilialModel::create($request->validated());

        return redirect()
            ->route($this->routePrefix() . '.filial.index')
            ->with('success', 'Filial muvaffaqiyatli yaratildi');
    }

    public function edit($id)
    {
        $filial = FilialModel::find($id);

        if (!$filial) {
            return redirect()
                ->route($this->routePrefix() . '.filial.index')
                ->with('error', 'Bunday filial mavjud emas');
        }

        return view('admin.filial.edit', [
            'filial' => $filial,
            'managers' => $this->managerOptions(),
        ]);
    }

    public function update(FilialUpdateRequest $request, FilialModel $filial)
    {
        $filial->update($request->validated());

        return redirect()
            ->route($this->routePrefix() . '.filial.index')
            ->with('success', 'Filial muvaffaqiyatli tahrirlandi');
    }

    public function destroy($id)
    {
        $filial = FilialModel::find($id);

        if (!$filial) {
            return redirect()
                ->route($this->routePrefix() . '.filial.index')
                ->with('error', 'Bunday filial mavjud emas');
        }

        $hasUsers = User::withTrashed()->where('filial_id', $filial->id)->exists();
        $hasDocuments = DocumentsModel::where('filial_id', $filial->id)->exists();
        $hasExpenses = ExpenseAdminModel::where('filial_id', $filial->id)->exists();

        if ($hasUsers || $hasDocuments || $hasExpenses) {
            return redirect()
                ->route($this->routePrefix() . '.filial.index')
                ->with('error', 'Filialni o\'chirishdan oldin unga biriktirilgan foydalanuvchi, hujjat va xarajatlarni ko\'chiring.');
        }

        $filial->delete();

        return redirect()
            ->route($this->routePrefix() . '.filial.index')
            ->with('success', 'Filial muvaffaqiyatli o\'chirildi');
    }

    private function managerOptions(bool $forNewFilial = false)
    {
        return User::query()
            ->whereHas('roles', fn ($roles) => $roles
                ->whereIn('name', $forNewFilial
                    ? ['admin_manager', 'super_admin']
                    : ['admin_filial', 'admin_manager', 'super_admin'])
                ->where('guard_name', 'web'))
            ->orderBy('name')
            ->get(['id', 'name', 'filial_id']);
    }
}
