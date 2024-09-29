# Laravel Tolgee Integration

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dusanbre/laravel-tolgee.svg?style=flat-square)](https://packagist.org/packages/dusanbre/laravel-tolgee)
[![Total Downloads](https://img.shields.io/packagist/dt/dusanbre/laravel-tolgee.svg?style=flat-square)](https://packagist.org/packages/dusanbre/laravel-tolgee)

This package provides integration with [Tolgee](https://tolgee.io) service with Laravel apps.

## Installation

You can install the package via composer:

```bash
composer require dusanbre/laravel-tolgee
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-tolgee-config"
```

This is the contents of the published config file:

```php
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
];
```

## Usage

Package provides several Artisan commands to work with you translations in Tolgee.

1. Import keys from your local files into Tolgee service

```php
php artisan tolgee:keys:sync --with-vendors
```

2. Delete all keys from Tolgee service

```php
php artisan tolgee:keys:flush
```

3. Import translations from Tolgee service into your local files

```php
php artisan tolgee:translations:sync
```

You will need to have Tolgee instance set up in your config file.

## Configuration

If you want to use this locally in your project you can run it on the docker container:

1. Add service in your docker-compose.yml

```
...
    tolgee:
        image: tolgee/tolgee
        volumes:
            - sail-tolgee:/data
            - ./tolgee.config.yaml:/config.yaml
        ports:
            - '25432:25432'
            - '9090:8080'
        environment:
            spring.config.additional-location: file:///config.yaml
        networks:
            - sail
...
volumes:
    ...
    sail-tolgee:
        driver: local
```

2. Add config `tolgee.config.yaml` in root of you project

```
tolgee:
  authentication:
    enabled: true
    initial-password: admin
    initial-username: admin
    jwt-secret: <jwt-secret> // Random string
  machine-translation:
    google:
      api-key: <google-translations-api-key> // If you want to use google translation
  smtp:
    auth: false
    from: Tolgee <no-reply@nibblo.com>
    host: mailpit
    password: 'password'
    port: 1025
    ssl-enabled: false
    username: user@company.com
```

You can see more about configuration and setup
on [Tolgee docs](https://tolgee.io/platform/self_hosting/configuration?config-format=yaml)

3. Restart docker containers
4. Publish laravel translation files `php artisan lang:publish`
5. Edit local translation files
6. Use package commands to sync it with Tolgee

### NOTE

You should be able to access Tolgee service on `http://localhost:9090`</br>
When you setup is dockerized, you will need to set TOLGEE_HOST for docker internal network. In this case that would be
`http://tolgee:8080`

## Limitations

You are need to use English as base language.</br>
All operations are constrained to one project.

This will be fixed/implemented in the future.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Dusan Antonijevic](https://github.com/dusanbre)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
