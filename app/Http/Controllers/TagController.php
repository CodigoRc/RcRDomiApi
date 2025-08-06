<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::all()->map(function ($tag) {
            $tag->id = (string) $tag->id;
            return $tag;
        });
        return response()->json(["data" => $tags, "code" => 200]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
        ]);
    
        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors(), "code" => 422]);
        }
    
        // Verificar si el título ya existe
        if (Tag::where('title', $request->title)->exists()) {
            return response()->json(["message" => "Title already exists", "code" => 409]);
        }
    
        $tag = Tag::create($request->all());
        $tag->id = (string) $tag->id;
        return response()->json(["data" => $tag, "code" => 200]);
    }

    public function show($id)
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json(["message" => "Tag not found", "code" => 404]);
        }

        $tag->id = (string) $tag->id;
        return response()->json(["data" => $tag, "code" => 200]);
    }



    public function update($id, Request $request)
    {
        // Encuentra la etiqueta por ID
        $tag = Tag::findOrFail($id);

        // Actualiza la etiqueta con los datos del request
        $tag->update($request->all());

        // Retorna la etiqueta actualizada
        return response()->json(['data' => $id], 200);
    }


    // public function update(Request $request, $id)

    // {


    //     $title = $request->input('title');


        // $validator = Validator::make($request->all(), [
        //     'title' => 'sometimes|required|string|max:255',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json(["errors" => $validator->errors(), "code" => 422]);
        // }

        // $tag = Tag::find($id);

        // if (!$tag) {
        //     return response()->json(["message" => "Tag not found", "code" => 404]);
        // }

        // $tag->fill($request->all())->save();
        // $tag->id = (string) $tag->id;
    //     return response()->json(["data" => $request, "code" => 200]);
    // }

    public function destroy($id)
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json(["message" => "Tag not found", "code" => 404]);
        }

        $tag->delete();
        return response()->json(["message" => "Tag deleted", "code" => 200]);
    }

    public function syncTags(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tags' => 'required|array',
            'tags.*.title' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors(), "code" => 422]);
        }

        $newTags = collect($request->input('tags'));

        // Get existing tags from the database
        $existingTags = Tag::all()->keyBy('title');

        // Find tags to delete
        $tagsToDelete = $existingTags->diffKeys($newTags->keyBy('title'));

        // Find tags to add or update
        $tagsToAddOrUpdate = $newTags->keyBy('title')->diffKeys($existingTags);

        // Delete tags
        Tag::destroy($tagsToDelete->pluck('id')->toArray());

        // Add or update tags
        foreach ($tagsToAddOrUpdate as $tagData) {
            Tag::updateOrCreate(['title' => $tagData['title']], $tagData);
        }

        // Return updated list of tags
        $updatedTags = Tag::all()->map(function ($tag) {
            $tag->id = (string) $tag->id;
            return $tag;
        });

        return response()->json(["message" => "Tags synchronized successfully", "data" => $updatedTags, "code" => 200]);
    }
}