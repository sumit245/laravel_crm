<?php

return [
    'chunk_size' => env('TARGET_DELETION_CHUNK_SIZE', 50),
    'poles_batch_size' => env('TARGET_DELETION_POLES_BATCH', 100),
    'progress_poll_interval' => env('TARGET_DELETION_POLL_INTERVAL', 2000),
    'sync_threshold' => env('TARGET_DELETION_SYNC_THRESHOLD', 100), // Process synchronously if less than this many poles
];

