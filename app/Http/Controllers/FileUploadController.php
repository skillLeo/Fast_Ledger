<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class FileUploadController extends Controller
{
    /**
     * Serve a stored file (e.g., logos, invoices, etc.)
     */
    public function show($folder, $filename)
    {
        $path = storage_path("app/public/{$folder}/{$filename}");

        if (!File::exists($path)) {
            abort(404, 'File not found');
        }

        $mimeType = File::mimeType($path);

        return Response::file($path, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
