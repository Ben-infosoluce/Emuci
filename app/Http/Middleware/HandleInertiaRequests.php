<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            //
            'auth_user' => fn() => $request->user()
                ? array_merge(
                    // $request->user()->only('id', 'first_name', 'last_name', 'email', 'user_type'),
                    [
                        // 'roles' => $request->user()->r_user_role->pluck('name'),
                        'data' => getUserWithRelation($request->user()->id),
                        'user_permissions' => getUserPermissions(),
                        // 'companie_type' => getUserTypeAndComapnie($request->user()->id),
                    ]
                )
                : null,
            // 'auth_user' => fn() => $request->user()->r_user_role,
            //  'auth_user' => fn() => getConnectedUserRole(),

        ]);
    }
}
