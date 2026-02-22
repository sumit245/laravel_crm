<?php

namespace App\Contracts;

/**
 * Base interface for the Service layer pattern. Defines standard service methods that business
 * logic services must implement. Services sit between controllers and repositories.
 *
 * Data Flow:
 *   Controller → Service.method() → Business logic → Repository → Database
 *
 * @business-domain Architecture
 * @package App\Contracts
 */
interface ServiceInterface
{
    // Marker interface for service classes
    // Specific service interfaces will extend this
}
