<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Lead;

class UserInquiryController extends Controller
{
    public function index(Request $request)
    {
        $userEmail = $request->user() ? $request->user()->email : $request->input('email');
        $leads = Lead::where('email', $userEmail)
            ->orderBy('created_at', 'desc')
            ->get();
        return Inertia::render('User/Inquiries', [
            'leads' => $leads
        ]);
    }
}
