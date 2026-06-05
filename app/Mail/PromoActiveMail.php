<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Promo;

class PromoActiveMail extends Mailable
{
    use Queueable, SerializesModels;

    public $promo;

    public function __construct(Promo $promo)
    {
        $this->promo = $promo;
    }

    public function build()
    {
        return $this->subject('Promo Baru: ' . $this->promo->title)
                    ->view('emails.promo_active')
                    ->with(['promo' => $this->promo]);
    }
}
