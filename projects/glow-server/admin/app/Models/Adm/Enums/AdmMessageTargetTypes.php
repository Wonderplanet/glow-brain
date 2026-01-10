<?php

declare(strict_types=1);

namespace App\Models\Adm\Enums;

/**
 * adm_message_distribution_inputs.target_type用enum
 */
enum AdmMessageTargetTypes: string
{
    case All = 'All';
    case UserId = 'UserId';
    case MyId = 'MyId';
}
