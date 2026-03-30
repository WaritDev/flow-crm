<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * n8n (Node) may throw ERR_INVALID_CHAR on Authorization if the value contains stray Unicode/control chars from copy/paste.
 * Optional: send the same Sanctum plain token as Base64 in X-FlowCRM-Token; we decode and set Authorization for Sanctum.
 */
class FlowCrmN8nTokenFromBase64Header
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->bearerToken() !== null && $request->bearerToken() !== '') {
            return $next($request);
        }

        $b64 = $request->header('X-FlowCRM-Token');
        if (! is_string($b64) || $b64 === '') {
            return $next($request);
        }

        $b64 = preg_replace('/\s+/', '', $b64) ?? '';
        $decoded = base64_decode($b64, true);
        if ($decoded === false || $decoded === '') {
            return $next($request);
        }

        $token = trim($decoded);
        if ($token === '') {
            return $next($request);
        }

        $request->headers->set('Authorization', 'Bearer '.$token);

        return $next($request);
    }
}
