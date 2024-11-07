<?php

return [
    /*
     * Specify the path to your language files
     * Default is 'lang' it can be set to 'resources/lang'
     */
    'lang_path' => env('TOLGEE_LANG_PATH', 'lang'),

    /*
     * Specify a language files subfolder, in order to filter specific language files.Please be aware that this applies to subfolders within your base local folders.
     * So if you have folder structure like `lang/en/messages/...`, you can set this env variable to `messages` and package will use only files from messages folder.
     */
    'lang_subfolder' => env('TOLGEE_LANG_SUBFOLDER'),

    /*
     * Host to you Tolgee service instance
     * Please note that if you are using Sail for local development, service need to be in the same docker network
     * and you will need to set host in the format of 'http://{docker_tolgee_service_name}:{docker_tolgee_service_port}'
     */
    'host' => env('TOLGEE_HOST', 'https://app.tolgee.io'),

    /**
     * Project ID of your Tolgee service.
     */
    'project_id' => env('TOLGEE_PROJECT_ID'),

    /**
     * Valid api key from Tolgee service for the given project.
     * Api key needs to have all permissions to manage project.
     */
    'api_key' => env('TOLGEE_API_KEY'),

    /**
     * Base locale of the project.
     * Please note that the locale you set here should match the base language in your project.
     */
    'locale' => env('TOLGEE_LOCALE', 'en'),

    /**
     * Override base locale translations files.
     */
    'override' => env('TOLGEE_OVERRIDE', false),

    /**
     * Accepted translation states. Check Tolgee documentation for available states.
     * Ex: REVIEWED,DISABLED,UNTRANSLATED,TRANSLATED
     */
    'accepted_states' => explode(",", env('TOLGEE_ACCEPTED_STATES', 'REVIEWED')),
];
