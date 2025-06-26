<?php

namespace App\Http\Controllers;

use App\Models\DigitalFile;
use App\Models\Order;
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


    public function download(Request $request, DigitalFile $file)
    {
        $user = $request->user();

        // ─── Authorise ───────────────────────────────────────────────
        // Customer must have an order (processing or completed) that
        // contains a product linked to this file.  Adjust as needed.
        $ownsFile = Order::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [Order::STATUS_PROCESSING, Order::STATUS_COMPLETED])
            ->whereHas('items', function ($q) use ($file) {
                $q->where('product_id', $file->product_id);
            })
            ->exists();

        if (! $ownsFile && ! $user->is_admin) {
            abort(403, 'Not authorised to download this file.');
        }

        // ─── Serve the file ──────────────────────────────────────────
        $disk     = Storage::disk($file->disk ?? 'private'); // e.g. "private"
        $path     = $file->filepath;                         // stored path
        $filename = $file->filename;                         // nice name

        if (! $disk->exists($path)) {
            abort(404, 'File not found.');
        }

        // Force-download so it doesn't open in browser
        return $disk->download($path, $filename);
    }
}
