<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserCV;
use App\Jobs\AnalyzeCVJob;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Afficher le profil de l'utilisateur connecté
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        $user = Auth::user();
        
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Mettre à jour le profil de l'utilisateur
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'phone_number' => 'nullable|string|max:20',
            'skills' => 'nullable|array'
        ]);

        $dataToUpdate = [];
        
        if ($request->has('name')) {
            $dataToUpdate['name'] = $request->name;
        }
        
        if ($request->has('email')) {
            $dataToUpdate['email'] = $request->email;
        }
        
        if ($request->has('phone_number')) {
            $dataToUpdate['phone_number'] = $request->phone_number;
        }
        
        if ($request->has('skills')) {
            $dataToUpdate['skills'] = $request->skills;
        }

        $user->update($dataToUpdate);

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour avec succès',
            'data' => $user
        ]);
    }

    /**
     * Uploader un nouveau CV
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadCV(Request $request)
    {
        $request->validate([
            'cv' => 'required|file|mimes:pdf,doc,docx|max:5120' // 5MB max
        ]);

        $file = $request->file('cv');
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();
        $size = $file->getSize();
        
        $path = $file->store('cvs/' . Auth::id(), 'public');
        
        $cv = UserCV::create([
            'user_id' => Auth::id(),
            'path' => $path,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'size' => $size
        ]);

        // Dispatch du job pour analyser le CV (si nécessaire)
        // AnalyzeCVJob::dispatch($cv);
        
        return response()->json([
            'success' => true,
            'message' => 'CV téléchargé avec succès',
            'data' => $cv
        ]);
    }

    /**
     * Obtenir tous les CVs de l'utilisateur
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCVs()
    {
        $cvs = UserCV::where('user_id', Auth::id())->get();
        
        return response()->json([
            'success' => true,
            'data' => $cvs
        ]);
    }

    /**
     * Supprimer un CV de l'utilisateur
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCV($id)
    {
        $cv = UserCV::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();
            
        if (!$cv) {
            return response()->json([
                'success' => false,
                'message' => 'CV non trouvé ou non autorisé'
            ], 404);
        }
        
        Storage::disk('public')->delete($cv->path);
        
        $cv->delete();

        return response()->json([
            'success' => true,
            'message' => 'CV supprimé avec succès'
        ]);
    }
}