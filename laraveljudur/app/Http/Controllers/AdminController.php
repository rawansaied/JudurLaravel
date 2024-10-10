<?php

namespace App\Http\Controllers;
use Carbon\Carbon;

use App\Models\Auction;
use App\Models\AuctionStatus;
use App\Models\Donor;
use App\Models\Event;
use App\Models\Examiner;
use App\Models\Financial;
use App\Models\Inventory;
use App\Models\ItemDonation;
use App\Models\Land;
use App\Models\Treasury;
use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Notifications\EventNotification;
use App\Events\EventCreated;
class AdminController extends Controller
{
    public function index()
    {
        $donations = DB::table('financials')
                        ->select(DB::raw('SUM(amount) as total_amount'), DB::raw('MONTH(created_at) as month'))
                        ->groupBy(DB::raw('MONTH(created_at)'))
                        ->get();
    
        return response()->json($donations);
    }
    public function getPieChartData()
    {
        $donorsCount = Donor::count(); 
        $volunteersCount = Volunteer::where('volunteer_status', 2)->count(); 
        $examinersCount = Examiner::where('examiner_status', 2)->count(); 

        return response()->json([
            'donors' => $donorsCount,
            'volunteers' => $volunteersCount,
            'examiners' => $examinersCount, 
        ]);
    }

    public function getDashboardData()
    {
        $lastMonthDonations = DB::table('financials')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->sum('amount');

        $totalDonations = DB::table('financials')->sum('amount');

        $totalHubs = DB::table('lands')
            ->where('status_id', 2)
            ->count();

        $pendingUserRequests = DB::table('examiners')
            ->where('examiner_status', 2)
            ->count() + DB::table('volunteers')
            ->where('volunteer_status', 2)
            ->count();

        $totalDonors = DB::table('donors')->count();

        $totalVolunteers = DB::table('volunteers')
            ->where('volunteer_status', 2)
            ->count();

        $totalExaminers = DB::table('examiners')
            ->where('examiner_status', 2)
            ->count();

        $valuableItemChecks = DB::table('item_donations')
            ->where('status_id', 1)
            ->count();

        return response()->json([
            'last_month_donations' => $lastMonthDonations,
            'total_donations' => $totalDonations,
            'total_hubs' => $totalHubs,
            'pending_user_requests' => $pendingUserRequests,
            'total_donors' => $totalDonors,
            'total_volunteers' => $totalVolunteers,
            'total_examiners' => $totalExaminers,
            'valuable_item_checks' => $valuableItemChecks,
        ]);
    }
    

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
        $volunteer = Volunteer::where('user_id', $examiner->user_id)->first(); // Assuming examiner has a user_id

        if ($volunteer) {
            $volunteer->examiner = 1; 
            $volunteer->save(); 
        }

        return response()->json(['success' => true]);
    } else {
        return response()->json(['success' => false], 500);
    }
}


public function getEvents()
{
    $events = Event::with('eventStatus')->get();

    return response()->json($events);
}

public function eventDetails($id)
{
    $event = event::with(['eventstatus'])->findOrFail($id);

    return response()->json($event);
}

