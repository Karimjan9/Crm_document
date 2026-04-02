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
use Illuminate\Validation\Rules\In;

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
    if (auth()->user()->hasAnyRole(['super_admin'])) {
        $rols = Role::where('name', 'like', '%employee%')
            ->orWhere('name', 'like', '%admin_manager%')
            ->orWhere('name', 'like', '%admin_filial%')
            ->orWhere('name', 'like', '%courier%')
            ->get();
        $filials = FilialModel::get();

         return view('admin.create', compact('rols', 'filials'));
    } else {
        $rols = Role::where('name', 'like', '%employee%')
            ->orWhere('name', 'like', '%admin_filial%')
            ->orWhere('name', 'like', '%courier%')
            ->get();
        $filials = FilialModel::get();

        return view('admin.create', compact('rols', 'filials'));
    }

   
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
            'filial_id' => in_array($request->role, ['employee', 'admin_filial'], true) ? $request->filial_id : null,
        ]);

       
        $user->assignRole($request->role);

        return redirect()->route($this->userRoutePrefix() . '.index')
            ->with('success', 'Foydalanuvchi muvaffaqiyatli qo‘shildi va roli biriktirildi ✅');
    }

   
    public function show($id)
    {
        //
    }

   
    public function edit($id)
    {
         $rols = Role::where('name', 'like', '%employee%')
            ->orWhere('name', 'like', '%admin_filial%')
            ->orWhere('name', 'like', '%courier%')
            ->get();
        // dd($rols);
        $filials=FilialModel::get();
        $user=User::find($id);
        return view('admin.edit',compact('user','rols','filials'));
    }

   
   public function update(UpdateUserRequest $request, $id)
{
   
    $user = User::findOrFail($id);

 
    $data = $request->validated();

   
    $updateData = [];

    foreach (['name', 'phone', 'login'] as $field) {
        if (array_key_exists($field, $data) && $data[$field] !== null && $data[$field] !== $user->{$field}) {
            $updateData[$field] = $data[$field];
        }
    }

    if (!empty($data['password'])) {
        $updateData['password'] = bcrypt($data['password']);
    }

    $currentRole = $user->roles->first()?->name;
    $newRole = $data['role'] ?? $currentRole;

    if (!empty($newRole) && $newRole !== $currentRole) {
        $user->syncRoles([$newRole]);
    }

    if (in_array($newRole, ['employee', 'admin_filial'], true)) {
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
        ->route('superadmin.index')
        ->with('success', 'Foydalanuvchi ma’lumotlari muvaffaqiyatli yangilandi!');
}

  
    public function destroy($id)
{
    $user = User::findOrFail($id);

  
    if (auth()->id() == $user->id) {
        return back()->with('error', 'O‘zingizni o‘chira olmaysiz 😅');
    }

    $user->delete(); 

    return redirect()->route('admin.index')->with('success', 'Foydalanuvchi muvaffaqiyatli o‘chirildi (soft delete)');
}
}
