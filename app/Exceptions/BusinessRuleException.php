<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;

/**
 * Violação de uma regra de negócio (ex.: transição de status inválida).
 *
 * Renderizada automaticamente como HTTP 422 com `{ "message": "..." }`,
 * o mesmo formato das falhas de validação, simplificando o tratamento no frontend.
 */
class BusinessRuleException extends RuntimeException
{
    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 422);
    }
}
