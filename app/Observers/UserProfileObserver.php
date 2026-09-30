<?php

declare(strict_types=1);

namespace App\Observers;

use App\DTOs\UserProfileUpdatedDto;
use App\Models\UserProfile;
use App\Services\EventPublisher\RedisPublisherService;

class UserProfileObserver
{
    public function __construct(
        private readonly RedisPublisherService $redisPublisherService,
    ) {}

    /**
     * Handle the UserProfile "created" event.
     */
    public function created(UserProfile $userProfile): void
    {
        $this->broadcastProfileChange($userProfile);
    }

    /**
     * Handle the UserProfile "updated" event.
     */
    public function updated(UserProfile $userProfile): void
    {
        $this->broadcastProfileChange($userProfile);
    }

    /**
     * Transform the model to a DTO and publish to Redis via the Service layer.
     */
    private function broadcastProfileChange(UserProfile $userProfile): void
    {
        $dto = UserProfileUpdatedDto::fromModel($userProfile);

        $this->redisPublisherService->publishUserProfileUpdated($dto);
    }
}
