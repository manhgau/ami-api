<?php
/**
 * Created by PhpStorm.
 * User: nguyenpv
 * Date: 14/07/2022
 * Time: 13:46
 */

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * send active mail to client
 * Class ResetPassword
 * @package App\Mail
 */
class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData;   /*['active_link','expire_time','subject','to','fullname','app_name']*/

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailData)
    {
        $this->mailData = $mailData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->mailData['subject'])
            ->view('emails.client.reset-password');
    }
}