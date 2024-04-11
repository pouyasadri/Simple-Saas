<?php

namespace App\Http\Controllers;

use App\Http\Resources\FeatureResource;
use App\Models\Feature;
use App\Models\UsedFeature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;

class Feature1Controller extends Controller
{
    public ?Feature $feature = null;

    /**
     * Feature1Controller constructor.
     * @return void
     */
    public function __construct()
    {
        $this->feature = Feature::where('route_name', 'feature1.index')->where('is_active', true)->firstOrFail();
    }

    /**
     * Display the specified resource.
     * @return Response|ResponseFactory
     */
    public function index(): Response|ResponseFactory
    {
        return inertia('Feature1/Index', [
            'feature' => new FeatureResource($this->feature),
            'answer' => session('answer')
        ]);
    }

    /**
     * Calculate the sum of two numbers.
     * @param Request $request
     * @return RedirectResponse
     */
    public function calculate(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user->available_credits < $this->feature->required_credits) {
            return redirect()->back()->with('answer', 'You do not have enough credits to use this feature.');
        }
        $data = $request->validate([
            'number1' => 'required|numeric',
            'number2' => 'required|numeric',
        ]);

        $number1 = (float)$data['number1'];
        $number2 = (float)$data['number2'];

        $user->decreaseCredits($this->feature->required_credits);

        UsedFeature::create([
            'feature_id' => $this->feature->id,
            'user_id' => $user->id,
            'credits' => $this->feature->required_credits,
            'data' => $data,
        ]);

        return to_route('feature2.index')->with('answer', 'The sum of ' . $number1 . ' and ' . $number2 . ' is ' . ($number1 + $number2));
    }
}
