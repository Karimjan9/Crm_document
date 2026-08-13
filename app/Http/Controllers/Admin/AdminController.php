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

   
    public function index()
    {
        // dd('here');
        $users = User::role(['employee', 'courier','admin_filial'])
        ->with('roles', 'filial')
        ->orderBy('id', 'desc')
        ->get();
        // $filial=FilialModel::
        // dd($users[0]->roles[0]->name);
        return view('admin.index',compact('users'));
    }

   /**
 * @method bool hasRole(string|array $roles)
 * @method bool hasAnyRole(array|string $roles)
 * @method bool hasAllRoles(array|string $roles)
 */
    public function create()
    {
        $rols = Role::whereIn('name', $this->allowedRoles())->orderBy('name')->get();
        $filials = FilialModel::query()->orderBy('name')->get();

        return view('admin.create', compact('rols', 'filials'));
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

        return redirect()->route($this->userRoutePrefix() . '.index')
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
