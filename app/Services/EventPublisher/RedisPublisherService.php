<?php

declare(strict_types=1);

namespace App\Services\EventPublisher;

use App\DTOs\UserProfileUpdatedDto;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Throwable;

class RedisPublisherService
{
    /**
     * Publish user profile updated event DTO to Redis channel.
     *
     * @return int The number of clients that received the message.
     */
    public function publishUserProfileUpdated(UserProfileUpdatedDto $dto): int
    {
        $channel = (string) config('services.redis_channels.user_profile_updated', 'university.user.profile_updated');
        $payload = $dto->toJson();

        try {
            $rawCount = Redis::publish($channel, $payload);
            $subscribersCount = is_numeric($rawCount) ? (int) $rawCount : 0;

            Log::info('Redis event published successfully', [
                'channel' => $channel,
                'event' => $dto->event,
                'sso_id' => $dto->ssoId,
                'subscribers_count' => $subscribersCount,
            ]);

            return $subscribersCount;
        } catch (Throwable $e) {
            Log::error('Failed to publish event to Redis', [
                'channel' => $channel,
                'sso_id' => $dto->ssoId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
