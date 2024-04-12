<?php

namespace App\Http\Controllers;

use App\Http\Resources\FeatureResource;
use App\Http\Resources\PackageResource;
use App\Models\Feature;
use App\Models\Package;
use App\Models\Transaction;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

class CreditController extends Controller
{
    /**
     * Display the specified resource.
     * @return Response
     */
    public function index(): Response
    {
        $packages = Package::all();
        $features = Feature::where('is_active', true)->get();
        return inertia('Credit/Index', [
            'packages' => PackageResource::collection($packages),
            'features' => FeatureResource::collection($features),
            'success' => session('success'),
            'error' => session('error')
        ]);
    }

    /**
     * @param Package $package
     * @return Application|Redirector|\Illuminate\Contracts\Foundation\Application|RedirectResponse
     * @throws ApiErrorException
     */
    public function buyCredits(Package $package): Application|Redirector|\Illuminate\Contracts\Foundation\Application|RedirectResponse
    {
        try {
            $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
            $checkout_session = $stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => [
                                'name' => $package->name . ' package - ' .
                                    $package->credits . ' credits',
                            ],
                            'unit_amount' => $package->price * 100,
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'success_url' => route('credit.success', [], true),
                'cancel_url' => route('credit.cancel', [], true),
            ]);

            Transaction::create([
                'status' => 'pending',
                'price' => $package->price,
                'credits' => $package->credits,
                'session_id' => $checkout_session->id,
                'user_id' => Auth::id(),
                'package_id' => $package->id
            ]);

            return redirect($checkout_session->url);
        } catch (ApiErrorException $e) {
            // Handle Stripe API error
            return redirect()->back()->withErrors(['error' => 'Stripe API Error: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            // Handle general error
            return redirect()->back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    /**
     * @return RedirectResponse
     */
    public function success(): RedirectResponse
    {
        return to_route('credit.index')->with('success', 'You have successfully purchased credits.');
    }

    /**
     * @return RedirectResponse
     */
    public function cancel(): RedirectResponse
    {
        return to_route('credit.index')->with('error', 'There was an error processing your payment. Please try again.');
    }

    public function webhook()
    {
        //This is your stripe cli webhook secret for testing your endpoint locally
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            http_response_code(400);
            exit();
        }

        // Handle the event
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $transaction = Transaction::where('session_id', $session->id)->first();
            if ($transaction && $transaction->status === 'pending') {
                $transaction->status = 'paid';
                $transaction->save();
                $transaction->user->increaseCredits($transaction->credits);
            }
        } else {
            echo 'Received unknown event type ' . $event->type;
        }
        return response('');
    }
}
