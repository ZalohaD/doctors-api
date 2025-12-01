<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session as CheckoutSession;

class PaymentPageController extends Controller
{
    public function success(Request $request)
    {
        {
            $sessionId = $request->query('session_id');
            if (!$sessionId) {
                abort(404);
            }

            Stripe::setApiKey(config('services.stripe.secret'));
            $session = CheckoutSession::retrieve($sessionId);

            return response()->json([
                'amount' => $session->amount_total / 100,
                'currency' => strtoupper($session->currency),
                'email' => $session->customer_email,
            ]);
        }


    }
}
