<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;
use App\Models\Country;

class CityController extends Controller
{
    /**
     * Get all cities
     */
    public function index()
    {
        try {
            $cities = City::with('country:id,name')
                ->select('id', 'country_id', 'name', 'lat', 'long')
                ->orderBy('name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $cities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching cities: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cities by country ID
     */
    public function getByCountry(Request $request)
    {
        try {
            $countryId = $request->input('country_id');
            
            if (!$countryId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Country ID is required'
                ], 400);
            }

            // Verify country exists
            $country = Country::find($countryId);
            if (!$country) {
                return response()->json([
                    'success' => false,
                    'message' => 'Country not found'
                ], 404);
            }

            $cities = City::where('country_id', $countryId)
                ->select('id', 'country_id', 'name', 'lat', 'long')
                ->orderBy('name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $cities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching cities: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific city by ID
     */
    public function show($id)
    {
        try {
            $city = City::with('country:id,name')->find($id);
            
            if (!$city) {
                return response()->json([
                    'success' => false,
                    'message' => 'City not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $city
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching city: ' . $e->getMessage()
            ], 500);
        }
    }
}
