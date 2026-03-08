<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Http\Requests\StoreSettingRequest;
use App\Modules\Core\Http\Requests\UpdateSettingRequest;
use App\Modules\Core\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Settings Controller
 *
 * Manages application settings and configurations.
 *
 * @group Core Module
 * @subgroup Settings
 */
class SettingController extends Controller
{
    /**
     * List all settings
     *
     * Retrieve all application settings with pagination support.
     *
     * @queryParam page int Page number for pagination. Example: 1
     * @queryParam per_page int Items per page. Example: 15
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "key": "app.name",
     *       "value": "Soft ERP",
     *       "type": "string",
     *       "created_at": "2024-01-01T00:00:00.000000Z",
     *       "updated_at": "2024-01-01T00:00:00.000000Z"
     *     }
     *   ],
     *   "meta": {
     *     "current_page": 1,
     *     "per_page": 15,
     *     "total": 1
     *   }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $settings = Setting::query()
            ->when($request->search, function ($query, $search) {
                $query->where('key', 'like', "%{$search}%");
            })
            ->paginate($request->per_page ?? 15);

        return response()->json($settings);
    }

    /**
     * Get a specific setting
     *
     * Retrieve a single setting by its key.
     *
     * @urlParam key string required The setting key. Example: app.name
     *
     * @response 200 {
     *   "data": {
     *     "key": "app.name",
     *     "value": "Soft ERP",
     *     "type": "string",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "message": "Setting not found"
     * }
     */
    public function show(string $key): JsonResponse
    {
        $setting = Setting::where('key', $key)->firstOrFail();

        return response()->json(['data' => $setting]);
    }

    /**
     * Create a new setting
     *
     * Store a new application setting.
     *
     * @bodyParam key string required The setting key. Example: app.timezone
     * @bodyParam value mixed required The setting value. Example: America/Sao_Paulo
     * @bodyParam type string The setting type (string, integer, boolean, json). Example: string
     *
     * @response 201 {
     *   "data": {
     *     "key": "app.timezone",
     *     "value": "America/Sao_Paulo",
     *     "type": "string",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     */
    public function store(StoreSettingRequest $request): JsonResponse
    {
        $setting = Setting::create($request->validated());

        return response()->json(['data' => $setting], 201);
    }

    /**
     * Update an existing setting
     *
     * Update the value of an existing setting.
     *
     * @urlParam key string required The setting key. Example: app.name
     * @bodyParam value mixed required The new setting value. Example: New ERP Name
     *
     * @response 200 {
     *   "data": {
     *     "key": "app.name",
     *     "value": "New ERP Name",
     *     "type": "string",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T12:00:00.000000Z"
     *   }
     * }
     */
    public function update(UpdateSettingRequest $request, string $key): JsonResponse
    {
        $setting = Setting::where('key', $key)->firstOrFail();
        $setting->update($request->validated());

        return response()->json(['data' => $setting]);
    }

    /**
     * Delete a setting
     *
     * Remove a setting from the application.
     *
     * @urlParam key string required The setting key. Example: app.name
     *
     * @response 204 {}
     */
    public function destroy(string $key): JsonResponse
    {
        $setting = Setting::where('key', $key)->firstOrFail();
        $setting->delete();

        return response()->json(null, 204);
    }
}
