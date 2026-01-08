<?php

declare(strict_types=1);

namespace App\Models\Adm\Enums;

/**
 * adm_message_distribution_inputs.create_status用enum
 */
enum AdmMessageCreateStatuses: string
{
    case Editing = 'Editing';
    case Pending = 'Pending';
    case Approved = 'Approved';
}
