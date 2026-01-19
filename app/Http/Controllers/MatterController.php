<?php

namespace App\Http\Controllers;

use App\Models\SubMatter;
use Illuminate\Http\Request;

class MatterController extends Controller
{
    // In MatterController.php
    public function getSubMatters($id)
    {
        $submatters = SubMatter::where('matter_id', $id)->get();

        return response()->json($submatters);
    }
}
