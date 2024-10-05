<?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;

// class AuctionController extends Controller
// {
//     /**
//      * Display a listing of the resource.
//      */
//     public function index()
//     {
//         //
//     }

//     /**
//      * Show the form for creating a new resource.
//      */
//     public function create()
//     {
//         //
//     }

//     /**
//      * Store a newly created resource in storage.
//      */
//     public function store(Request $request)
//     {
//         //
//     }

//     /**
//      * Display the specified resource.
//      */
//     public function show(string $id)
//     {
//         //
//     }

//     /**
//      * Show the form for editing the specified resource.
//      */
//     public function edit(string $id)
//     {
//         //
//     }

//     /**
//      * Update the specified resource in storage.
//      */
//     public function update(Request $request, string $id)
//     {
//         //
//     }

//     /**
//      * Remove the specified resource from storage.
//      */
//     public function destroy(string $id)
//     {
//         //
//     }
// }


namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\ItemDonation;
use Illuminate\Http\Request;


class AuctionController extends Controller
{
    public function index()
    {
        // Get all item donations with their associated data
        $itemDonations = ItemDonation::with('donor')->get();
    
        // Transform the data to include only the necessary fields
        $auctions = $itemDonations->map(function ($item) {
            return [
                'id' => $item->id, // Assuming there's an ID in item donations
                'imageUrl' => asset('storage/' . $item->image), // Use asset() to generate the correct URL
                'title' => $item->item_name, // Assuming item_name corresponds to title
                'currentPrice' => $item->value, // Assuming value corresponds to current price
                'description' => $item->condition, // Adjust as necessary
            ];
        });
    
        return response()->json($auctions);  
    }
    

    
    // عرض نموذج إنشاء عنصر جديد (create)
    public function create()
    {
        // يمكن استخدام هذا لعرض صفحة إنشاء المزاد الجديد (للواجهات فقط)
    }

    // تخزين عنصر جديد (store)
    public function store(Request $request)
    {
        $auction = Auction::create($request->all());
        return response()->json($auction, 201);  // 201 Created status
    }

    // عرض عنصر محدد (show)
    // public function show($id)
    // {
    //     $auction = Auction::findOrFail($id);
    //     return response()->json($auction);
    // }
    public function show($id)
{
    // Find the item donation by ID
    $itemDonation = ItemDonation::with('donor')->find($id);
    
    if (!$itemDonation) {
        return response()->json(['message' => 'Item not found'], 404);
    }

    // Retrieve auction data using the correct column name
    $auction = Auction::where('item_id', $id)->first();
    
    // Transform the data for the response
    $auctionDetails = [
        'id' => $itemDonation->id,
        'imageUrl' => asset('storage/' . $itemDonation->image),
        'title' => $itemDonation->item_name,
        'description' => $itemDonation->condition,
        'currentPrice' => $itemDonation->value,
        'start_date' => $auction ? $auction->start_date : null,
        'end_date' => $auction ? $auction->end_date : null,
        'number_of_bidders' => $auction ? $auction->number_of_bidders : null,
    ];

    // Return the transformed data
    return response()->json($auctionDetails);
}











    

    // عرض نموذج تعديل عنصر محدد (edit)
    public function edit($id)
    {
        // يمكن استخدامه لعرض نموذج التعديل (للواجهات فقط)
    }

    // تحديث عنصر محدد (update)
    public function update(Request $request, $id)
    {
        $auction = Auction::findOrFail($id);
        $auction->update($request->all());
        return response()->json($auction);
    }

    // حذف عنصر محدد (destroy)
    public function destroy($id)
    {
        $auction = Auction::findOrFail($id);
        $auction->delete();
        return response()->json(null, 204);  // 204 No Content status
    }
}
