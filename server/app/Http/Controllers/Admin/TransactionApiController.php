<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class TransactionApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $searchText = trim((string) $request->query('q', ''));
        $typeFilter = strtoupper(trim((string) $request->query('type', ''))); // MANUAL|CHALLENGE|ALLO|''

        $perPage = (int) $request->query('per_page', 25);
        $perPage = max(1, min($perPage, 100));

        $query = PointTransaction::query()
            ->with([
                'user:id,university_email,display_name',
                'createdBy:id,university_email,display_name',
            ])
            ->orderByDesc('created_at');

        if ($typeFilter !== '') {
            // Backward compatible: some old manual rows may have NULL context_type
            if ($typeFilter === 'MANUAL') {
                $query->where(function ($q) {
                    $q->whereNull('context_type')->orWhere('context_type', 'MANUAL');
                });
            } else {
                $query->where('context_type', $typeFilter);
            }
        }

        if ($searchText !== '') {
            $query->where(function ($q) use ($searchText) {
                $q->where('reason', 'like', "%{$searchText}%")
                    ->orWhereHas('user', function ($uq) use ($searchText) {
                        $uq->where('display_name', 'like', "%{$searchText}%")
                            ->orWhere('university_email', 'like', "%{$searchText}%");
                    });
            });
        }

        $page = $query->paginate($perPage);

        $data = $page->getCollection()->map(function (PointTransaction $t) {
            $userName = $t->user?->display_name ?: $t->user?->university_email ?: '—';
            $userEmail = $t->user?->university_email;

            $adminName = $t->createdBy
                ? ($t->createdBy->display_name ?: $t->createdBy->university_email)
                : 'Auto';

            $type = $t->context_type ?: 'MANUAL';

            return [
                'id' => $t->id,
                'user_name' => $userName,
                'user_email' => $userEmail,
                'amount' => $t->amount,
                'reason' => $t->reason,
                'type' => $type,
                'created_at' => optional($t->created_at)->toISOString(),
                'admin_name' => $adminName,
            ];
        })->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    public function students(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));
        if (mb_strlen($term) < 2) {
            return response()->json(['data' => []]);
        }

        $students = User::query()
            ->select(['id', 'display_name', 'university_email'])
            ->where(function ($q) use ($term) {
                $q->where('display_name', 'like', "%{$term}%")
                    ->orWhere('university_email', 'like', "%{$term}%");
            })
            ->orderByRaw('display_name IS NULL, display_name ASC')
            ->limit(10)
            ->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->display_name ?: $u->university_email,
                'email' => $u->university_email,
            ])
            ->values();


        return response()->json(['data' => $students]);
    }

    public function storeManual(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'amount' => ['required', 'integer', 'not_in:0'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $transaction = PointTransaction::create([
            'user_id' => $validated['user_id'],
            'amount' => $validated['amount'],
            'reason' => trim($validated['reason']),
            'context_type' => 'MANUAL',
            'context_id' => null,
            'created_by_id' => (int) $request->user()->id,
            'created_at' => Carbon::now(),
        ]);

        $transaction->load(['user:id,university_email,display_name', 'createdBy:id,university_email,display_name']);

        return response()->json([
            'data' => [
                'id' => $transaction->id,
                'user_name' => $transaction->user?->display_name ?: $transaction->user?->university_email ?: '—',
                'user_email' => $transaction->user?->university_email,
                'amount' => $transaction->amount,
                'reason' => $transaction->reason,
                'type' => $transaction->context_type ?: 'MANUAL',
                'created_at' => optional($transaction->created_at)->toISOString(),
                'admin_name' => $transaction->createdBy
                    ? ($transaction->createdBy->display_name ?: $transaction->createdBy->university_email)
                    : 'Auto',
            ],
        ], 201);
    }
}
