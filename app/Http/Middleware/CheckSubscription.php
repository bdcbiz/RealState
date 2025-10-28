<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Skip check for admin users or guests
        if (!$user || $user->role === 'admin') {
            return $next($request);
        }

        // Check if user has an active subscription
        if (!$user->hasActiveSubscription()) {
            return response()->json([
                'success' => false,
                'message' => 'ليس لديك اشتراك نشط. يرجى الاشتراك للوصول إلى هذه الميزة.',
                'message_en' => 'You do not have an active subscription. Please subscribe to access this feature.',
                'subscription_required' => true,
            ], 403);
        }

        $subscription = $user->getCurrentSubscription();

        // Check if subscription is expired
        if ($subscription->isExpired()) {
            $subscription->markAsExpired();

            return response()->json([
                'success' => false,
                'message' => 'انتهت صلاحية اشتراكك. يرجى تجديد الاشتراك للاستمرار.',
                'message_en' => 'Your subscription has expired. Please renew to continue.',
                'subscription_expired' => true,
            ], 403);
        }

        // Check if user can perform search (has remaining searches)
        if (!$subscription->canSearch()) {
            $plan = $subscription->subscriptionPlan;

            return response()->json([
                'success' => false,
                'message' => 'لقد استنفدت عدد محاولات البحث المتاحة في باقتك. يرجى ترقية الباقة للحصول على المزيد من عمليات البحث.',
                'message_en' => 'You have exhausted your available search attempts. Please upgrade your plan for more searches.',
                'search_limit_exceeded' => true,
                'current_plan' => $plan->name,
                'searches_used' => $subscription->searches_used,
                'search_limit' => $plan->search_limit,
            ], 403);
        }

        return $next($request);
    }
}
