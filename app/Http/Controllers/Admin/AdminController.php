<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\FilialModel;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;

class AdminController extends Controller
{
    protected function userRoutePrefix(): string
    {
        return request()->routeIs('superadmin.*') ? 'superadmin' : 'admin';
    }

   
    public function index(Request $request)
    {
        $visibleRoles = ['employee', 'courier', 'admin_filial'];
        $filterRole = $request->string('role')->toString();
        $search = trim((string) $request->input('search', ''));
        $filialId = (int) $request->input('filial_id', 0);

        if (! in_array($filterRole, $visibleRoles, true)) {
            $filterRole = null;
        }

        $users = User::query()
            ->when(
                $filterRole,
                fn ($query) => $query->role($filterRole),
                fn ($query) => $query->role($visibleRoles),
            )
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($users) use ($search): void {
                    $users->where('name', 'like', "%{$search}%")
                        ->orWhere('login', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($filialId > 0, fn ($query) => $query->where('filial_id', $filialId))
            ->with('roles', 'filial')
            ->orderByDesc('id')
            ->get();

        $roleLabels = [
            'employee' => 'Xodim',
            'courier' => 'Kuryer',
            'admin_filial' => 'Filial administratori',
        ];

        $stats = [
            'total' => User::role($visibleRoles)->count(),
            'employee' => User::role('employee')->count(),
            'courier' => User::role('courier')->count(),
            'admin_filial' => User::role('admin_filial')->count(),
        ];

        $filters = [
            'search' => $search,
            'role' => $filterRole,
            'filial_id' => $filialId > 0 ? $filialId : null,
        ];
        $filials = FilialModel::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.index', compact('users', 'filters', 'stats', 'filials', 'roleLabels'));
    }

   /**
 * @method bool hasRole(string|array $roles)
 * @method bool hasAnyRole(array|string $roles)
 * @method bool hasAllRoles(array|string $roles)
 */
    public function create(Request $request)
    {
        $rols = Role::whereIn('name', $this->allowedRoles())->orderBy('name')->get();
        $filials = FilialModel::query()->orderBy('name')->get();
        $selectedRole = $request->string('role')->toString();

        if (! in_array($selectedRole, $this->allowedRoles(), true)) {
            $selectedRole = null;
        }

        return view('admin.create', compact('rols', 'filials', 'selectedRole'));
    }

        public function store(StoreUserRequest $request)
    {
       
        $phone = preg_replace('/\D/', '', $request->phone);
        $phone = substr($phone, -9); 


         $user = User::create([
            'name' => $request->name,
            'login' => $request->login,
            'phone' => $phone,
            'password' => Hash::make($request->password),
             'filial_id' => in_array($request->role, ['employee', 'admin_filial', 'courier'], true) ? $request->filial_id : null,
        ]);

       
        $user->assignRole($request->role);

        $returnRole = $request->string('return_role')->toString();
        $returnParameters = in_array($returnRole, ['employee', 'courier', 'admin_filial'], true)
            ? ['role' => $returnRole]
            : [];

        return redirect()->route($this->userRoutePrefix() . '.index', $returnParameters)
            ->with('success', 'Foydalanuvchi muvaffaqiyatli qo‘shildi va roli biriktirildi ✅');
    }

   
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->ensureCanManageUser($user);
        $rols = Role::whereIn('name', $this->allowedRoles())->orderBy('name')->get();
        $filials = FilialModel::query()->orderBy('name')->get();

        return view('admin.edit',compact('user','rols','filials'));
    }

   
   public function update(UpdateUserRequest $request, $id)
{
   
     $user = User::findOrFail($id);
     $this->ensureCanManageUser($user);

 
    $data = $request->validated();

   
    $updateData = [];

     foreach (['name', 'login'] as $field) {
         if (array_key_exists($field, $data) && $data[$field] !== null && $data[$field] !== $user->{$field}) {
             $updateData[$field] = $data[$field];
         }
     }

     $normalizedPhone = substr(preg_replace('/\D/', '', (string) $data['phone']), -9);
     if ($normalizedPhone !== (string) $user->phone) {
         $updateData['phone'] = $normalizedPhone;
     }

    if (!empty($data['password'])) {
        $updateData['password'] = bcrypt($data['password']);
    }

    $currentRole = $user->roles->first()?->name;
    $newRole = $data['role'] ?? $currentRole;

    if (!empty($newRole) && $newRole !== $currentRole) {
        $user->syncRoles([$newRole]);
    }

    if (in_array($newRole, ['employee', 'admin_filial', 'courier'], true)) {
        if (array_key_exists('filial_id', $data) && (int) $data['filial_id'] !== (int) $user->filial_id) {
            $updateData['filial_id'] = $data['filial_id'];
        }
    } elseif ($user->filial_id !== null) {
        $updateData['filial_id'] = null;
    }

    if (!empty($updateData)) {
        $user->update($updateData);
    }

    return redirect()
        ->route($this->userRoutePrefix() . '.index')
        ->with('success', 'Foydalanuvchi ma’lumotlari muvaffaqiyatli yangilandi!');
}

  
    public function destroy($id)
{
    $user = User::findOrFail($id);
    $this->ensureCanManageUser($user);

  
    if (auth()->id() == $user->id) {
        return back()->with('error', 'O‘zingizni o‘chira olmaysiz 😅');
    }

    $user->delete(); 

    return redirect()->route('admin.index')->with('success', 'Foydalanuvchi muvaffaqiyatli o‘chirildi (soft delete)');
}

    protected function allowedRoles(): array
    {
        return auth()->user()?->hasRole('super_admin')
            ? ['employee', 'admin_filial', 'courier', 'admin_manager', 'super_admin']
            : ['employee', 'admin_filial', 'courier'];
    }

    protected function ensureCanManageUser(User $user): void
    {
        $actor = auth()->user();

        if ($actor?->hasRole('super_admin')) {
            return;
        }

        abort_unless(
            $actor?->hasRole('admin_manager')
            && !$user->hasAnyRole(['super_admin', 'admin_manager']),
            403
        );
    }
}
