<?php


namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Bid;

use App\Models\ItemDonation;
use Illuminate\Http\Request;


class AuctionController extends Controller
{public function index() {
    // Fetch ongoing auctions with related item donations
    $auctions = Auction::with('itemDonation')->where('auction_status_id', 2) // Ongoing
                        ->where('end_date', '>', now()) // Not yet ended
                        ->get();

    // Format the response
    $formattedAuctions = $auctions->map(function ($auction) {
        // Construct image URL
        $imageUrl = $auction->itemDonation->image 
            ? asset('storage/' . $auction->itemDonation->image) 
            : 'https://via.placeholder.com/150'; // Placeholder image

        return [
            'id' => $auction->id,
            'title' => $auction->title,
            'description' => $auction->description,
            'starting_price' => $auction->starting_price,
            'current_highest_bid' => $auction->current_highest_bid ?? $auction->starting_price,
            'start_date' => $auction->start_date,
            'end_date' => $auction->end_date,
            'item_name' => $auction->itemDonation->item_name,
            'item_value' => $auction->itemDonation->value,
            'item_condition' => $auction->itemDonation->condition,
            'image_url' => $imageUrl,
        ];
    });

    return response()->json($formattedAuctions);
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
        
        $imageUrl = $auction->itemDonation->image 
        ? asset('storage/' . $auction->itemDonation->image) 
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
    public function getCompletedAuctions()
{
    // Get the currently authenticated user ID
    $userId = auth()->id();

    // Get completed auctions where the auction has ended
    $completedAuctions = Auction::where('end_date', '<', now())
        ->with(['bids', 'itemDonation']) // Include itemDonation relation
        ->get();

    $auctionWinners = [];

    foreach ($completedAuctions as $auction) {
        // Get the highest bid for the auction
        $highestBid = $auction->bids()->orderBy('bid_amount', 'desc')->first();

        // If there is a highest bid and the user is the highest bidder
        if ($highestBid && $highestBid->user_id == $userId) {
            // Check if the image exists and create the correct URL
            $imageUrl = $auction->itemDonation->image
                ? asset('storage/' . $auction->itemDonation->image)
                : 'https://via.placeholder.com/150'; // Fallback placeholder image

            // Append the auction details to the response if the user is the highest bidder
            $auctionWinners[] = [
                'auction_id' => $auction->id,
                'auction_title' => $auction->title,
                'auction_image' => $imageUrl,
                'highest_bidder_id' => $highestBid->user_id,
                'highest_bidder_name' => $highestBid->user->name,
                'bid_amount' => $highestBid->bid_amount, // Store the highest bid amount
            ];
        }
    }

    return response()->json($auctionWinners);
}

public function getHighestBid($id)
    {
        // Find the auction by ID
        $auction = Auction::find($id);
        
        if (!$auction) {
            return response()->json(['error' => 'Auction not found'], 404);
        }

        // Get the highest bid for the auction
        $highestBid = Bid::where('auction_id', $id)
            ->orderBy('amount', 'desc')
            ->first();

        if ($highestBid) {
            return response()->json(['amount' => $highestBid->amount]);
        } else {
            return response()->json(['amount' => 0]); // No bids yet
        }
    }

    

}
