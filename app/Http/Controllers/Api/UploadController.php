<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function uploadImage(Request $req)
    {
        $req->validate([
            'image' => 'required|image|max:5120', // max 5MB
        ]);

        $file = $req->file('image');
        $path = $file->store('analyses', 'public'); // storage/app/public/analyses
        $url = Storage::url($path); // /storage/analyses/...

        return response()->json(['url' => $url], 201);
    }
}
