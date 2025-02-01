<?php

namespace App\Http\Controllers\Icons;

use App\Http\Controllers\Controller;
use App\Http\Requests\Icons\IconRequest;
use App\Models\Icons\Icon;
use Illuminate\Http\JsonResponse;

class IconController extends Controller
{
    public function index(): JsonResponse
    {
        $items = Icon::query()
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $items]);
    }

    public function store(IconRequest $request): JsonResponse
    {
        $icon = Icon::query()->create($request->validated());

        return response()->json([
            'message' => 'SVG icon uploaded successfully!',
            'icon' => $icon,
        ]);
    }

    public function edit(Icon $icon): JsonResponse
    {
        return response()->json(['data' => $icon]);
    }

    public function destroy(Icon $icon): JsonResponse
    {
        // Delete the icon
        $icon->delete();

        return response()->json([
            'message' => 'Icon deleted successfully.',
        ]);
    }
}
