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
use App\Models\Bid;

use App\Models\ItemDonation;
use Illuminate\Http\Request;


class AuctionController extends Controller
{public function index() {
    $auctions = Auction::with('itemDonation')
                ->where('auction_status_id', 2) // 2 represents 'Ongoing'
                ->where('end_date', '>', now()) // Only fetch auctions that haven't ended yet
                ->get();
    
    return response()->json($auctions);
}

    
    

  
    public function store(Request $request)
    {
        $auction = Auction::create($request->all());
        return response()->json($auction, 201);  
    }

 
    public function show($id) {
        $auction = Auction::with('itemDonation', 'highestBidder')->findOrFail($id);
        
        // Count the number of bidders for the auction
        $numberOfBidders = Bid::where('auction_id', $id)->count();
        
        // Correctly generate the image URL
        $imageUrl = $auction->itemDonation->image 
            ? asset('storage/item_images/' . $auction->itemDonation->image) 
            : 'https://via.placeholder.com/150'; // Placeholder image URL
    
        return response()->json([
            'id' => $auction->id,
            'title' => $auction->title,
            'description' => $auction->description,
            'current_highest_bid' => $auction->current_highest_bid ?? $auction->starting_price,
            'start_date' => $auction->start_date,
            'end_date' => $auction->end_date,
            'number_of_bidders' => $numberOfBidders,
            'highest_bidder' => $auction->highestBidder->name ?? 'No bids yet',
            'imageUrl' => $imageUrl, // Ensure this is set correctly
        ]);
    }
    
    
    
    
    
    
    

    // عرض نموذج تعديل عنصر محدد (edit)

    public function edit($id)
    {
       
    }

   
    public function update(Request $request, $id)
    {
        $auction = Auction::findOrFail($id);
        $auction->update($request->all());
        return response()->json($auction);
    }

   
    public function destroy($id)
    {
        $auction = Auction::findOrFail($id);
        $auction->delete();
        return response()->json(null, 204); 
    }


    public function completeAuction($id)
    {
        $auction = Auction::findOrFail($id);

        // Check if the auction has ended
        if (now()->greaterThan($auction->end_date)) {
            $auction->update(['auction_status_id' => 3]); // Mark auction as completed
            $itemDonation = ItemDonation::find($auction->item_id);
            $itemDonation->update(['status_id' => 1]); // Mark the item as sold
            return response()->json(['message' => 'Auction completed, item marked as sold.']);
        }

        return response()->json(['message' => 'Auction is still ongoing.'], 400);
    }

}
