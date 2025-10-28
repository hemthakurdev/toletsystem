<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Lead;

class UserInquiryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $leads = Lead::with('property')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return Inertia::render('User/Inquiries', [
            'leads' => $leads
        ]);
    }
}
