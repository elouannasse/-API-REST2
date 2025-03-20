<?php

namespace App\Http\Controllers;

use App\Models\Offre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OffreController extends Controller
{   
    
    
     
    public function index(Request $request)
    {
        $query = Offre::query();
        
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
        
            $query->where('status', 'active');
        }
        
        $offres = $query->paginate(10);
        
        return response()->json([
            'success' => true,
            'data' => $offres
        ]);
    }

    
     
     
    public function show($id)
    {
        $offre = Offre::find($id);
        
        if (!$offre) {
            return response()->json([
                'success' => false,
                'message' => 'Offre non trouvée'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $offre
        ]);
    }

    

     
    public function store(Request $request)
    {
        
        if (!Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Action non autorisée'
            ], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'company' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'contract_type' => 'required|string|max:50',
            'category' => 'required|string|max:100'
        ]);

        $offre = Offre::create([
            'title' => $request->title,
            'description' => $request->description,
            'company' => $request->company,
            'location' => $request->location,
            'contract_type' => $request->contract_type,
            'category' => $request->category,
            'status' => 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Offre créée avec succès',
            'data' => $offre
        ], 201);
    }

    
    
     
    public function update(Request $request, $id)
    {
        
        if (!Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Action non autorisée'
            ], 403);
        }

        $offre = Offre::find($id);
        
        if (!$offre) {
            return response()->json([
                'success' => false,
                'message' => 'Offre non trouvée'
            ], 404);
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'company' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'contract_type' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:100',
            'status' => 'nullable|in:active,closed'
        ]);

        $dataToUpdate = [];
        
        if ($request->has('title')) {
            $dataToUpdate['title'] = $request->title;
        }
        
        if ($request->has('description')) {
            $dataToUpdate['description'] = $request->description;
        }
        
        if ($request->has('company')) {
            $dataToUpdate['company'] = $request->company;
        }
        
        if ($request->has('location')) {
            $dataToUpdate['location'] = $request->location;
        }
        
        if ($request->has('contract_type')) {
            $dataToUpdate['contract_type'] = $request->contract_type;
        }
        
        if ($request->has('category')) {
            $dataToUpdate['category'] = $request->category;
        }
        
        if ($request->has('status')) {
            $dataToUpdate['status'] = $request->status;
        }

        $offre->update($dataToUpdate);

        return response()->json([
            'success' => true,
            'message' => 'Offre mise à jour avec succès',
            'data' => $offre
        ]);
    }

    

     
    public function destroy($id)
    {
        
        if (!Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Action non autorisée'
            ], 403);
        }

        $offre = Offre::find($id);
        
        if (!$offre) {
            return response()->json([
                'success' => false,
                'message' => 'Offre non trouvée'
            ], 404);
        }

        $offre->delete();

        return response()->json([
            'success' => true,
            'message' => 'Offre supprimée avec succès'
        ]);
    }
}