<?php

return [
    'new_request_email_enabled' => env(
        'FIXIT_NEW_REQUEST_EMAIL_ENABLED',
        false
    ),

        'new_job_email_enabled' => env(
        'FIXIT_NEW_JOB_EMAIL_ENABLED',
        false
    ),
    
    'test_recipient' => env(
        'FIXIT_EMAIL_TEST_RECIPIENT'
    ),
];