<?php

namespace App\Http\Controllers;

use App\Models\Stagiaire;
use App\Models\Stg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Pail\Files;

class StagiaireController extends Controller
{
    public function show(){
        $allStagiaires = Stagiaire::with(['diplomes','competences','experiences'])->get();
        return $allStagiaires->toJson();
    }

    public function showcompts(Request $request){
        $compts=Stagiaire::where('CIN',$request['id'])->first()->competences;
        return response()->json($compts,200);
    }

    public function store(Request $request){
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|regex:/^[a-zA-Z0-9._%+-]+@ofppt-edu\.ma$/',
            'adr' => 'required',
            'ville' => 'required',
            'phone' => 'required',
            'password' => 'required',
            'cin' => 'required',
        ], [
            'email.required' => 'Veuillez entrer une adresse email.',
            'email.email' => 'Veuillez fournir une adresse email valide.',
            'email.regex' => 'L\'adresse email doit se terminer par @ofppt-edu.ma.',
            'password.required' => 'Veuillez entrer un mot de passe.',
        ]);

        // Create the Stagiaire record using mass assignment
        $newstg = Stagiaire::create([
            'StagiaireName' => $validated['name'],
            'OFPPTMail' => $validated['email'],
            'Phone' => $validated['phone'],
            'Ville' => $validated['ville'],
            'Adresse' => $validated['adr'],
            'Password' => bcrypt($validated['password']),
            'CIN' => $validated['cin']
        ]);
              return $newstg ;

    }
    public function login(Request $request)
    {
        // Validation des données avec messages d'erreur personnalisés
        $data = $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        // Recherche de l'utilisateur dans la base de données
        $user = Stagiaire::where('OFPPTMail', $data['email'])->first();

        if (!$user) {
            return response()->json(['msg' => 'Adresse email introuvable.'], 401);
        }

        // Vérification du mot de passe
        if(Hash::check($data['password'],$user['Password'])){
            $token=$user->createToken('main')->plainTextToken;
            return response()->json([
                'stg' => $user,
                'token' => $token,
            ]);
        } else {
            return response()->json([
                'msg' => 'Mot de passe incorrect.',
            ], 401);
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Stagiaire $stagiaire)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $stagiaire = Auth::user();
        if (!$stagiaire) {
            return response()->json(['msg' => 'Non connecté'], 401);
        }

        $validated = $request->validate([
            'Phone' => 'nullable|string|min:1|max:15',
            'Adresse' => 'nullable|string|max:40',
            'Profile' => 'nullable|string',
            'Photo' => 'nullable', // Validate Photo as an image
        ]);

        // Handle file upload for Photo if provided
        if ($request->hasFile('Photo')) {
            $photoPath = $request->file('Photo')->store('profile_photos', 'public'); // Store in 'public/profile_photos'
            $validated['Photo'] = $photoPath; // Update validated data with the file path
        }

        // Update the user's information
        $stagiaire->update($validated);

        return response()->json([
            'stg' => $stagiaire,
            'msg' => 'Profil est modifié',
        ], 200);
    }

    public function updatecvletter(Request $request){
        $stagiaire = Auth::user();

        $validated = $request->validate([
            'MotivationLetter' => 'required',
            'CV' => 'required',
        ]);
        // Handle file upload for Photo if provided
        // Update the user's information
        $stagiaire->update($validated);

        return response()->json([
            'stg' => $stagiaire,
            'msg' => 'pieces est modifié',
        ], 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Stagiaire $stagiaire)
    {
        //
    }

    public function showLangues(Request $request)
    {
        $langues=Stagiaire::find($request['id'])->langues;
        return response()->json($langues,200);
    }

    public function showDiplomes(Request $request)
    {
        $diplomes=Stagiaire::find($request['id'])->diplomes;
        return response()->json($diplomes,200);
    }

    public function showExperience(Request $request)
    {
        $experience=Stagiaire::find($request['id'])->experiences;
        return response()->json($experience,200);
    }

    public function candidaturesWithOffres(Request $request)
    {
        $stagiaire = Stagiaire::with(['candidatures.offre'])->where('CIN', $request['cin'])->first();

        if (!$stagiaire) {
            return response()->json(['error' => 'Stagiaire non trouvé'], 404);
        }

        return response()->json($stagiaire, 200);
    }

    public function addCompetences(Request $request){
        $stagiaire=Auth::user();
        if(!$stagiaire){
            return response()->json(['msg'=>'non connecté']);
        }
        $validated=$request->validate([
            'competence'=>'required'
        ]);

        $currentCompetences = $validated['competence'];

        $stagiaire->competences = $currentCompetences;
        $stagiaire->save();

        return response()->json(['msg'=>'competence est ajouté']);
    }



}
