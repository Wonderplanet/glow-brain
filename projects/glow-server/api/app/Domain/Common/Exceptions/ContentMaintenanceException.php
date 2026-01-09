<?php

declare(strict_types=1);

namespace App\Domain\Common\Exceptions;

use App\Domain\Common\Constants\ErrorCode;
use Exception;

class ContentMaintenanceException extends Exception
{
    public function __construct(string $contentType, ?string $message = null)
    {
        $message = $message ?? "Content '{$contentType}' is currently under maintenance.";
        parent::__construct($message);
    }

    public function getErrorCode(): int
    {
        return ErrorCode::CONTENT_MAINTENANCE;
    }
}
