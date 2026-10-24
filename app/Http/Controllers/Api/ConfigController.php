<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ConfigController extends Controller
{
    /**
     * Return public editorial brand and publication configuration.
     */
    public function show(): JsonResponse
    {
        return response()->json([
            'brand_name' => config('editorial.brand_name'),
            'tagline' => config('editorial.tagline'),
            'description' => config('editorial.description'),
            'founding_year' => config('editorial.founding_year'),
            'contact_email' => config('editorial.contact_email'),
            'navigation' => config('editorial.navigation'),
            'socials' => config('editorial.socials'),
        ]);
    }
}
