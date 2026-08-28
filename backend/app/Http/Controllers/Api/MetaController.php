<?php

namespace App\Http\Controllers\Api;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\DepartmentResource;
use App\Models\Category;
use App\Models\Department;
use Illuminate\Http\JsonResponse;

class MetaController extends Controller
{
    public function meta(): JsonResponse
    {
        $categories = Category::where('is_active', true)->get();
        $departments = Department::orderBy('name')->get();

        $priorities = array_map(fn($p) => [
            'value' => $p->value,
            'label' => $p->label(),
        ], TicketPriority::cases());

        $statuses = array_map(fn($s) => [
            'value' => $s->value,
            'label' => $s->label(),
        ], TicketStatus::cases());

        $roles = array_map(fn($r) => [
            'value' => $r->value,
            'label' => $r->label(),
        ], UserRole::cases());

        return response()->json([
            'categories' => CategoryResource::collection($categories),
            'departments' => DepartmentResource::collection($departments),
            'priorities' => $priorities,
            'statuses' => $statuses,
            'roles' => $roles,
        ]);
    }
}
