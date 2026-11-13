<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Http\Traits\SettingTrait;

class FinalizeNotification extends Notification
{
    use Queueable;
    use SettingTrait;

    protected $trip_optimizer;

    /**
     * Create a new notification instance.
     */
    public function __construct($trip_optimizer)
    {
        $this->trip_optimizer = $trip_optimizer;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if($this->getEmailSending()) {
            return ['database', 'mail'];
        } else {
            return ['database'];
        } 
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = "TRIP OPTIMIZER - [".$this->trip_optimizer->control_number.']';
        $greeting = "Hello, {$notifiable->name}";

        $introLines = [
            "Trip Optimizer with number \"<strong>{$this->trip_optimizer->control_number}</strong>\" has been FINALIZED! by ".$this->trip_optimizer->user->name
        ];
        $outroLines = [
            "You can view or print Trip Optimizer at your earliest convenience by clicking the button above."
        ];

        $buttons = [
            [
                'url' => url('trip-optimizer/show/' . encrypt($this->trip_optimizer->id)),
                'label' => 'View B-FORM',
                'class' => 'button'
            ],
            [
                'url' => url('printPDF/' . encrypt($this->trip_optimizer->id)),
                'label' => 'Print PDF',
                'class' => 'button'
            ]
        ];


        return (new MailMessage)
            ->subject($subject)
            ->view('emails.notification', [
                'url' => url('/trip-optimizer/show/' . encrypt($this->trip_optimizer->id)),
                'greeting' => $greeting,
                'introLines' => $introLines,
                'outroLines' => $outroLines,
                'buttons' => $buttons,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'TRIP OPTIMIZER FINALIZED',
            'message' => 'Trip Optimizer - ['.$this->trip_optimizer->control_number.'] has been finalized!',
            'action_url' => url('/trip-optimizer/show/' . encrypt($this->trip_optimizer->id)),
        ];
    }
}
