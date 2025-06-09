<?php

namespace App\Http\Controllers;

use App\Models\FAQ;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|max:255',
            'type' => 'nullable|in:tickets,faqs,all',
        ]);

        $query = $request->input('q');
        $type = $request->input('type', 'all');
        $results = [];

        if ($type === 'tickets' || $type === 'all') {
            $ticketQuery = Ticket::search($query);

            if (! Auth::user()->hasRole('admin')) {
                $ticketQuery->where('creator_id', Auth::id());
            }

            $results['tickets'] = $ticketQuery->paginate(10);
        }

        if ($type === 'faqs' || $type === 'all') {
            $results['faqs'] = FAQ::search($query)
                ->where('is_published', true)
                ->paginate(10);
        }

        return view('search.results', compact('results', 'query', 'type'));
    }
}
