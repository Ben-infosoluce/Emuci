<?php

namespace App\Http\Controllers;

use App\Models\DetailTypeService;
use App\Models\Site;
use App\Models\Service;
use App\Models\Entite;
use App\Models\TypeService;
use Illuminate\Http\Request;

class ElementFacturationController extends Controller
{
    public function showElementFacturationList()
    {
        $detailTypeServices = DetailTypeService::with([
            'r_detailsTypeService_typeService',
            'r_detailsTypeService_service',
            'r_detailsTypeService_site',
            'r_details_type_service_entite',
        ])->get();

        return inertia('Admin/ElementFacturation', [
            'elementFacturation' => $detailTypeServices,
            'sites' => Site::all(),
            'services' => Service::all(),
            'entites' => Entite::all(),
            'type_services' => TypeService::all(),
        ]);
    }

    public function createElementFacturation(Request $request)
    {
        $validated = $request->validate([
            'element_facturation' => 'required|string',
            'id_type_services' => 'required|exists:type_services,id',
            'id_site' => 'required|exists:sites,id',
            'id_service' => 'required|exists:services,id',
            'id_entite' => 'required|exists:entites,id',
            'montant_2_plaques' => 'required|numeric|min:0',
            'montant_1_plaque' => 'required|numeric|min:0',
        ]);

        $item = DetailTypeService::create(array_merge($validated, [
            'statut' => 1,
        ]));

        return response()->json($item, 201);
    }

    public function showElementFacturationEdit($id)
    {
        $item = DetailTypeService::findOrFail($id);
        return response()->json($item);
    }

    public function updateElementFacturation(Request $request, $id)
    {
        $validated = $request->validate([
            'element_facturation' => 'required|string',
            'id_type_services' => 'required|exists:type_services,id',
            'id_site' => 'required|exists:sites,id',
            'id_service' => 'required|exists:services,id',
            'id_entite' => 'required|exists:entites,id',
            'montant_2_plaques' => 'required|numeric|min:0',
            'montant_1_plaque' => 'required|numeric|min:0',
        ]);

        $item = DetailTypeService::findOrFail($id);
        $item->update($validated);

        return response()->json($item);
    }

    public function deleteElementFacturation($id)
    {
        $item = DetailTypeService::findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Supprimé avec succès.']);
    }

    public function updateStatutElementFacturation(Request $request, $id)
    {
        $validated = $request->validate([
            'statut' => 'required|integer|in:1,2',
        ]);

        $item = DetailTypeService::findOrFail($id);
        $item->statut = $validated['statut'];
        $item->save();

        return response()->json([
            'message' => 'Statut mis à jour avec succès.',
            'item' => $item
        ]);
    }
}
