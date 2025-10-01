<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;

class CountryController extends Controller
{
    /**
     * Get all countries
     */
    public function index()
    {
        try {
            $countries = Country::select('id', 'name', 'code', 'lat', 'long')
                ->orderBy('name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $countries
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching countries: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific country by ID
     */
    public function show($id)
    {
        try {
            $country = Country::find($id);
            
            if (!$country) {
                return response()->json([
                    'success' => false,
                    'message' => 'Country not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $country
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching country: ' . $e->getMessage()
            ], 500);
        }
    }
}
