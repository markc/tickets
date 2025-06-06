<?php

namespace App\Http\Controllers;

use App\Models\FAQ;
use App\Models\Office;
use Illuminate\Http\Request;

class FAQController extends Controller
{
    public function index(Request $request)
    {
        $officeId = $request->get('office');
        $search = $request->get('search');

        $faqs = FAQ::published()->ordered();

        if ($officeId) {
            $faqs->where(function ($q) use ($officeId) {
                $q->where('office_id', $officeId)->orWhereNull('office_id');
            });
        } else {
            $faqs->whereNull('office_id');
        }

        if ($search) {
            $faqs->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        $faqs = $faqs->paginate(10);
        $offices = Office::orderBy('name')->get();

        return view('faq.index', compact('faqs', 'offices', 'officeId', 'search'));
    }
}
