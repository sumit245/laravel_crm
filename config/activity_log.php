<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Activity Log Retention
    |--------------------------------------------------------------------------
    |
    | Number of days to retain activity log records in the primary table.
    | Older records can be pruned by a scheduled command.
    |
    */

    'retention_days' => 365,

    /*
    |--------------------------------------------------------------------------
    | Modules & Actions
    |--------------------------------------------------------------------------
    |
    | Central list of known modules and actions. This is primarily used for
    | UI labelling and optional validation inside the logger service.
    |
    */

    'modules' => [
        'auth',
        'project',
        'site',
        'task',
        'inventory',
        'pole',
        'billing',
        'rms',
        'hrm',
    ],

    'actions' => [
        'login',
        'logout',
        'login_failed',
        'created',
        'updated',
        'deleted',
        'imported',
        'exported',
        'dispatched',
        'returned',
        'replaced',
        'reassigned',
        'pushed',
        'status_changed',
    ],
];

