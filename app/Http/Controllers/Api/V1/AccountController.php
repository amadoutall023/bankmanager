<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use App\Http\Resources\AccountResource;
use App\Http\Requests\ListAccountsRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class AccountController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Account::with('client.user')->notDeleted();

        // Filtres
        if ($request->has('type') && in_array($request->type, ['epargne', 'cheque'])) {
            $query->where('type', $request->type);
        }

        if ($request->has('status') && in_array($request->status, ['active', 'inactive', 'closed'])) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('account_number', 'like', "%{$search}%")
                  ->orWhereHas('client.user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Tri
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $allowedSorts = ['created_at', 'balance', 'account_number'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $order);
        }

        // Pagination
        $perPage = min($request->get('limit', 10), 100);
        $accounts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Liste des comptes récupérée avec succès',
            'data' => AccountResource::collection($accounts),
            'pagination' => [
                'currentPage' => $accounts->currentPage(),
                'totalPages' => $accounts->lastPage(),
                'totalItems' => $accounts->total(),
                'itemsPerPage' => $accounts->perPage(),
                'hasNext' => $accounts->hasMorePages(),
                'hasPrevious' => $accounts->currentPage() > 1,
            ],
            'links' => [
                'self' => $accounts->url($accounts->currentPage()),
                'next' => $accounts->nextPageUrl(),
                'first' => $accounts->url(1),
                'last' => $accounts->url($accounts->lastPage()),
            ],
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
