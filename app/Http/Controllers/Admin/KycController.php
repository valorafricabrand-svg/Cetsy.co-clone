<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kyc;
use Illuminate\Http\Request;

class KycController extends Controller
{
    /**
     * Display a listing of KYC submissions
     */
    public function index()
    {
        $kycs = Kyc::with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.kyc.index', compact('kycs'));
    }

    /**
     * Display the specified KYC submission
     */
    public function show(Kyc $kyc)
    {
        $kyc->load(['user', 'documents']);

        return view('admin.kyc.show', compact('kyc'));
    }
}
