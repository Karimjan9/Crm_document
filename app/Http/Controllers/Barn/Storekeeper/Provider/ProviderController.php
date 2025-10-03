<?php

namespace App\Http\Controllers\Barn\Storekeeper\Provider;

use App\Http\Controllers\Controller;
use App\Models\ProviderModel;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
   
    public function index()
    {
        $providers=ProviderModel::paginate(10);
        return view('barn.storekeep.provider.index',compact('providers'));
    }

   
    public function create()
    {
        return view('barn.storekeep.provider.create');
    }

   
    public function store(Request $request)
    {
        $item_create=ProviderModel::create($request->all());

        return redirect()->route('storekeeper_role.provider.index');
    }

   
    public function show($id)
    {
        
    }

   
    public function edit($id)
    {
        $provider=ProviderModel::find($id);
        return view('barn.storekeep.provider.edit',compact('provider'));
    }

 
    public function update(Request $request,ProviderModel $provider)
    {
        $input = $request->all();
        $provider->fill($input)->save();

        return redirect()->route('storekeeper_role.provider.index');
    }

    public function destroy($id)
    {
        //
    }
}
