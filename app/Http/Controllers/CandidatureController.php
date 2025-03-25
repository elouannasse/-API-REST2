<?php

namespace App\Http\Controllers;

use App\Models\Offre;
use App\Models\UserCV;
use App\Models\Candidature;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\Controller;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendConfirmationEmailJob;
use App\Jobs\ProcessBulkCandidaturesJob;

class CandidatureController extends Controller
{
    public function index()
    {
        $user = Auth::guard('api')->user();

        if ($user->is_admin) {
            $candidatures = Candidature::with(['user', 'offre'])->paginate(10);
        } else {
            $candidatures = Candidature::with('offre')
                ->where('user_id', $user->id)
                ->paginate(10);
        }

        return response()->json([
            'success' => true,
            'data' => $candidatures
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'offre_id' => 'required|exists:offres,id',
            'cv_id' => 'required|exists:user_cvs,id'
        ]);

        $offre = Offre::find($request->offre_id);
        if (!$offre || $offre->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Cette offre n\'est plus disponible.'
            ], 400);
        }

        $existingCandidature = Candidature::where('user_id', Auth::guard('api')->id())
            ->where('offre_id', $request->offre_id)
            ->first();

        if ($existingCandidature) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà postulé à cette offre.'
            ], 400);
        }

        $cv = UserCV::where('id', $request->cv_id)
            ->where('user_id', Auth::guard('api')->id())
            ->first();

        if (!$cv) {
            return response()->json([
                'success' => false,
                'message' => 'CV non trouvé ou non autorisé.'
            ], 404);
        }

        $candidature = Candidature::create([
            'user_id' => Auth::guard('api')->id(),
            'offre_id' => $request->offre_id,
            'cv_path' => $cv->path,
            'status' => 'pending'
        ]);

        SendConfirmationEmailJob::dispatch($candidature);

        return response()->json([
            'success' => true,
            'message' => 'Votre candidature a été enregistrée avec succès.',
            'data' => $candidature
        ], 201);
    }

    public function storeBulk(Request $request)
    {
        $request->validate([
            'offre_ids' => 'required|array',
            'offre_ids.*' => 'required|exists:offres,id',
            'cv_id' => 'required|exists:user_cvs,id'
        ]);

        $cv = UserCV::where('id', $request->cv_id)
            ->where('user_id', Auth::guard('api')->id())
            ->first();

        if (!$cv) {
            return response()->json([
                'success' => false,
                'message' => 'CV non trouvé ou non autorisé.'
            ], 404);
        }

        ProcessBulkCandidaturesJob::dispatch(
            Auth::guard('api')->id(),
            $request->offre_ids,
            $cv->path
        );

        return response()->json([
            'success' => true,
            'message' => 'Vos candidatures sont en cours de traitement.'
        ]);
    }

    public function show($id)
    {
        $user = Auth::guard('api')->user();

        if ($user->is_admin) {
            $candidature = Candidature::with(['user', 'offre'])->find($id);
        } else {
            $candidature = Candidature::with('offre')
                ->where('id', $id)
                ->where('user_id', $user->id)
                ->first();
        }

        if (!$candidature) {
            return response()->json([
                'success' => false,
                'message' => 'Candidature non trouvée.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $candidature
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        if (!Auth::guard('api')->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Action non autorisée.'
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,reviewed,accepted,rejected'
        ]);

        $candidature = Candidature::find($id);

        if (!$candidature) {
            return response()->json([
                'success' => false,
                'message' => 'Candidature non trouvée.'
            ], 404);
        }

        $candidature->status = $request->status;
        $candidature->save();

        return response()->json([
            'success' => true,
            'message' => 'Statut de la candidature mis à jour avec succès.',
            'data' => $candidature
        ]);
    }

    public function cancel($id)
    {
        $candidature = Candidature::where('id', $id)
            ->where('user_id', Auth::guard('api')->id())
            ->first();

        if (!$candidature) {
            return response()->json([
                'success' => false,
                'message' => 'Candidature non trouvée.'
            ], 404);
        }

        if ($candidature->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cette candidature ne peut plus être annulée.'
            ], 400);
        }

        $candidature->status = 'cancelled';
        $candidature->save();

        return response()->json([
            'success' => true,
            'message' => 'Candidature annulée avec succès.'
        ]);
    }
}