public function eventForm()
{
    // Get the current date
    $currentDate = Carbon::now()->toDateString();

    // Fetch lands with status_id = 2 and availability_time in the future
    $lands = Land::where('status_id', 2)
                ->where('availability_time', '>', $currentDate)
                ->get();

    return response()->json($lands);
}
public function createEvent(Request $request)
{
    Log::info('Create Event Called', ['request' => $request->all()]);

    $validatedData = $request->validate([
        'title' => 'required|string|max:255',
        'land_id' => 'required|integer',
        'location' => 'required|string|max:255',
        'date' => 'required|date',
        'time' => 'required',
        'expected_organizer_number' => 'required|integer|min:1',
        'allocatedMoney' => 'nullable|integer|min:0',
        'allocatedItems' => 'nullable|integer|min:0',
        'event_status' => 'required|integer|in:1,2,3,4',
        'description' => 'required|string',
        'duration' => 'nullable|integer|min:0',
        'people_helped' => 'nullable|integer|min:0',
        'goods_distributed' => 'nullable|integer|min:0',
        'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    $treasury = Treasury::where('id', 1)->first();
    $old_money = $treasury->money;
    $inventory = Inventory::where('id', 1)->first();
    $old_items = $inventory->items;

    if ($request->allocatedMoney > $old_money) {
        return response()->json(['error' => 'Insufficient funds in the treasury'], 400);
    }
    if ($request->allocatedItems > $old_items) {
        return response()->json(['error' => 'Insufficient Wuantity in the Inventory'], 400);
    }

    $event = new Event();
    $event->fill($validatedData);

    if ($request->hasFile('image')) {
        $event->image = $request->file('image')->store('images', 'public');
    }

    $new_money = $old_money - $request->allocatedMoney;
    $treasury->update(['money' => $new_money]);
    $new_items = $old_items - $request->allocatedItems;
    $inventory->update(['items' => $new_items]);
    

    if ($event->save()) {
        return response()->json(['message' => 'Event created successfully', 'data' => $event], 201);
    } else {
        return response()->json(['message' => 'Failed to create event'], 500);
    }
    $users = User::all();  // You can also target specific users
    foreach ($users as $user) {
        $user->notify(new EventNotification());
    }
    broadcast(new EventCreated());
}



public function editEvent(Request $request, $id)
{
    $validatedData = $request->validate([
        'title' => 'required|string|max:255',
        'land_id' => 'required|integer|exists:lands,id',
        'location' => 'required|string|max:255',
        'date' => 'required|date',
        'time' => 'required',
        'expected_organizer_number' => 'required|integer|min:1',
        'allocatedMoney' => 'nullable|integer|min:0',
        'allocatedItems' => 'nullable|integer|min:0',
        'event_status' => 'required|integer|in:1,2,3,4',
        'description' => 'required|string',
        'duration' => 'nullable|integer|min:0',
        'people_helped' => 'nullable|integer|min:0',
        'goods_distributed' => 'nullable|min:0',
        'image' => 'nullable|string', 
    ]);

    $event = Event::findOrFail($id);
    $old_value = $event->allocatedMoney;
    $treasury = Treasury::where('id', 1)->first();
    $old_money = $treasury->money;
    $old_money += $old_value;

    $old_value_items = $event->allocatedItems;
    $inventory = Inventory::where('id', 1)->first();
    $oldItem = $inventory->items;
    $oldItem += $old_value_items;

    if ($request->allocatedMoney > $old_money) {
        return response()->json(['error' => 'Insufficient funds in the treasury'], 400);
    }
    if ($request->allocatedItems > $oldItem) {
        return response()->json(['error' => 'Insufficient quantity in the Inventory'], 400);
    }


    $new_money = $old_money - $request->allocatedMoney;
    $treasury->update(['money' => $new_money]);

    $new_items = $oldItem - $request->allocatedItems;
    $inventory->update(['items' => $new_items]);

    $event->fill($validatedData);

    if ($request->has('image') && !empty($request->image)) {
        $imageData = $request->image;

        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
            $imageType = strtolower($type[1]);
            
            if (!in_array($imageType, ['jpg', 'jpeg', 'png', 'gif'])) {
                return response()->json(['error' => 'Invalid image type'], 422);
            }

            $imageData = substr($imageData, strpos($imageData, ',') + 1);
            $imageData = base64_decode($imageData);

            if ($imageData === false) {
                return response()->json(['error' => 'Base64 decode failed'], 422);
            }

            $imageName = time() . '.' . $imageType;
            Storage::disk('public')->put('images/' . $imageName, $imageData);

            $event->image = 'images/' . $imageName;
        } else {
            return response()->json(['error' => 'Invalid image format'], 422);
        }
    }

    if ($event->save()) {
        return response()->json(['message' => 'Event updated successfully', 'data' => $event], 200);
    } else {
        return response()->json(['message' => 'Failed to update event'], 500);
    }
}



public function deleteEvent($id)
{
    $event = Event::findOrFail($id);
    $old_value = $event->allocatedMoney;
    $old_value_items = $event->allocatedItems;

    if ($event->image) {
        Storage::disk('public')->delete($event->image);
    }

    $event->delete();
    $treasury = Treasury::where('id', 1)->first();
    $old_money = $treasury->money;
    $new_money = $old_money + $old_value;
    $treasury->update(['money' => $new_money]);

    $inventory = Inventory::where('id', 1)->first();
    $old_item = $inventory->items;
    $new_item = $old_item + $old_value_items;
    $inventory->update(['items' => $new_item]);

    return response()->json(['message' => 'Event deleted successfully'], 204);
}


// Get all auctions with the related itemDonation
public function getAuctions()
{
    $auctions = Auction::with('itemDonation')->get();

    return response()->json($auctions);
}

public function auctionDetails($id)
{
    $auction = Auction::with(['itemDonation'])->findOrFail($id);

    return response()->json($auction);
}


public function createAuction(Request $request)
{
    Log::info('Create Auction Called', ['request' => $request->all()]);

    $validatedData = $request->validate([
        'title' => 'required|string|max:255',
        'item_id' => 'required|integer|exists:item_donations,id',
        'status' => 'required|integer|exists:auction_statuses,id', 
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'starting_price' => 'required|numeric|min:0',
        'description' => 'nullable|string',
    ]);

    $auction = new Auction();
    $auction->fill($validatedData);
    $auction->auction_status_id = $validatedData['status'];
    if ($auction->save()) {
        return response()->json(['message' => 'Auction created successfully', 'data' => $auction], 201);
    } else {
        return response()->json(['message' => 'Failed to create auction'], 500);
    }
}

// Edit an existing auction
public function editAuction(Request $request, $id)
{
    $validatedData = $request->validate([
        'title' => 'required|string|max:255',
        'item_id' => 'required|integer|exists:item_donations,id',
        'status' => 'required|integer|exists:auction_statuses,id', 
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'starting_price' => 'required|numeric|min:0',
        'description' => 'nullable|string',
    ]);

    $auction = Auction::findOrFail($id);
    $auction->fill($validatedData);
    $auction->auction_status_id = $validatedData['status'];


    if ($auction->save()) {
        return response()->json(['message' => 'Auction updated successfully', 'data' => $auction], 200);
    } else {
        return response()->json(['message' => 'Failed to update auction'], 500);
    }
}

public function deleteAuction($id)
{
    $auction = Auction::findOrFail($id);


    $auction->delete();

    return response()->json(['message' => 'Auction deleted successfully'], 204);
}

public function getAuctionStatuses()
{
    $statuses = AuctionStatus::all(); 
    return response()->json($statuses, 200);
}

public function getAuctionItems()
{
    $items = ItemDonation::where('status_id', 1)->with('donor')->get(); 
    return response()->json($items, 200);
}

public function getAllItems()
{
    $items = ItemDonation::all(); 
    return response()->json($items, 200);
}

public function getValuableItemDetails($id)
{
    $items = ItemDonation::findOrFail($id); 
    return response()->json($items, 200);
}





}
