<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ModelController extends Controller
{
    public function index()
    {
        $marques = DB::table('marque')->get();
        return Inertia::render('Admin/Models/Index', [
            'marques' => $marques
        ]);
    }

    public function getData(Request $request)
    {
        $search = $request->input('search');

        $models = DB::table('model')
            ->join('marque', 'model.marque_id', '=', 'marque.id')
            ->select('model.*', 'marque.nom as marque_nom')
            ->when($search, function ($query, $search) {
                $query->where('model.nom', 'like', "%{$search}%")
                    ->orWhere('marque.nom', 'like', "%{$search}%");
            })
            ->paginate(15);

        // Formater les données pour inclure la marque complète
        foreach ($models as $model) {
            $model->marque = (object) ['id' => $model->marque_id, 'nom' => $model->marque_nom];
        }

        return response()->json([
            'models' => $models,
        ]);
    }

    public function create()
    {
        $marques = DB::table('marque')->get();
        return Inertia::render('Admin/Models/Create', [
            'marques' => $marques
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'marque_id' => 'required|exists:marque,id',
            'nom' => 'required|string|max:255|unique:model',
        ]);

        DB::table('model')->insert([
            'marque_id' => $validated['marque_id'],
            'nom' => $validated['nom'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Modèle créé avec succès');
    }

    public function edit($id)
    {
        $model = DB::table('model')->where('id', $id)->first();

        if (!$model) {
            abort(404);
        }

        $marques = DB::table('marque')->get();

        return Inertia::render('Admin/Models/Edit', [
            'model' => $model,
            'marques' => $marques
        ]);
    }

    public function update(Request $request, $id)
    {
        $model = DB::table('model')->where('id', $id)->first();

        if (!$model) {
            abort(404);
        }

        $validated = $request->validate([
            'marque_id' => 'required|exists:marque,id',
            'nom' => 'required|string|max:255|unique:model,nom,' . $id,
        ]);

        DB::table('model')
            ->where('id', $id)
            ->update([
                'marque_id' => $validated['marque_id'],
                'nom' => $validated['nom'],
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Modèle modifié avec succès');
    }

    public function destroy($id)
    {
        $model = DB::table('model')->where('id', $id)->first();

        if (!$model) {
            abort(404);
        }

        DB::table('model')->where('id', $id)->delete();

        return redirect()->back()->with('success', 'Modèle supprimé avec succès');
    }
}
