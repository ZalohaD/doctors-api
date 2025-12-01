<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use App\Models\DoctorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Webhook;
use Stripe\PaymentIntent;

class PaymentController extends Controller
{
    /**
     * Створення Stripe Checkout Session
     */
    public function createCheckoutSession(Request $request)
    {
        $request->validate([
            'doctor_service_id' => 'required|integer',
            'from' => 'required|date',
            'to' => 'required|date',
        ]);

        $service = DoctorService::with('doctor.clinics')->findOrFail($request->doctor_service_id);
        $doctor = $service->doctor;
        $user = auth('sanctum')->user();

        Stripe::setApiKey(config('services.stripe.secret'));

        $amount = intval(round($service->price * 100));

        $session = CheckoutSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $service->name,
                    ],
                    'unit_amount' => $amount,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => url('payment/success?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url' => url('payment/cancel'),
            'customer_email' => $user->email ?? null,

            'payment_intent_data' => [
                'metadata' => [
                    'user_id' => $user->id,
                    'doctor_id' => $doctor->id,
                    'service_id' => $service->id,
                    'clinic_id' => $doctor->clinics->first()->id ?? null,
                    'from' => date('c', strtotime($request->from)),
                    'to' => date('c', strtotime($request->to)),
                ],
            ],
        ]);

        return response()->json(['url' => $session->url]);
    }

    /**
     * Обробка Stripe Webhook
     */
    public function handleWebhook(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');


        $secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error: '.$e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }

        Log::info('Stripe event received', ['type' => $event->type]);

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            try {
                $paymentIntent = PaymentIntent::retrieve($session->payment_intent);

                $metadata = $paymentIntent->metadata->toArray();


                Log::info('Stripe metadata received', $metadata);
            } catch (\Exception $e) {
                Log::error('Cannot retrieve payment intent metadata: '.$e->getMessage());
                return response('Error fetching metadata', 400);
            }

            if (empty($metadata['user_id']) || empty($metadata['doctor_id']) || empty($metadata['service_id'])) {
                Log::warning('Stripe webhook missing metadata', $metadata);
                return response('Missing metadata', 400);
            }

            $user = User::find($metadata['user_id']);
            $doctor = Doctor::find($metadata['doctor_id']);
            $service = DoctorService::find($metadata['service_id']);

            if (!$user || !$doctor || !$service) {
                Log::error('Webhook data invalid', $metadata);
                return response('Invalid data', 400);
            }

            $appointment = Appointment::create([
                'clinic_id' => $metadata['clinic_id'] ?? null,
                'user_id' => $user->id,
                'doctor_id' => $doctor->id,
                'service_id' => $service->id,
                'status' => 1,
                'firstname' => $user->name ?? 'Unknown',
                'lastname' => $user->lastname ?? '',
                'from' => $metadata['from'] ?? now(),
                'to' => $metadata['to'] ?? now()->addMinutes($service->duration ?? 30),
            ]);

            Log::info('Appointment created after Stripe payment', $appointment->toArray());
        } else {
            Log::info('Stripe event not handled', ['type' => $event->type]);
        }

        return response('Webhook handled', 200);
    }
}
