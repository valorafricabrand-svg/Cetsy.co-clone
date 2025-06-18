<?php

namespace App\Http\Controllers;

use App\Models\DigitalFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DigitalFileController extends Controller
{
    /**
     * Remove the specified digital file from storage.
     *
     * @param  \App\Models\DigitalFile  $digitalFile
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(DigitalFile $digitalFile)
    {
        // Delete the physical file from storage
        if (Storage::exists($digitalFile->filepath)) {
            Storage::delete($digitalFile->filepath);
        }

        // Delete the database record
        $digitalFile->delete();

        return redirect()->back()->with('success', 'Digital file deleted successfully.');
    }
}
