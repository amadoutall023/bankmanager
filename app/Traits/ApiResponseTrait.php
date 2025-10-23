<?php

namespace App;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Retourne une réponse de succès standardisée
     */
    protected function successResponse($data, string $message = 'Opération réussie', int $status = 200, array $meta = []): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if (!empty($meta)) {
            $response = array_merge($response, $meta);
        }

        return response()->json($response, $status);
    }

    /**
     * Retourne une réponse d'erreur standardisée
     */
    protected function errorResponse(string $message, int $status = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Génère les métadonnées de pagination
     */
    protected function getPaginationMeta($paginatedData): array
    {
        return [
            'pagination' => [
                'currentPage' => $paginatedData->currentPage(),
                'totalPages' => $paginatedData->lastPage(),
                'totalItems' => $paginatedData->total(),
                'itemsPerPage' => $paginatedData->perPage(),
                'hasNext' => $paginatedData->hasMorePages(),
                'hasPrevious' => $paginatedData->currentPage() > 1,
            ],
            'links' => [
                'self' => $paginatedData->url($paginatedData->currentPage()),
                'next' => $paginatedData->nextPageUrl(),
                'first' => $paginatedData->url(1),
                'last' => $paginatedData->url($paginatedData->lastPage()),
            ],
        ];
    }
}
