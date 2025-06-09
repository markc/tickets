<?php

namespace App\Http\Controllers;

use App\Models\FAQ;
use App\Models\SavedSearch;
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
            'status' => 'nullable|array',
            'priority' => 'nullable|array',
            'office' => 'nullable|array',
            'assignee' => 'nullable|array',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'sort_by' => 'nullable|in:relevance,created_at,updated_at',
            'sort_order' => 'nullable|in:asc,desc',
        ]);

        $query = $request->input('q');
        $type = $request->input('type', 'all');
        $filters = $request->only(['status', 'priority', 'office', 'assignee', 'date_from', 'date_to']);
        $sortBy = $request->input('sort_by', 'relevance');
        $sortOrder = $request->input('sort_order', 'desc');

        // Initialize results with empty paginated collections
        $results = [
            'tickets' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1, [
                'path' => request()->url(),
                'pageName' => 'page',
            ]),
            'faqs' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1, [
                'path' => request()->url(),
                'pageName' => 'page',
            ]),
        ];

        if ($type === 'tickets' || $type === 'all') {
            $results['tickets'] = $this->searchTickets($query, $filters, $sortBy, $sortOrder);
        }

        if ($type === 'faqs' || $type === 'all') {
            $results['faqs'] = $this->searchFaqs($query, $filters);
        }

        // Get filter options for the UI
        $filterOptions = $this->getFilterOptions();

        // Handle saved search loading
        $savedSearchId = $request->input('saved_search');
        $savedSearch = null;
        if ($savedSearchId) {
            $savedSearch = SavedSearch::accessibleByUser(Auth::user())
                ->find($savedSearchId);
            if ($savedSearch) {
                $savedSearch->incrementUsage();
            }
        }

        // Get user's saved searches
        $savedSearches = SavedSearch::accessibleByUser(Auth::user())
            ->orderBy('usage_count', 'desc')
            ->orderBy('name')
            ->get();

        return view('search.results', compact('results', 'query', 'type', 'filters', 'sortBy', 'sortOrder', 'filterOptions', 'savedSearches', 'savedSearch'));
    }

    private function searchTickets(string $query, array $filters, string $sortBy, string $sortOrder)
    {
        // Start with Scout search if query is provided
        if (! empty(trim($query))) {
            try {
                $ticketQuery = Ticket::search($query)->query(function ($builder) use ($filters, $sortBy, $sortOrder) {
                    return $this->applyTicketFilters($builder, $filters, $sortBy, $sortOrder);
                });
            } catch (\Exception $e) {
                // Fallback to regular query if Scout fails
                $ticketQuery = $this->applyTicketFilters(Ticket::query(), $filters, $sortBy, $sortOrder);
            }
        } else {
            // If no search query, use regular query builder with filters
            $ticketQuery = $this->applyTicketFilters(Ticket::query(), $filters, $sortBy, $sortOrder);
        }

        // Apply authorization
        if (! Auth::user()->isAdmin()) {
            if (Auth::user()->isAgent()) {
                // Agents can see tickets from their offices or assigned to them
                if (is_object($ticketQuery) && method_exists($ticketQuery, 'query')) {
                    $ticketQuery = $ticketQuery->query(function ($builder) {
                        $userOfficeIds = Auth::user()->offices->pluck('id')->toArray();
                        $builder->where(function ($q) use ($userOfficeIds) {
                            $q->whereIn('office_id', $userOfficeIds)
                                ->orWhere('assigned_to_id', Auth::id());
                        });
                    });
                } else {
                    $userOfficeIds = Auth::user()->offices->pluck('id')->toArray();
                    $ticketQuery->where(function ($q) use ($userOfficeIds) {
                        $q->whereIn('office_id', $userOfficeIds)
                            ->orWhere('assigned_to_id', Auth::id());
                    });
                }
            } else {
                // Customers can only see their own tickets
                if (is_object($ticketQuery) && method_exists($ticketQuery, 'query')) {
                    $ticketQuery = $ticketQuery->query(function ($builder) {
                        $builder->where('creator_id', Auth::id());
                    });
                } else {
                    $ticketQuery->where('creator_id', Auth::id());
                }
            }
        }

        // Paginate and load relationships
        if (is_object($ticketQuery) && method_exists($ticketQuery, 'paginate')) {
            $results = $ticketQuery->paginate(10);

            // Load relationships after paginating for Scout results
            if (method_exists($ticketQuery, 'query')) {
                $results->getCollection()->load(['creator', 'assignedTo', 'office', 'status', 'priority']);
            }

            return $results;
        } else {
            // Regular Eloquent query - can use with() directly
            $ticketQuery->with(['creator', 'assignedTo', 'office', 'status', 'priority']);

            return $ticketQuery->paginate(10);
        }
    }


    private function applyTicketFilters($query, array $filters, string $sortBy, string $sortOrder)
    {
        // Status filter
        if (! empty($filters['status'])) {
            $query->whereHas('status', function ($q) use ($filters) {
                $q->whereIn('name', $filters['status']);
            });
        }

        // Priority filter
        if (! empty($filters['priority'])) {
            $query->whereHas('priority', function ($q) use ($filters) {
                $q->whereIn('name', $filters['priority']);
            });
        }

        // Office filter
        if (! empty($filters['office'])) {
            $query->whereIn('office_id', $filters['office']);
        }

        // Assignee filter
        if (! empty($filters['assignee'])) {
            $query->whereIn('assigned_to_id', $filters['assignee']);
        }

        // Date range filter
        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from'].' 00:00:00');
        }
        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to'].' 23:59:59');
        }

        // Sorting (only apply if not using Scout search for relevance)
        if ($sortBy !== 'relevance') {
            $query->orderBy($sortBy, $sortOrder);
        }

        return $query;
    }

    private function searchFaqs(string $query, array $filters)
    {
        // Start with Scout search if query is provided
        if (! empty(trim($query))) {
            try {
                $faqQuery = FAQ::search($query);

                // Office/category filter for FAQs
                if (! empty($filters['office'])) {
                    $faqQuery = $faqQuery->query(function ($builder) use ($filters) {
                        $builder->whereIn('office_id', $filters['office'])
                            ->where('is_published', true);
                    });
                } else {
                    $faqQuery = $faqQuery->query(function ($builder) {
                        $builder->where('is_published', true);
                    });
                }

                $results = $faqQuery->paginate(10);

                // Load relationships after paginating
                $results->getCollection()->load('office');

                return $results;
            } catch (\Exception $e) {
                // Fallback to regular query if Scout fails
                $faqQuery = FAQ::query()->where('is_published', true);

                if (! empty($filters['office'])) {
                    $faqQuery->whereIn('office_id', $filters['office']);
                }

                return $faqQuery->with('office')->paginate(10);
            }
        } else {
            // If no search query, use regular query builder with filters
            $faqQuery = FAQ::query()->where('is_published', true);

            if (! empty($filters['office'])) {
                $faqQuery->whereIn('office_id', $filters['office']);
            }

            return $faqQuery->with('office')->paginate(10);
        }
    }

    private function getFilterOptions()
    {
        $user = Auth::user();

        // Get statuses
        $statuses = \App\Models\TicketStatus::orderBy('name')->get(['id', 'name']);

        // Get priorities
        $priorities = \App\Models\TicketPriority::orderBy('name')->get(['id', 'name']);

        // Get offices based on user role
        if ($user->isAdmin()) {
            $offices = \App\Models\Office::orderBy('name')->get(['id', 'name']);
        } elseif ($user->isAgent()) {
            $offices = $user->offices()->orderBy('name')->get(['offices.id', 'name']);
        } else {
            $offices = \App\Models\Office::whereHas('tickets', function ($q) use ($user) {
                $q->where('creator_id', $user->id);
            })->orderBy('name')->get(['id', 'name']);
        }

        // Get assignees (agents and admins only)
        $assignees = collect();
        if ($user->isAdmin() || $user->isAgent()) {
            $assignees = \App\Models\User::whereIn('role', ['agent', 'admin'])
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        return [
            'statuses' => $statuses,
            'priorities' => $priorities,
            'offices' => $offices,
            'assignees' => $assignees,
        ];
    }

    public function saveSearch(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'search_params' => 'required|array',
            'is_public' => 'boolean',
        ]);

        $savedSearch = SavedSearch::create([
            'name' => $request->name,
            'description' => $request->description,
            'search_params' => $request->search_params,
            'user_id' => Auth::id(),
            'is_public' => $request->boolean('is_public') && (Auth::user()->isAgent() || Auth::user()->isAdmin()),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Search saved successfully',
            'saved_search' => $savedSearch,
        ]);
    }

    public function deleteSavedSearch(SavedSearch $savedSearch)
    {
        // Only allow deletion by owner or admin
        if ($savedSearch->user_id !== Auth::id() && ! Auth::user()->isAdmin()) {
            abort(403);
        }

        $savedSearch->delete();

        return response()->json([
            'success' => true,
            'message' => 'Saved search deleted successfully',
        ]);
    }

    public function getSavedSearches()
    {
        $savedSearches = SavedSearch::accessibleByUser(Auth::user())
            ->with('user:id,name')
            ->orderBy('usage_count', 'desc')
            ->orderBy('name')
            ->get();

        return response()->json([
            'saved_searches' => $savedSearches->map(function ($search) {
                return [
                    'id' => $search->id,
                    'name' => $search->name,
                    'description' => $search->description,
                    'formatted_filters' => $search->getFormattedFilters(),
                    'is_public' => $search->is_public,
                    'usage_count' => $search->usage_count,
                    'created_by' => $search->user->name,
                    'created_at' => $search->created_at->format('M j, Y'),
                    'url' => $search->getSearchUrl(),
                    'can_delete' => $search->user_id === Auth::id() || Auth::user()->isAdmin(),
                ];
            }),
        ]);
    }
}
