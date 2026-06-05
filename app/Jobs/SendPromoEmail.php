<?php

namespace App\Jobs;

use App\Mail\PromoActiveMail;
use App\Models\Promo;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPromoEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $userId;
    public Promo $promo;

    public function __construct(int $userId, Promo $promo)
    {
        $this->userId = $userId;
        $this->promo = $promo;
    }

    public function handle()
    {
        $user = User::find($this->userId);
        if (! $user || ! $user->email) {
            return;
        }

        Mail::to($user->email)->send(new PromoActiveMail($this->promo));
    }
}
