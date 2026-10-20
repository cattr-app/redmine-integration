<?php

namespace Modules\RedmineIntegration\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\RedmineIntegration\Entities\RedmineInstance;

class ValidateSignature
{
    public function handle(Request $request, Closure $next): mixed
    {
        $instance = $request->route('instance');
        if (!$instance instanceof RedmineInstance) {
            $instance = RedmineInstance::findOrFail($instance);
        }

        // Redmine 7 signs the exact HTTP body with the instance's secret.
        // A missing secret must never disable verification.
        $secret = $instance->webhook_secret;
        $signature = $request->header('X-Redmine-Signature-256');
        if (!is_string($secret) || $secret === '' || !is_string($signature)) {
            return response('ERR_INVALID_SIGN', 403);
        }

        $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);
        if (!hash_equals($expected, $signature)) {
            return response('ERR_INVALID_SIGN', 403);
        }

        return $next($request);
    }
}
