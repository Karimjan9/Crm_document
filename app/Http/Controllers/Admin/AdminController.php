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
   
    public function index()
    {
        $users = User::role(['employee', 'courier'])
        ->with('roles', 'filial')
        ->orderBy('id', 'desc')
        ->get();
        // $filial=FilialModel::
        // dd($users[0]->roles[0]->name);
        return view('admin.index',compact('users'));
    }

   
    public function create()
    {
        $rols=Role::where('name' , 'like', '%employee%')->orWhere('name','like','%courier%')->get();
        // dd($rols);
        $filials=FilialModel::get();
        return view('admin.create',compact('rols','filials'));
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
            'filial_id' => ($request->role === 'employee' && $request->filled('filial_id'))
                ? $request->filial_id
                : null,
        ]);

       
        $user->assignRole($request->role);

        return redirect()->route('admin.index')
            ->with('success', 'Foydalanuvchi muvaffaqiyatli qo‘shildi va roli biriktirildi ✅');
    }

   
    public function show($id)
    {
        //
    }

   
    public function edit($id)
    {
         $rols=Role::where('name' , 'like', '%employee%')->orWhere('name','like','%courier%')->get();
        // dd($rols);
        $filials=FilialModel::get();
        $user=User::find($id);
        return view('admin.edit',compact('user','rols','filials'));
    }

   
   public function update(UpdateUserRequest $request, $id)
{
   
    $user = User::findOrFail($id);

 
    $data = $request->validated();

   
    if (empty($data['password'])) {
        unset($data['password']);
    } else {
        $data['password'] = bcrypt($data['password']);
    }

   
    $user->update([
        'name' => $data['name'],
        'phone' => $data['phone'],
        'login' => $data['login'],
        'password' => $data['password'] ?? $user->password,
        'filial_id' => $data['filial_id'] ?? null,
    ]);

    
    if (!empty($data['role'])) {
        $user->syncRoles([$data['role']]);
    }

    return redirect()
        ->route('admin.index')
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
