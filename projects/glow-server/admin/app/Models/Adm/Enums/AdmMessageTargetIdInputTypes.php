<?php

declare(strict_types=1);

namespace App\Models\Adm\Enums;

/**
 * adm_message_distribution_inputs.target_id_input_type用
 */
enum AdmMessageTargetIdInputTypes: string
{
    case All = 'All';
    case Input = 'Input';
    case Csv = 'Csv';
}
