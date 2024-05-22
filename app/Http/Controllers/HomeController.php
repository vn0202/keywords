<?php

namespace App\Http\Controllers;

use App\Models\ImportFile;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    //
    public function uploadFile(Request $request)
    {

        $file = $request->myFile->store("");
        ImportFile::create([
            'namespace' => 1,
            'source' => "Ahref",
            "country" => "vn",
            "path" => $file,
        ]);
        return back()->with(['success' => "Upload success"]);
    }
}
