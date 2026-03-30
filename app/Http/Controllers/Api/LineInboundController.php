<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesAutomationTeam;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\User;
use App\Services\LineInboundConversationStore;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LineInboundController extends Controller
{
    use ResolvesAutomationTeam;

    public function __construct(
        private LineInboundConversationStore $conversations,
    ) {}

    /**
     * CRM snapshot for one LINE user: customer row + open deals + which deal is "quiet" (same rule as inactive deals).
     */
    public function customerContext(Request $request)
    {
        $user = $request->user();
        $orgId = (int) $user->organization_id;
        if (! $orgId) {
            abort(403, 'Missing organization.');
        }

        $validated = $request->validate([
            'line_user_id' => ['required', 'string', 'max:64'],
            'hours' => ['nullable', 'integer', 'min:1', 'max:720'],
            'team_id' => ['nullable', 'integer'],
        ]);

        $teamId = $this->resolveAutomationTeamId($user, $request, $orgId);

        $hours = (int) ($validated['hours'] ?? 48);
        $cutoff = now()->subHours(max($hours, 1));
        $lineUserId = $validated['line_user_id'];

        $customer = Customer::query()
            ->where('organization_id', $orgId)
            ->where('line_id', $lineUserId)
            ->first();

        if (! $customer) {
            return response()->json([
                'known_customer' => false,
                'customer_id' => null,
                'line_id' => $lineUserId,
                'hours_threshold' => $hours,
                'open_deals' => [],
                'primary_quiet_deal_id' => null,
            ]);
        }

        $dealsQuery = Deal::query()
            ->where('customer_id', $customer->id)
            ->where('team_id', $teamId)
            ->whereNull('won_at')
            ->whereNull('lost_at')
            ->with(['stage']);

        if ($user->role === 'sales') {
            $dealsQuery->where('user_id', $user->id);
        }

        $deals = $dealsQuery->orderByDesc('updated_at')->get();

        $openDeals = [];
        $primaryQuietId = null;

        foreach ($deals as $d) {
            $updatedAt = $d->updated_at;
            $isQuiet = $updatedAt !== null && $updatedAt->lt($cutoff);
            if ($isQuiet && $primaryQuietId === null) {
                $primaryQuietId = (string) $d->id;
            }

            $hoursSince = null;
            if ($updatedAt !== null) {
                $hoursSince = round($updatedAt->diffInSeconds(now()) / 3600, 2);
            }

            $openDeals[] = [
                'deal_id' => (string) $d->id,
                'deal_name' => $d->name,
                'stage_name' => $d->stage?->name,
                'next_action' => $d->next_action,
                'next_action_date' => $d->next_action_date ? $d->next_action_date->toDateString() : null,
                'updated_at' => $updatedAt ? $updatedAt->toDateTimeString() : null,
                'hours_since_deal_update' => $hoursSince,
                'is_quiet_over_threshold' => $isQuiet,
            ];
        }

        return response()->json([
            'known_customer' => true,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'line_id' => $customer->line_id,
            'hours_threshold' => $hours,
            'open_deals' => $openDeals,
            'primary_quiet_deal_id' => $primaryQuietId,
        ]);
    }

    public function upsertCustomer(Request $request)
    {
        $user = $request->user();
        $orgId = (int) $user->organization_id;
        if (! $orgId) {
            abort(403, 'Missing organization.');
        }

        $validated = $request->validate([
            'line_user_id' => ['required', 'string', 'max:64'],
            'name' => ['nullable', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:255'],
            'phone_num' => ['nullable', 'string', 'max:64'],
            'email' => ['nullable', 'email', 'max:255'],
            'team_id' => ['nullable', 'integer'],
        ]);

        $teamId = $this->resolveAutomationTeamId($user, $request, $orgId);

        $customer = Customer::firstOrNew([
            'organization_id' => $orgId,
            'line_id' => $validated['line_user_id'],
        ]);

        if (! $customer->exists) {
            $customer->team_id = $teamId;
            // n8n calls this with an org-scoped service user (usually role=manager).
            // Customers/Activities must be owned by a real sales user for Action Stream + Customers APIs.
            $customer->user_id = $this->resolveSalesAssigneeUserId($orgId, $teamId, $user);
            $customer->name = $validated['name'] ?? 'LINE User';
        }

        $customer->fill(array_filter([
            'name' => $validated['name'] ?? null,
            'nickname' => $validated['nickname'] ?? null,
            'phone_num' => $validated['phone_num'] ?? null,
            'email' => $validated['email'] ?? null,
            'team_id' => $validated['team_id'] ?? null,
        ], fn ($v) => $v !== null));

        if ($customer->team_id === null) {
            $customer->team_id = $teamId;
        }
        if (! $customer->user_id) {
            $customer->user_id = $this->resolveSalesAssigneeUserId($orgId, $teamId, $user);
        }

        $customer->save();

        return response()->json([
            'customer_id' => $customer->id,
            'line_id' => $customer->line_id,
        ]);
    }

    public function storeActivity(Request $request)
    {
        $user = $request->user();
        $orgId = (int) $user->organization_id;
        if (! $orgId) {
            abort(403, 'Missing organization.');
        }

        // n8n often interpolates null deals as the literal string "null"
        $rawDealId = $request->input('deal_id');
        if ($rawDealId === 'null' || $rawDealId === '' || $rawDealId === 'undefined') {
            $request->merge(['deal_id' => null]);
        }

        $validated = $request->validate([
            'customer_id' => ['nullable', 'integer'],
            'line_user_id' => ['nullable', 'string', 'max:64'],
            'deal_id' => ['nullable', 'integer'],
            'assignee_user_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'activity_type' => ['nullable', Rule::in(['call', 'message', 'line', 'meeting', 'note', 'email', 'task'])],
            'priority' => ['nullable', 'integer', 'min:1', 'max:3'],
            'priority_label' => ['nullable', Rule::in(['low', 'normal', 'high'])],
            'is_completed' => ['nullable', 'boolean'],
        ]);

        if (empty($validated['customer_id']) && empty($validated['line_user_id'])) {
            return response()->json([
                'message' => 'Provide customer_id or line_user_id.',
            ], 422);
        }

        $customer = null;
        if (! empty($validated['customer_id'])) {
            $customer = Customer::query()
                ->where('organization_id', $orgId)
                ->whereKey($validated['customer_id'])
                ->firstOrFail();
        } else {
            $customer = Customer::query()
                ->where('organization_id', $orgId)
                ->where('line_id', $validated['line_user_id'])
                ->firstOrFail();
        }

        $teamId = (int) $customer->team_id;
        $priority = $this->resolvePriority($validated);

        $dealId = null;
        $dealUserId = null;
        if (! empty($validated['deal_id'])) {
            $deal = Deal::query()
                ->whereKey($validated['deal_id'])
                ->where('customer_id', $customer->id)
                ->where('team_id', $teamId)
                ->where('organization_id', $orgId)
                ->firstOrFail();
            if ($user->role === 'sales' && (string) $deal->user_id !== (string) $user->id) {
                abort(403, 'Unauthorized deal.');
            }
            $dealId = (int) $deal->id;
            $dealUserId = (int) $deal->user_id;
        }

        // Activity ownership must be aligned with the sales owner of the customer/deal,
        // otherwise sales Action Stream will not show it (SalesActivitiesController filters by user_id).
        $activityUserId = $dealUserId ?? (int) $customer->user_id;

        if (! empty($validated['assignee_user_id'])) {
            $assignee = User::query()
                ->where('organization_id', $orgId)
                ->where('team_id', $teamId)
                ->whereKey((int) $validated['assignee_user_id'])
                ->where('role', 'sales')
                ->firstOrFail();
            $activityUserId = (int) $assignee->id;
        }

        $activity = Activity::create([
            'deal_id' => $dealId,
            'customer_id' => $customer->id,
            'user_id' => $activityUserId,
            'team_id' => $teamId,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'activity_type' => $validated['activity_type'] ?? 'line',
            'priority' => $priority,
            'is_completed' => $validated['is_completed'] ?? false,
        ]);

        return response()->json([
            'activity_id' => $activity->id,
            'customer_id' => $customer->id,
            'deal_id' => $activity->deal_id,
        ]);
    }

    public function appendConversation(Request $request)
    {
        $user = $request->user();
        $orgId = (int) $user->organization_id;
        if (! $orgId) {
            abort(403, 'Missing organization.');
        }

        $validated = $request->validate([
            'line_user_id' => ['required', 'string', 'max:64'],
            'role' => ['nullable', Rule::in(['user', 'assistant', 'system'])],
            'content' => ['nullable', 'string', 'max:16000'],
            'messages' => ['nullable', 'array', 'max:50'],
            'messages.*.role' => ['required_with:messages', Rule::in(['user', 'assistant', 'system'])],
            'messages.*.content' => ['required_with:messages', 'string', 'max:16000'],
            'meta' => ['nullable', 'array'],
        ]);

        $lineUserId = $validated['line_user_id'];
        $hasBatch = isset($validated['messages']) && count($validated['messages']) > 0;
        if (! $hasBatch && ($validated['content'] ?? '') === '') {
            return response()->json([
                'message' => 'Provide `content` (single turn) or non-empty `messages`.',
            ], 422);
        }

        if ($hasBatch) {
            foreach ($validated['messages'] as $row) {
                $this->conversations->append(
                    $orgId,
                    $lineUserId,
                    $row['role'],
                    $row['content'],
                    ['meta' => $validated['meta'] ?? []],
                );
            }
        } else {
            $role = $validated['role'] ?? 'user';
            $this->conversations->append(
                $orgId,
                $lineUserId,
                $role,
                (string) $validated['content'],
                ['meta' => $validated['meta'] ?? []],
            );
        }

        return response()->json(['ok' => true]);
    }

    public function showConversation(Request $request)
    {
        $user = $request->user();
        $orgId = (int) $user->organization_id;
        if (! $orgId) {
            abort(403, 'Missing organization.');
        }

        $validated = $request->validate([
            'line_user_id' => ['required', 'string', 'max:64'],
        ]);

        $rows = $this->conversations->all($orgId, $validated['line_user_id']);

        return response()->json(['messages' => $rows]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function resolvePriority(array $validated): int
    {
        if (! empty($validated['priority'])) {
            return (int) $validated['priority'];
        }
        $label = $validated['priority_label'] ?? null;
        if ($label === 'high') {
            return 3;
        }
        if ($label === 'normal') {
            return 2;
        }
        if ($label === 'low') {
            return 1;
        }

        return 2;
    }

    private function resolveSalesAssigneeUserId(int $orgId, int $teamId, object $requester): int
    {
        if (($requester->role ?? null) === 'sales') {
            return (int) $requester->id;
        }

        $sales = User::query()
            ->where('organization_id', $orgId)
            ->where('team_id', $teamId)
            ->where('role', 'sales')
            ->orderBy('id')
            ->first();

        // If the team has no sales member, fallback to any sales user in the org.
        if (! $sales) {
            $sales = User::query()
                ->where('organization_id', $orgId)
                ->where('role', 'sales')
                ->orderBy('id')
                ->first();
        }

        return $sales ? (int) $sales->id : (int) $requester->id;
    }
}
