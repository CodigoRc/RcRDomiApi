<?php

namespace App\Http\Controllers;

use App\Models\MailFilter;
use Illuminate\Http\Request;

class MailFilterController extends Controller
{
    public function index()
    {
        $mailFilters = MailFilter::all();
        return response()->json($mailFilters);
    }

    public function store(Request $request)
    {
        $mailFilter = MailFilter::create($request->all());
        return response()->json($mailFilter, 201);
    }

    public function show($id)
    {
        $mailFilter = MailFilter::findOrFail($id);
        return response()->json($mailFilter);
    }

    public function update(Request $request, $id)
    {
        $mailFilter = MailFilter::findOrFail($id);
        $mailFilter->update($request->all());
        return response()->json($mailFilter);
    }

    public function destroy($id)
    {
        MailFilter::destroy($id);
        return response()->json(null, 204);
    }
}