<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class ImageController extends Controller
{
    public function getImage($filename)
    {
        $path = public_path('/storage' . $filename);

        if (!file_exists($path)) {
            abort(404, 'File not found');
        }

        $file = File::get($path);
        $response = Response::make($file, 200);
        $response->header('Content-Type', 'image/jpeg');
        return $response;
    }
}
