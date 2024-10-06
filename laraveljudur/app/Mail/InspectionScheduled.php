<?php

// app/Mail/InspectionScheduled.php

namespace App\Mail;

use App\Models\Land;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InspectionScheduled extends Mailable
{
    use Queueable, SerializesModels;

    public $land;
    public $inspectionDate;

    /**
     * Create a new message instance.
     *
     * @param Land $land
     * @param string $inspectionDate
     * @return void
     */
    public function __construct(Land $land, string $inspectionDate)
    {
        $this->land = $land;
        $this->inspectionDate = $inspectionDate;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Inspection Scheduled')
                    ->view('emails.inspection_scheduled')
                    ->with([
                        'landDescription' => $this->land->description,
                        'inspectionDate' => $this->inspectionDate,
                    ]);
    }
}
