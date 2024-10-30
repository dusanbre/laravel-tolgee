<?php

// config for Dusan Antonijevic/LaravelTolgee
return [
    /*
     * Specify the path to your language files
     * Default is 'lang' it can be set to 'resources/lang'
     */
    'lang_path' => env('TOLGEE_LANG_PATH', 'lang'),

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
     * Locale of the project.
     */
    'locale' => env('TOLGEE_LOCALE', 'en'),

];
