<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApostilStatikModel;
use App\Models\ConsulModel;
use App\Models\ConsulationTypeModel;
use App\Models\DirectionTypeModel;
use App\Models\DocumentTypeModel;
use App\Models\PackageTemplate;
use App\Models\ServiceAddonModel;
use App\Models\ServicesModel;
use App\Support\PackageTemplateSupport;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
   
    public function index()
    {
        return view('admin.document.index');
    }

    
    public function create()
    {
        $documentTypes = DocumentTypeModel::all();
        $directions = DirectionTypeModel::all();
        $consulateTypes = ConsulationTypeModel::all();
        $services = ServicesModel::all();
        $addons = ServiceAddonModel::all();
        $consuls = ConsulModel::all();
        $consul_price = 1000;
        $apostilStatics = ApostilStatikModel::all();
        $packageTemplates = PackageTemplateSupport::buildSelectionPayloads(
            PackageTemplate::query()
                ->active()
                ->whereHas('items')
                ->ordered()
                ->with([
                    'items.documentType:id,name',
                    'items.service:id,name,price,deadline',
                    'items.directionType:id,name',
                    'items.apostilGroup1:id,name,price,days',
                    'items.apostilGroup2:id,name,price,days',
                    'items.consul:id,name,amount,day',
                    'items.consulateType:id,name,amount,day',
                ])
                ->get()
        );
        $apiBase = url('superadmin/api');

        return view('admin_filial.admin_filial_document.refactor.create', compact(
            'services',
            'addons',
            'documentTypes',
            'directions',
            'consulateTypes',
            'consul_price',
            'apostilStatics',
            'consuls',
            'packageTemplates',
            'apiBase'
        ));
    }

    public function store(Request $request)
    {
        //
    }

  
    public function show($id)
    {
        //
    }

   
    public function edit($id)
    {
        //
    }

   
    public function update(Request $request, $id)
    {
        //
    }

    
    public function destroy($id)
    {
        //
    }

    public function statistika()
    {
        return view('admin.document.statistika');
    }
}
