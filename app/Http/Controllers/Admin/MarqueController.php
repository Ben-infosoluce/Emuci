<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MarqueController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Marques/Index');
    }

    public function getData(Request $request)
    {
        $search = $request->input('search');

        $marques = DB::table('marque')
            ->when($search, function ($query, $search) {
                $query->where('nom', 'like', "%{$search}%");
            })
            ->paginate(15);

        // Ajouter le nombre de modèles pour chaque marque
        foreach ($marques as $marque) {
            $marque->models_count = DB::table('model')
                ->where('marque_id', $marque->id)
                ->count();
        }

        return response()->json([
            'marques' => $marques,
        ]);
    }

    public function getModels(Request $request, $id)
    {
        $search = $request->input('search');

        $models = DB::table('model')
            ->where('marque_id', $id)
            ->when($search, function ($query, $search) {
                $query->where('nom', 'like', "%{$search}%");
            })
            ->get();

        foreach ($models as $model) {
            $model->marque = DB::table('marque')->where('id', $model->marque_id)->first();
        }

        return response()->json([
            'models' => $models,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Marques/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:marque',
        ]);

        DB::table('marque')->insert([
            'nom' => $validated['nom'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Marque créée avec succès');
    }

    public function edit($id)
    {
        $marque = DB::table('marque')->where('id', $id)->first();

        if (!$marque) {
            abort(404);
        }

        return Inertia::render('Admin/Marques/Edit', [
            'marque' => $marque
        ]);
    }

    public function update(Request $request, $id)
    {
        $marque = DB::table('marque')->where('id', $id)->first();

        if (!$marque) {
            abort(404);
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:marque,nom,' . $id,
        ]);

        DB::table('marque')
            ->where('id', $id)
            ->update([
                'nom' => $validated['nom'],
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Marque modifiée avec succès');
    }

    public function destroy($id)
    {
        $marque = DB::table('marque')->where('id', $id)->first();

        if (!$marque) {
            abort(404);
        }

        DB::table('marque')->where('id', $id)->delete();

        return redirect()->back()->with('success', 'Marque supprimée avec succès');
    }
}
