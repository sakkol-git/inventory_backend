<?php

declare(strict_types=1);

namespace App\Modules\Core\Controllers;

use App\Modules\Core\Http\Controllers\Controller;
use App\Modules\Core\Requests\Permission\StorePermissionRequest;
use App\Modules\Core\Requests\Permission\UpdatePermissionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    private string $guard = 'api';

    public function index(): JsonResponse
    {
        Gate::authorize('manage-roles');

        $permissions = Permission::where('guard_name', $this->guard)
            ->orderBy('name')
            ->get(['id', 'name', 'created_at']);

        return response()->json(['data' => $permissions]);
    }

    public function store(StorePermissionRequest $request): JsonResponse
    {
        Gate::authorize('manage-roles');

        $validated = $request->validated();
        $permission = Permission::create(['name' => $validated['name'], 'guard_name' => $this->guard]);

        return response()->json(['data' => ['id' => $permission->id, 'name' => $permission->name, 'guard_name' => $permission->guard_name, 'created_at' => $permission->created_at]], 201);
    }

    public function show($id): JsonResponse
    {
        Gate::authorize('manage-roles');

        $permission = Permission::where('guard_name', $this->guard)->findOrFail($id);

        return response()->json(['data' => ['id' => $permission->id, 'name' => $permission->name, 'guard_name' => $permission->guard_name, 'created_at' => $permission->created_at]]);
    }

    public function update(UpdatePermissionRequest $request, $id): JsonResponse
    {
        Gate::authorize('manage-roles');

        $permission = Permission::where('guard_name', $this->guard)->findOrFail($id);

        $validated = $request->validated();
        $permission->update(['name' => $validated['name']]);

        return response()->json(['data' => ['id' => $permission->id, 'name' => $permission->name, 'guard_name' => $permission->guard_name]]);
    }

    public function destroy($id): JsonResponse
    {
        Gate::authorize('manage-roles');

        $permission = Permission::where('guard_name', $this->guard)->findOrFail($id);
        $permission->delete();

        return response()->json(['message' => 'Permission deleted successfully']);
    }
}
