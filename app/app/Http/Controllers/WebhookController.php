<?php

namespace App\Http\Controllers;

use App\Models\Participation;
use Illuminate\Http\Request;
use Mollie\Laravel\Facades\Mollie;

class WebhookController extends Controller
{
    public function webhookMollie(Request $request)
    {
        $paymentId = $request->input('id');
        $payment = Mollie::api()->payments->get($paymentId);

        if ($payment->isPaid()) {
            Participation::where('user_id', $payment->metadata->user_id)->where('event_id', $payment->metadata->event_id)->update([
                'paid' => true
            ]);
        }

        return response('OK', 200);
    }
}
