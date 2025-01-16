<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Photo;
use Illuminate\Http\Request;

class PhotoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $albums = Album::all()->load('photos');

        return view('photos.index', compact('albums'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create(Album $album)
    {
        return view('photos.create', compact('album'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Album $album)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'image' => 'required|image',
        ]);

        $request->user()->photos()->create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $request->image->store('photos'),
            'album_id' => $album->id,
        ]);

        return redirect()->route('albums.show', $album->id);
    }

    /**
     * Display the specified resource.
     */
    public function show(Photo $photo)
    {
        return view('photos.show', compact('photo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Photo $photo, Album $album)
    {
        return view('photos.edit', compact('photo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Photo $photo)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'image' => 'nullable|image',
        ]);
        $input = [
            'name' => $request->name,
            'description' => $request->description,
        ];
        if ($request->hasFile('image')) {
            $input['image'] = $request->image->store('photos');
        }
        $photo->update(
            $input
        );

        return redirect()->route('photos.show', $photo->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Photo $photo, Album $album)
    {
        $photo->delete();

        return redirect()->route('albums.show', $photo->album_id);
    }
}
