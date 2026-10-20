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

        $nativeSecret = $instance->webhook_secret;

        if ($request->headers->has('X-Redmine-Signature-256')) {
            // A native signature must never fall back to the weaker shared legacy key.
            $signature = $request->header('X-Redmine-Signature-256');
            if (!is_string($nativeSecret) || $nativeSecret === '' || !is_string($signature)) {
                return response('ERR_INVALID_SIGN', 403);
            }

            $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $nativeSecret);
            if (!hash_equals($expected, $signature)) {
                return response('ERR_INVALID_SIGN', 403);
            }

            $request->attributes->set('redmine_webhook_source', 'native');
            return $next($request);
        }

        // Existing plugin installations use a static X-Redmine-Signature token.
        // Permit it only for instances without a native per-instance HMAC secret.
        $legacySecret = config('redmineintegration.request.signature');
        $signature = $request->header('X-Redmine-Signature');
        if (
            (is_string($nativeSecret) && $nativeSecret !== '')
            || !is_string($legacySecret)
            || $legacySecret === ''
            || $legacySecret === 'DEFAULT'
            || !is_string($signature)
            || !hash_equals($legacySecret, $signature)
        ) {
            return response('ERR_INVALID_SIGN', 403);
        }

        $request->attributes->set('redmine_webhook_source', 'legacy');
        return $next($request);
    }
}
