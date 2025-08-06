<?php

namespace App\Http\Controllers;

use App\Models\MailFolder;
use Illuminate\Http\Request;

class MailFolderController extends Controller
{
    public function index()
    { 
        $mailFolders = MailFolder::all();
        return response()->json($mailFolders);
    }

    public function store(Request $request)
    {
        $mailFolder = MailFolder::create($request->all());
        return response()->json($mailFolder, 201);
    }

    public function show($id)
    {
        $mailFolder = MailFolder::findOrFail($id);
        return response()->json($mailFolder);
    }

    public function update(Request $request, $id)
    {
        $mailFolder = MailFolder::findOrFail($id);
        $mailFolder->update($request->all());
        return response()->json($mailFolder);
    }

    public function destroy($id)
    {
        MailFolder::destroy($id);
        return response()->json(null, 204);
    }
}