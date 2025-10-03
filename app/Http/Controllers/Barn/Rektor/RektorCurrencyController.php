<?php

namespace App\Http\Controllers\Barn\Rektor;

use Illuminate\Http\Request;
use App\Models\CurrencyModel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Barn\Rektor\RektorCurrencyRequest;

class RektorCurrencyController extends Controller
{
  
    public function index()
    {
        $currencys=CurrencyModel::paginate(10);
        return view('barn.rektor.currency.index',compact('currencys'));

    }


    public function create()
    {
       return view('barn.rektor.currency.create'); 
    }

  
    public function store(RektorCurrencyRequest $request)
    {
        // $data=$request->all();
        $currency=CurrencyModel::create($request->all());
        session()->flash('success',"Valyuta yaratildi!");
        return to_route('rektor_role.rektor_currency.index');
    }


    public function show($id)
    {
        //
    }

  
    public function edit($id)
    {
        $currency=CurrencyModel::find($id);
        return view('barn.rektor.currency.edit',compact('currency'));
    }


    public function update(RektorCurrencyRequest $request, $id)
    {
       
        $currency=CurrencyModel::find($id)->update($request->all());
        session()->flash('success',"Valyuta o'zgartirildi!");
        return to_route('rektor_role.rektor_currency.index');
    }

 
    public function destroy($id)
    {
        //
    }
}
