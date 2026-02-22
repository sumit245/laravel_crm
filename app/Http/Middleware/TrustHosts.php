<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

/**
 * Host validation middleware. Restricts which hostnames the application trusts for incoming
 * requests. Prevents HTTP Host header attacks by validating the Host header against a whitelist.
 *
 * Data Flow:
 *   HTTP Request → Validate Host header → Trusted: proceed → Untrusted: reject request
 *
 * @business-domain Security
 * @package App\Http\Middleware
 */
class TrustHosts extends Middleware
{
    /**
     * Get the host patterns that should be trusted.
     *
     * @return array<int, string|null>
     */
    public function hosts(): array
    {
        return [
            $this->allSubdomainsOfApplicationUrl(),
        ];
    }
}
