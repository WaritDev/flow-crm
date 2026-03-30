<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

/**
 * Persists LINE inbound chat turns in Redis for CRM/audit alongside n8n's Redis Chat Memory.
 *
 * Keys (Laravel's global REDIS_PREFIX is applied automatically): logical key
 * "line-inbound:conv:{orgId}:{lineUserId}" → Redis LIST of JSON objects.
 */
class LineInboundConversationStore
{
    public function listKey(int $organizationId, string $lineUserId): string
    {
        return 'line-inbound:conv:'.$organizationId.':'.$lineUserId;
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    public function append(int $organizationId, string $lineUserId, string $role, string $content, array $extra = []): void
    {
        $payload = array_merge([
            'ts' => now()->toIso8601String(),
            'role' => $role,
            'content' => $content,
        ], $extra);

        $key = $this->listKey($organizationId, $lineUserId);
        Redis::rpush($key, json_encode($payload, JSON_UNESCAPED_UNICODE));
        Redis::expire($key, $this->ttlSeconds());
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function all(int $organizationId, string $lineUserId): array
    {
        $key = $this->listKey($organizationId, $lineUserId);
        $raw = Redis::lrange($key, 0, -1);
        $out = [];
        foreach ($raw as $row) {
            $decoded = json_decode((string) $row, true);
            if (is_array($decoded)) {
                $out[] = $decoded;
            }
        }

        return $out;
    }

    public function delete(int $organizationId, string $lineUserId): void
    {
        Redis::del($this->listKey($organizationId, $lineUserId));
    }

    private function ttlSeconds(): int
    {
        return max(3600, (int) config('services.line_inbound.conversation_ttl_seconds', 60 * 60 * 24 * 30));
    }
}
