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
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;

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

//     public function updateStatus(Request $request, $id)
// {
//     $volunteer = Volunteer::findOrFail($id);
//     $volunteer->volunteer_status = $request->input('status');
//     if ($volunteer->save()) {
//         return response()->json(['success' => true]);
//     } else {
//         return response()->json(['success' => false], 500);
//     }
// }

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

// public function updateExaminerStatus(Request $request, $id)
// {
//     $examiner = Examiner::findOrFail($id);
    
//     $examiner->examiner_status = $request->input('status');

//     if ($examiner->save()) {
//         $volunteer = Volunteer::where('user_id', $examiner->user_id)->first(); // Assuming examiner has a user_id

//         if ($volunteer) {
//             $volunteer->examiner = 1; 
//             $volunteer->save(); 
//         }

//         return response()->json(['success' => true]);
//     } else {
//         return response()->json(['success' => false], 500);
//     }
// }


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
    $currentDate = Carbon::now()->toDateString();

    $lands = Land::where('status_id', 2)
                ->where('availability_time', '>', $currentDate)
                ->get();

    return response()->json($lands);
}
public function createEvent(Request $request)
{
    Log::info('Create Event Called', ['request' => $request->all()]);

    $validatedData = $request->validate([
        'title' => 'required|max:255',
        'land_id' => 'required|integer',
        'location' => 'required|max:255',
        'date' => 'required|date',
        'time' => 'required',
        'expected_organizer_number' => 'required|integer|min:1',
        'allocatedMoney' => 'nullable|integer|min:0',
        'allocatedItems' => 'nullable|integer|min:0',
        'event_status' => 'required|integer|in:1,2,3,4',
        'description' => 'required',
        'duration' => 'nullable|integer|min:0',
        'people_helped' => 'nullable|integer|min:0',
        'goods_distributed' => 'nullable|integer|min:0',
        'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ], [
        'title.required' => 'The event title is required.',
        'title.max' => 'The event title cannot exceed 255 characters.',
        'land_id.required' => 'Please select a land for the event.',
        'location.required' => 'The location is required.',
        'location.max' => 'The location cannot exceed 255 characters.',
        'date.required' => 'The event date is required.',
        'date.date' => 'Please provide a valid date.',
        'time.required' => 'The event time is required.',
        'expected_organizer_number.required' => 'Please specify the expected number of organizers.',
        'expected_organizer_number.integer' => 'Expected organizers must be a number.',
        'expected_organizer_number.min' => 'The number of organizers must be at least 1.',
        'allocatedMoney.integer' => 'Allocated money must be a valid number.',
        'allocatedMoney.min' => 'Allocated money cannot be less than 0.',
        'allocatedItems.integer' => 'Allocated items must be a valid number.',
        'allocatedItems.min' => 'Allocated items cannot be less than 0.',
        'event_status.required' => 'Event status is required.',
        'event_status.in' => 'Invalid event status selected.',
        'description.required' => 'A description for the event is required.',
        'duration.integer' => 'Duration must be a valid number.',
        'duration.min' => 'Duration cannot be less than 0.',
        'people_helped.integer' => 'The number of people helped must be a valid number.',
        'people_helped.min' => 'The number of people helped cannot be less than 0.',
        'goods_distributed.integer' => 'Goods distributed must be a valid number.',
        'goods_distributed.min' => 'Goods distributed cannot be less than 0.',
        'image.image' => 'The uploaded file must be an image.',
        'image.mimes' => 'The image must be a file of type: jpg, jpeg, png.',
        'image.max' => 'The image size cannot exceed 2MB.',
    ]);

    $treasury = Treasury::where('id', 1)->first();
    $old_money = $treasury->money;
    $inventory = Inventory::where('id', 1)->first();
    $old_items = $inventory->items;

    if ($request->allocatedMoney > $old_money) {
        return response()->json(['errors' => ['allocatedMoney' => ['Insufficient funds in the treasury']]], 422);
    }
    if ($request->allocatedItems > $old_items) {
        return response()->json(['errors' => ['allocatedItems' => ['Insufficient quantity in the inventory']]], 422);
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
    $volunteers = Volunteer::where('volunteer_status', 2)->get();

    foreach ($volunteers as $volunteer) {
        Notification::create([
            'user_id' => $volunteer->user_id,  
            'message' => 'A new Charity Event "' . $event->title . '" has been created. Please check the details through events page.',
            'is_read' => false,
        ]);
    }

    if ($event->save()) {
        return response()->json(['message' => 'Event created successfully', 'data' => $event], 201);
    } else {
        return response()->json(['message' => 'Failed to create event'], 500);
    }

    $users = User::all();
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
    ], [
        'title.required' => 'Event title is required.',
        'land_id.required' => 'Please select a land.',
        'location.required' => 'Event location is required.',
        'date.required' => 'Event date is required.',
        'time.required' => 'Event time is required.',
        'expected_organizer_number.required' => 'Expected organizer number is required and must be at least 1.',
        'allocatedMoney.min' => 'Allocated money must be 0 or more.',
        'allocatedItems.min' => 'Allocated items must be 0 or more.',
        'event_status.required' => 'Please select an event status.',
        'description.required' => 'Event description is required.',
        'duration.min' => 'Event duration must be 0 or more.',
        'people_helped.min' => 'People helped must be 0 or more.',
        'goods_distributed.min' => 'Goods distributed must be 0 or more.',
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
        return response()->json(['errors' => ['allocatedMoney' => ['Insufficient funds in the treasury']]], 422);
    }
    if ($request->allocatedItems > $oldItem) {
        return response()->json(['errors' => ['allocatedItems' => ['Insufficient quantity in the inventory']]], 422);
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
                return response()->json(['errors' => ['image' => ['Invalid Image Type']]], 422);
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
            return response()->json(['errors' => ['image' => ['Invalid Image Type']]], 422);
        }
    }

    $volunteers = Volunteer::where('volunteer_status', 2)->get();

    foreach ($volunteers as $volunteer) {
        Notification::create([
            'user_id' => $volunteer->user_id,  
            'message' => 'There have been new updates to the "' . $event->title . '" charity event. Please check the details on the events page.',
            'is_read' => false,
        ]);
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

    $volunteers = Volunteer::where('volunteer_status', 2)->get();

    foreach ($volunteers as $volunteer) {
        Notification::create([
            'user_id' => $volunteer->user_id,  
            'message' => 'We regret to inform you that the charity event, "' . $event->title . '", has been cancelled. We appreciate your support and understanding. Thank you for your continued interest in our activities.',
            'is_read' => false,
        ]);
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

    $volunteers = Volunteer::where('volunteer_status', 2)->get();

    foreach ($volunteers as $volunteer) {
        Notification::create([
            'user_id' => $volunteer->user_id,
            'message' => 'A new charity auction, "' . $auction->title . '", has been created! Please check the details on the auctions page.',
            'is_read' => false,
        ]);
    }
    $donors = Donor::all();

    foreach ($donors as $donor) {
        Notification::create([
            'user_id' => $donor->user_id,
            'message' => 'A new charity auction, "' . $auction->title . '", has been created! Please check the details on the auctions page.',
            'is_read' => false,
        ]);
    }
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

    $volunteers = Volunteer::where('volunteer_status', 2)->get();

    foreach ($volunteers as $volunteer) {
        Notification::create([
            'user_id' => $volunteer->user_id,
            'message' => 'There have been updates to the charity auction, "' . $auction->title . '". Please check the details on the auctions page.',
            'is_read' => false,
        ]);
    }
    
    $donors = Donor::all();
    
    foreach ($donors as $donor) {
        Notification::create([
            'user_id' => $donor->user_id,
            'message' => 'There have been updates to the charity auction, "' . $auction->title . '". Please check the details on the auctions page.',
            'is_read' => false,
        ]);
    }
    if ($auction->save()) {
        return response()->json(['message' => 'Auction updated successfully', 'data' => $auction], 200);
    } else {
        return response()->json(['message' => 'Failed to update auction'], 500);
    }
}

public function deleteAuction($id)
{
    $auction = Auction::findOrFail($id);
$volunteers = Volunteer::where('volunteer_status', 2)->get();

foreach ($volunteers as $volunteer) {
    Notification::create([
        'user_id' => $volunteer->user_id,
        'message' => 'We regret to inform you that the charity auction, "' . $auction->title . '", has been cancelled. Thank you for your support and understanding.',
        'is_read' => false,
    ]);
}

$donors = Donor::all();

foreach ($donors as $donor) {
    Notification::create([
        'user_id' => $donor->user_id,
        'message' => 'We regret to inform you that the charity auction, "' . $auction->title . '", has been cancelled. Thank you for your support and understanding.',
        'is_read' => false,
    ]);
}


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


public function updateStatus(Request $request, $id)
{
    $volunteer = Volunteer::findOrFail($id);
    $newStatus = $request->input('status');

    Log::info('Updating volunteer status to: ' . $newStatus . ' for volunteer ID: ' . $volunteer->id);

    $volunteer->volunteer_status = $newStatus;

    if ($volunteer->save()) {
        Log::info('Volunteer status updated successfully to: ' . $newStatus);

        // Check for the corresponding integer values instead of strings
        if ($newStatus == 2) { // Assuming 2 is for 'Accepted'
            Log::info('Calling sendStatusEmail for accepted status.');
            $this->sendStatusEmail($volunteer, 'accepted');
        } elseif ($newStatus == 3) { // Assuming 3 is for 'Rejected'
            Log::info('Calling sendStatusEmail for rejected status.');
            $this->sendStatusEmail($volunteer, 'rejected');
        }

        return response()->json(['success' => true]);
    } else {
        Log::error('Failed to update volunteer status.');
        return response()->json(['success' => false], 500);
    }
}


protected function sendStatusEmail($volunteer, $status)
{
    $user = $volunteer->user;

    // Check if the user and their email are valid
    if (!$user) {
        Log::error('User not found for volunteer ID: ' . $volunteer->id);
        return;
    }

    if (!$user->email) {
        Log::error('Email is null for user: ' . $user->name . ' with volunteer ID: ' . $volunteer->id);
        return;
    }

    Log::info('Preparing to send email to: ' . $user->email);

    Log::info('Preparing to send email for status: ' . $status . ' to user: ' . $user->email);

    $emailSubject = '';
    $emailContent = '';

    if ($status == 'accepted') {
        $emailSubject = 'Congratulations! You have been accepted as a volunteer';
        $emailContent = '
            <h1>Welcome!</h1>
            <p>Dear ' . $user->name . ',</p>
            <p>We are pleased to inform you that your volunteer application has been <strong>accepted</strong>! Thank you for joining our team.</p>
            <p>Best regards,<br>The Team</p>';
    } elseif ($status == 'rejected') {
        $emailSubject = 'We regret to inform you that your volunteer application has been rejected';
        $emailContent = '
            <h1>Sorry!</h1>
            <p>Dear ' . $user->name . ',</p>
            <p>We regret to inform you that your volunteer application has been <strong>rejected</strong>. Thank you for your interest.</p>
            <p>Best regards,<br>The Team</p>';
    }

    try {
        Log::info('Sending email to: ' . $user->email); // Ensure email is logged before sending
        
        Mail::html($emailContent, function ($message) use ($user, $emailSubject) {
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                ->to($user->email) // Ensure the correct email is being passed
                ->subject($emailSubject);
        });
    
        Log::info('Email sent successfully to: ' . $user->email);
    } catch (\Exception $e) {
        Log::error('Failed to send email to: ' . $user->email . '. Error: ' . $e->getMessage());
    }
}
public function updateExaminerStatus(Request $request, $id)
{
    // Find the examiner or fail with a 404 error
    $examiner = Examiner::findOrFail($id);

    // Get the new status from the request
    $newStatus = $request->input('status');
    $examiner->examiner_status = $newStatus;
    Log::info('Updating examiner status to: ' . $newStatus );
    // Attempt to save the examiner status
    if ($examiner->save()) {
        // Get the associated volunteer
        $volunteer = Volunteer::where('user_id', $examiner->user_id)->first(); // Assuming examiner has a user_id

        if ($volunteer) {
            $volunteer->examiner = 1; // Set the examiner flag for the volunteer
            $volunteer->save(); // Save changes to the volunteer
        }

        // Send an email based on the new status
        if ($newStatus == 2) { // Assuming 'accepted' is the string representation of the status
            $this->sendStatusEmail2($volunteer, 'accepted');
        } elseif ($newStatus == 3) { // Assuming 'rejected' is the string representation of the status
            $this->sendStatusEmail2($volunteer, 'rejected');
        }

        return response()->json(['success' => true]);
    } else {
        return response()->json(['success' => false], 500);
    }
}

// Helper function to send the status email
protected function sendStatusEmail2($volunteer, $status)
{
    $user = $volunteer->user; // Get the user associated with the volunteer
    Log::info('Entering sendStatusEmail for volunteer ID: ' . $volunteer->id . ' with status: ' . $status);
    
    // Ensure the user and email are valid
    if (!$user) {
        Log::error('User not found for volunteer ID: ' . $volunteer->id);
        return;
    }

    if (empty($user->email)) {
        Log::error('Email is null for user: ' . $user->name . ' with volunteer ID: ' . $volunteer->id);
        return;
    }

    Log::info('Preparing to send email to: ' . $user->email);
    
    // Prepare email subject and content based on the status
    $emailSubject = '';
 
    $message = '';

    if ($status == 'accepted') {
        $emailSubject = 'Congratulations! You have been accepted as an examiner';
  
        $message = 'We are pleased to inform you that you have been <strong>accepted</strong> as an examiner!';
    } elseif ($status == 'rejected') {
        $emailSubject = 'We regret to inform you that you have been rejected as an examiner';
      
        $message = 'We regret to inform you that your application to be an examiner has been <strong>rejected</strong>. Thank you for your interest.';
    }

    try {
        Log::info('Sending email to: ' . $user->email);
        
        Mail::html($message, function ($message) use ($user, $emailSubject) {
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                ->to($user->email) // Ensure the correct email is being passed
                ->subject($emailSubject);
        });
    
        Log::info('Email sent successfully to: ' . $user->email);
    } catch (\Exception $e) {
        Log::error('Failed to send email to: ' . $user->email . '. Error: ' . $e->getMessage());
    }
}

}
