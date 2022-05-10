<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MoneyReceived extends Mailable
{
    use Queueable, SerializesModels;

    public $sender;
    public $receiver;
    public $transaction;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $sender, User $receiver, Transaction $transaction)
    {
        $this->sender = $sender;
        $this->receiver = $receiver;
        $this->transaction = $transaction;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Money Received')->view('emails.moneyReceived');
    }
}
