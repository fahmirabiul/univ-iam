<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SsoUserResource;
use App\Models\User;
use App\Services\Auth\SsoUserService;
use Illuminate\Http\Request;

class SsoUserController extends Controller
{
    public function __construct(
        private readonly SsoUserService $ssoUserService,
    ) {}

    /**
     * Handle the incoming request to retrieve SSO user resource.
     */
    public function show(Request $request): SsoUserResource
    {
        /** @var User $user */
        $user = $request->user();

        $userWithRelations = $this->ssoUserService->getAuthenticatedUserProfile($user);

        return new SsoUserResource($userWithRelations);
    }
}
