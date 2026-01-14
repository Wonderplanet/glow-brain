<?php

declare(strict_types=1);

namespace App\Models\Adm\Enums;

/**
 * adm_message_distribution_inputs.account_created_type用enum
 */
enum AdmMessageAccountCreatedTypes: string
{
    case Unset = 'Unset';
    case Started = 'Started';
    case Ended = 'Ended';
    case Both = 'Both';
}
