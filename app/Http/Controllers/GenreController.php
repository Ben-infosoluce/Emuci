<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GenreController extends Controller
{
    /**
     * Liste des genres pour l'administration
     */
    public function index()
    {
        return Inertia::render('Admin/Genres/index', [
            'genres' => Genre::orderBy('id', 'desc')->get()
        ]);
    }

    /**
     * Enregistrer un nouveau genre
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:genre',
            'nb_plaque' => 'required|integer|min:1|max:2',
        ]);

        Genre::create($validated);

        return back()->with('success', 'Genre ajouté avec succès');
    }

    /**
     * Mettre à jour un genre existant
     */
    public function update(Request $request, Genre $genre)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:genre,nom,' . $genre->id,
            'nb_plaque' => 'required|integer|min:1|max:2',
        ]);

        $genre->update($validated);

        return back()->with('success', 'Genre modifié avec succès');
    }

    /**
     * Supprimer un genre
     */
    public function destroy(Genre $genre)
    {
        $genre->delete();

        return back()->with('success', 'Genre supprimé avec succès');
    }
}
