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

    'not_done_email_enabled' => env(
        'FIXIT_NOT_DONE_EMAIL_ENABLED',
        false
    ),

    'escalation_email_enabled' => env(
        'FIXIT_ESCALATION_EMAIL_ENABLED',
        false
    ),     

    'escalation_decision_email_enabled' => env(
        'FIXIT_ESCALATION_DECISION_EMAIL_ENABLED',
        false
    ),

    'resolution_confirmation_email_enabled' => env(
        'FIXIT_RESOLUTION_CONFIRMATION_EMAIL_ENABLED',
        false
    ),

    'test_recipient' => env(
        'FIXIT_EMAIL_TEST_RECIPIENT'
    ),



];