<?php

namespace App\Events;

use App\Models\Land;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LandInspectionScheduled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $land;
    public $landOwner;
    public $inspectionDate;

    public function __construct($land, $landOwner, $inspectionDate)
    {
        $this->land = $land;
        $this->landOwner = $landOwner;
        $this->inspectionDate = $inspectionDate;
    
        // Log the event details
        Log::info('LandInspectionScheduled event fired:', [
            'landId' => $land->id,
            'landOwnerId' => $landOwner->id,
            'inspectionDate' => $inspectionDate,
        ]);
    }
    

    public function broadcastOn()
    {
        return new Channel('land-inspection-channel');
    }
    
    public function broadcastWith()
    {
        return [
            'message' => "Land inspection scheduled for {$this->inspectionDate} by {$this->landOwner->name}",
            'land_id' => $this->land->id,
        ];
    }
}
