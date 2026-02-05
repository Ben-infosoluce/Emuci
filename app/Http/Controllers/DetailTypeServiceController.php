<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\DetailTypeService;
use App\Models\Entite;
use App\Models\Service;
use Illuminate\Http\Request;

class DetailTypeServiceController extends Controller
{
    // Afficher tous les enregistrements
    public function index()
    {
        $detailTypeServices = DetailTypeService::with([
            'r_detailsTypeService_typeService',
            'r_detailsTypeService_service',
            'r_detailsTypeService_site',
            'r_details_type_service_entite',
        ])->get();

        return inertia('Site/ElementFacturation', [
            'elementFacturation' => $detailTypeServices,
            'sites' => Site::all(),
            'services' => Service::all(),
            'entites' => Entite::all(),
        ]);
    }

    // Créer un enregistrement
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_type_services' => 'required|exists:type_services,id',
            'id_service' => 'required|exists:services,id',
            'id_site' => 'required|exists:sites,id',
            'id_entite' => 'required|exists:entites,id',
        ]);

        $item = DetailTypeService::create($validated);

        return response()->json($item, 201);
    }

    // Afficher un enregistrement
    public function show($id)
    {
        $item = DetailTypeService::with([
            'typeService:id,nom_type_service',
            'service:id,nom_service',
            'site:id,nom_site',
            'entite:id,nom',
        ])->findOrFail($id);

        return response()->json($item);
    }

    // Mettre à jour un enregistrement
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'id_type_services' => 'sometimes|exists:type_services,id',
            'id_service' => 'sometimes|exists:services,id',
            'id_site' => 'sometimes|exists:sites,id',
            'id_entite' => 'sometimes|exists:entites,id',
        ]);

        $item = DetailTypeService::findOrFail($id);
        $item->update($validated);

        return response()->json($item);
    }

    // Supprimer un enregistrement
    public function destroy($id)
    {
        $item = DetailTypeService::findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Supprimé avec succès.']);
    }
}
