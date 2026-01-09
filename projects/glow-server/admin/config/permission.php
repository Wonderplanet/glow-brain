<?php

# Filamentが構築するテーブルにadmの接頭辞をつける
return [
    "table_names" => [
        "model_has_permissions" => "adm_model_has_permissions",
        "model_has_roles" => "adm_model_has_roles",
        "permissions" => "adm_permissions",
        "personal_access_tokens" => "adm_personal_access_tokens",
        "role_has_permissions" => "adm_role_has_permissions",
        "roles" => "adm_roles",
        "users" => "adm_users",
    ],
];
