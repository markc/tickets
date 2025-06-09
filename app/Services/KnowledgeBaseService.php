<?php

namespace App\Services;

use App\Models\FAQ;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class KnowledgeBaseService
{
    /**
     * Get FAQ suggestions based on ticket content
     */
    public function getSuggestedFAQs(Ticket $ticket, int $limit = 5): Collection
    {
        $cacheKey = "faq_suggestions_ticket_{$ticket->id}";

        return Cache::remember($cacheKey, 3600, function () use ($ticket, $limit) {
            $keywords = $this->extractKeywords($ticket->subject.' '.$ticket->content);

            if (empty($keywords)) {
                return FAQ::published()
                    ->where('office_id', $ticket->office_id)
                    ->ordered()
                    ->limit($limit)
                    ->get();
            }

            // Search FAQs using keywords
            try {
                $suggestions = FAQ::search($this->buildSearchQuery($keywords))
                    ->get()
                    ->filter(function ($faq) use ($ticket) {
                        // Only show published FAQs from same office or global FAQs (office_id = null)
                        return $faq->is_published && 
                               ($faq->office_id === $ticket->office_id || $faq->office_id === null);
                    })
                    ->sortByDesc(function ($faq) use ($keywords) {
                        return $this->calculateRelevanceScore($faq, $keywords);
                    })
                    ->take($limit)
                    ->values();
            } catch (\Exception $e) {
                // Fallback: use regular database queries if search fails
                $suggestions = collect();
            }

            // If no suggestions found, fallback to manual keyword matching
            if ($suggestions->isEmpty()) {
                $allFaqs = FAQ::published()
                    ->where(function ($q) use ($ticket) {
                        $q->where('office_id', $ticket->office_id)
                          ->orWhereNull('office_id');
                    })
                    ->get();

                $suggestions = $allFaqs->filter(function ($faq) use ($keywords) {
                    return $this->calculateRelevanceScore($faq, $keywords) > 0;
                })
                ->sortByDesc(function ($faq) use ($keywords) {
                    return $this->calculateRelevanceScore($faq, $keywords);
                })
                ->take($limit)
                ->values();
            }

            return $suggestions;
        });
    }

    /**
     * Search FAQs with advanced filtering
     */
    public function searchFAQs(string $query, ?int $officeId = null, int $limit = 10): Collection
    {
        $searchResults = FAQ::search($query)
            ->get();

        return $searchResults->filter(function ($faq) use ($officeId) {
            // Only show published FAQs
            if (!$faq->is_published) {
                return false;
            }
            
            if ($officeId === null) {
                return true; // Show all published if no office filter
            }

            // Show FAQs from the specified office or global FAQs
            return $faq->office_id === $officeId || $faq->office_id === null;
        })
            ->sortByDesc(function ($faq) use ($query) {
                return $this->calculateSearchRelevance($faq, $query);
            })
            ->take($limit)
            ->values();
    }

    /**
     * Get trending/popular FAQs
     */
    public function getTrendingFAQs(?int $officeId = null, int $limit = 5): Collection
    {
        $cacheKey = "trending_faqs_office_{$officeId}";

        return Cache::remember($cacheKey, 1800, function () use ($officeId, $limit) {
            $query = FAQ::published()
                ->withCount(['usageTracking as usage_count' => function ($q) {
                    $q->where('created_at', '>=', now()->subDays(30));
                }])
                ->orderByDesc('usage_count')
                ->ordered();

            if ($officeId !== null) {
                $query->where(function ($q) use ($officeId) {
                    $q->where('office_id', $officeId)
                        ->orWhereNull('office_id');
                });
            }

            return $query->limit($limit)->get();
        });
    }

    /**
     * Track FAQ usage when inserted into ticket reply
     */
    public function trackFAQUsage(FAQ $faq, Ticket $ticket, User $user, string $context = 'reply_insertion'): void
    {
        try {
            \DB::table('faq_usage_tracking')->insert([
                'faq_id' => $faq->id,
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'context' => $context,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Invalidate relevant caches
            Cache::forget("faq_suggestions_ticket_{$ticket->id}");
            Cache::forget("trending_faqs_office_{$ticket->office_id}");
            Cache::forget('trending_faqs_office_');

        } catch (\Exception $e) {
            Log::warning('Failed to track FAQ usage: '.$e->getMessage());
        }
    }

    /**
     * Get FAQ analytics for admin dashboard
     */
    public function getFAQAnalytics(int $days = 30): array
    {
        $cacheKey = "faq_analytics_{$days}";

        return Cache::remember($cacheKey, 1800, function () use ($days) {
            $startDate = now()->subDays($days);

            return [
                'total_faqs' => FAQ::published()->count(),
                'total_usage' => \DB::table('faq_usage_tracking')
                    ->where('created_at', '>=', $startDate)
                    ->count(),
                'top_faqs' => $this->getTopFAQs($days),
                'usage_by_office' => $this->getUsageByOffice($days),
                'daily_usage' => $this->getDailyUsage($days),
                'effectiveness_rate' => $this->calculateEffectivenessRate($days),
            ];
        });
    }

    /**
     * Get formatted FAQ content for insertion
     */
    public function formatFAQForInsertion(FAQ $faq, string $format = 'markdown'): string
    {
        switch ($format) {
            case 'html':
                return "<div class='faq-reference'>".
                       "<h4>{$faq->question}</h4>".
                       "<div>{$faq->answer}</div>".
                       "<small>Reference: FAQ #{$faq->id}</small>".
                       '</div>';

            case 'plain':
                return "--- FAQ Reference ---\n".
                       "Q: {$faq->question}\n".
                       'A: '.strip_tags($faq->answer)."\n".
                       "Reference: FAQ #{$faq->id}\n".
                       '--- End FAQ ---';

            default: // markdown
                return "## {$faq->question}\n\n".
                       strip_tags($faq->answer)."\n\n".
                       "*Reference: FAQ #{$faq->id}*";
        }
    }

    /**
     * Extract keywords from text content
     */
    private function extractKeywords(string $text): array
    {
        // Remove HTML and special characters
        $text = strip_tags($text);
        $text = preg_replace('/[^\w\s]/', ' ', $text);

        // Convert to lowercase and split into words
        $words = array_filter(
            explode(' ', strtolower($text)),
            fn ($word) => strlen($word) > 3 && ! in_array($word, $this->getStopWords())
        );

        // Get word frequency and return top keywords
        $wordCounts = array_count_values($words);
        arsort($wordCounts);

        return array_keys(array_slice($wordCounts, 0, 10));
    }

    /**
     * Build search query from keywords
     */
    private function buildSearchQuery(array $keywords): string
    {
        return implode(' ', array_slice($keywords, 0, 5));
    }

    /**
     * Calculate relevance score between FAQ and keywords
     */
    private function calculateRelevanceScore(FAQ $faq, array $keywords): float
    {
        $content = strtolower($faq->question.' '.strip_tags($faq->answer));
        $score = 0;

        foreach ($keywords as $index => $keyword) {
            $count = substr_count($content, strtolower($keyword));
            // Weight earlier keywords higher
            $weight = 1 / ($index + 1);
            $score += $count * $weight;
        }

        return $score;
    }

    /**
     * Calculate search relevance score
     */
    private function calculateSearchRelevance(FAQ $faq, string $query): float
    {
        $content = strtolower($faq->question.' '.strip_tags($faq->answer));
        $queryWords = array_filter(explode(' ', strtolower($query)));

        $score = 0;
        foreach ($queryWords as $word) {
            if (strlen($word) > 2) {
                // Question matches score higher
                $questionScore = substr_count(strtolower($faq->question), $word) * 3;
                $answerScore = substr_count(strtolower(strip_tags($faq->answer)), $word);
                $score += $questionScore + $answerScore;
            }
        }

        return $score;
    }

    /**
     * Get list of stop words to filter out
     */
    private function getStopWords(): array
    {
        return [
            'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with',
            'by', 'from', 'up', 'about', 'into', 'through', 'during', 'before',
            'after', 'above', 'below', 'between', 'among', 'is', 'are', 'was',
            'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does',
            'did', 'will', 'would', 'should', 'could', 'can', 'may', 'might',
            'must', 'this', 'that', 'these', 'those', 'a', 'an', 'please',
            'how', 'what', 'when', 'where', 'why', 'which', 'who', 'there',
        ];
    }

    /**
     * Get top FAQs by usage
     */
    private function getTopFAQs(int $days): Collection
    {
        return FAQ::select('f_a_q_s.*')
            ->selectRaw('COUNT(faq_usage_tracking.id) as usage_count')
            ->leftJoin('faq_usage_tracking', 'f_a_q_s.id', '=', 'faq_usage_tracking.faq_id')
            ->where('f_a_q_s.is_published', true)
            ->where('faq_usage_tracking.created_at', '>=', now()->subDays($days))
            ->groupBy('f_a_q_s.id')
            ->orderByDesc('usage_count')
            ->limit(10)
            ->get();
    }

    /**
     * Get usage statistics by office
     */
    private function getUsageByOffice(int $days): \Illuminate\Support\Collection
    {
        return \DB::table('faq_usage_tracking')
            ->join('tickets', 'faq_usage_tracking.ticket_id', '=', 'tickets.id')
            ->join('offices', 'tickets.office_id', '=', 'offices.id')
            ->select('offices.name', \DB::raw('COUNT(*) as usage_count'))
            ->where('faq_usage_tracking.created_at', '>=', now()->subDays($days))
            ->groupBy('offices.id', 'offices.name')
            ->orderByDesc('usage_count')
            ->get();
    }

    /**
     * Get daily usage statistics
     */
    private function getDailyUsage(int $days): \Illuminate\Support\Collection
    {
        return \DB::table('faq_usage_tracking')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as usage_count')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();
    }

    /**
     * Calculate FAQ effectiveness rate
     */
    private function calculateEffectivenessRate(int $days): float
    {
        $totalTickets = \DB::table('tickets')
            ->where('created_at', '>=', now()->subDays($days))
            ->count();

        $ticketsWithFAQUsage = \DB::table('faq_usage_tracking')
            ->distinct('ticket_id')
            ->where('created_at', '>=', now()->subDays($days))
            ->count();

        return $totalTickets > 0 ? ($ticketsWithFAQUsage / $totalTickets) * 100 : 0;
    }
}
