<?php

namespace App\Http\Controllers;

use App\Models\Donor;
use App\Models\Examiner;
use App\Models\Volunteer;
use Illuminate\Http\Request;

class AdminController extends Controller
{

    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>Users section
    public function getDonors()
    {
        $donors = Donor::with('user')->get();

        return response()->json($donors);
    }

    public function getVolunteers()
    {
        $volunteers = Volunteer::with('user', 'volunteerStatus')->get();

        return response()->json($volunteers);
    }

    public function donorDetails($donorId)
    {
        $donor = Donor::with(['user', 'lastDonation', 'donations'])->findOrFail($donorId);
    
        $totalDonations = $donor->donations->sum('amount'); 
    
        $responseData = [
            'donor' => [
                'id' => $donor->id,
                'name' => $donor->user->name,
                'email' => $donor->user->email,
                'total_donations' => $totalDonations,
                'last_donation' => $donor->lastDonation ? [
                    'amount' => $donor->lastDonation->amount,
                    'created_at' => $donor->lastDonation->created_at,
                ] : null,
                'donations' => $donor->donations, 
            ]
        ];
    
        return response()->json($responseData, 200);
    }
    

    public function volunteerDetails($id)
    {
        $volunteer = Volunteer::with(['user', 'volunteerStatus'])->findOrFail($id);

        return response()->json($volunteer);
    }

    //>>>>>>>>>>>>>>>>>>>>>>>>>> requests section

    public function getPendingVolunteers()
    {
        $volunteers = Volunteer::with('user')
            ->where('volunteer_status', 1)
            ->get();

        return response()->json($volunteers);
    }

    public function updateStatus(Request $request, $id)
{
    $volunteer = Volunteer::findOrFail($id);
    $volunteer->volunteer_status = $request->input('status');
    if ($volunteer->save()) {
        return response()->json(['success' => true]);
    } else {
        return response()->json(['success' => false], 500);
    }
}

public function getPendingExaminers()
{
    $examiners = Examiner::with('user')
        ->where('Examiner_status', 1)
        ->get();

    return response()->json($examiners);
}

public function examinerDetails($id)
{
    $examiner = Examiner::with(['user', 'examinerStatus'])->findOrFail($id);

    return response()->json($examiner);
}

public function updateExaminerStatus(Request $request, $id)
{
    $examiner = Examiner::findOrFail($id);
    $examiner->examiner_status = $request->input('status');
    if ($examiner->save()) {
        return response()->json(['success' => true]);
    } else {
        return response()->json(['success' => false], 500);
    }
}

}
