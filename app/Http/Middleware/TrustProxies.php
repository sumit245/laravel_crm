<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

/**
 * Reverse proxy configuration middleware. Configures which proxy headers to trust
 * (X-Forwarded-For, X-Forwarded-Proto, etc.) when the application sits behind a load balancer or
 * CDN like AWS ALB or CloudFront.
 *
 * Data Flow:
 *   HTTP Request via proxy → Read forwarded headers → Set trusted proxy IPs → Correct
 *   request URL/protocol detection
 *
 * @business-domain Infrastructure
 * @package App\Http\Middleware
 */
class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
    Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
