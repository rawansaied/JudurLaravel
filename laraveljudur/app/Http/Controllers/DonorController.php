<?php
namespace App\Http\Controllers;

use App\Models\Donor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DonorController extends Controller
{
   
    public function dashboard()
    {
       
        $user = Auth::user();
    
      
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
    
        
        $donor = DB::table('donors')->where('user_id', $user->id)->first();
    
        if (!$donor) {
            return response()->json(['error' => 'Donor not found'], 404);
        }
    
       
        $totalLandDonations = DB::table('lands')->where('donor_id', $donor->id)->count();
    

        $totalItemDonations = DB::table('item_donations')->where('donor_id', $donor->id)->count();
    
       
        $totalFinancialDonations = DB::table('financials')
            ->where('donor_id', $donor->id)
            ->sum('amount');
    
    
        $currency = DB::table('financials')
            ->where('donor_id', $donor->id)
            ->value('currency') ?? 'N/A';
    
        return response()->json([
            'donor_id' => $donor->id,
            'total_land_donations' => $totalLandDonations,
            'total_item_donations' => $totalItemDonations,
            'total_financial_donations' => [
                'amount' => $totalFinancialDonations,
                'currency' => $currency
            ]
        ], 200);
    }
    
    
    
    public function viewDetails($type)
    {
      
        $user = Auth::user();
        
       
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
    
       
        $donor = Donor::where('user_id', $user->id)->with(['landDonations', 'itemDonations', 'financialDonations'])->first();
    
      
        if (!$donor) {
            return response()->json(['error' => 'User is not a donor'], 404);
        }
    
       
        \Log::info('Donation type received: ' . $type);
    
     
        $validTypes = ['land', 'item', 'financial'];
    
        if (!in_array(strtolower($type), $validTypes)) {
            return response()->json(['error' => 'Invalid donation type'], 400);
        }
    
       
        switch (strtolower($type)) {
            case 'land':
                $donations = $donor->landDonations;
                break;
            case 'item':
                $donations = $donor->itemDonations;
                break;
            case 'financial':
                $donations = $donor->financialDonations;
                break;
        }
    
       
        if ($donations->isEmpty()) {
            return response()->json(['message' => 'No donations found for this type'], 404);
        }
    
        return response()->json($donations, 200);
    }
    
}
