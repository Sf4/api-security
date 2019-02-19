# api-security

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

## Structure

If any of the following are applicable to your project, then the directory structure should follow industry best practices by being named the following.

```
bin/        
config/
src/
tests/
vendor/
```


## Install

Via Composer

``` bash
$ composer require sf4/api-security
```

## Usage

config/bundles.php
``` php
<?php

return [
    # ...
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true]
];
```

config/services.yaml
``` yaml
services:
    # ...
    
    Sf4\ApiSecurity\Command\UserRightCreator:
        class: Sf4\ApiSecurity\Command\UserRightCreator
        tags:
            - { name: 'console.command', command: 'api-security:create-user-rights' }
        arguments:
            $requestHandler: '@Sf4\Api\RequestHandler\RequestHandlerInterface'
    
    Sf4\Api\Repository\RepositoryFactory:
        class: Sf4\Api\Repository\RepositoryFactory
        arguments:
            $entityManager: '@Doctrine\ORM\EntityManagerInterface'
            $entities:
#                user: Sf4\ApiUser\Entity\User
#                user_detail: Sf4\ApiUser\Entity\UserDetail
                user: Sf4\ApiSecurity\Entity\User
                user_detail: Sf4\ApiSecurity\Entity\UserDetail
                user_role: Sf4\ApiSecurity\Entity\UserRole
                user_right: Sf4\ApiSecurity\Entity\UserRight
                user_role_right: Sf4\ApiSecurity\Entity\UserRoleRight
    # ...
    Sf4\ApiSecurity\EventSubscriber\RequestSubscriber: ~
    Sf4\ApiSecurity\Security\Authenticator\TokenAuthenticator:
            class: Sf4\ApiSecurity\Security\Authenticator\TokenAuthenticator
            arguments:
                -   '@Doctrine\ORM\EntityManagerInterface'
```

config/packages/doctrine.yaml
``` yaml
doctrine:
    # ...
    orm:
        # ...
        mappings:
            # ...
#            Sf4\ApiUser:
#                is_bundle: false
#                type: annotation
#                dir: '%kernel.project_dir%/vendor/sf4/api-user/src/Entity'
#                prefix: 'Sf4\ApiUser\Entity'
#                alias: Sf4\ApiUser
            Sf4\ApiSecurity:
                is_bundle: false
                type: annotation
                dir: '%kernel.project_dir%/vendor/sf4/api-security/src/Entity'
                prefix: 'Sf4\ApiSecurity\Entity'
                alias: Sf4\ApiSecurity
```

config/packages/security.yaml
``` yaml
security:
    encoders:
        Sf4\ApiSecurity\Entity\User:
            algorithm: argon2i
    providers:
        app_user_provider:
            entity:
                class: Sf4\ApiSecurity\Entity\User
                property: email
    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        main:
            anonymous: true
            stateless:  true
            logout: ~
            guard:
                authenticators:
                    - Sf4\ApiSecurity\Security\Authenticator\TokenAuthenticator
    access_control:
        - { path: ^/security, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/site, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/admin, roles: ROLE_ADMIN }
        - { path: ^/user, roles: ROLE_USER }
```

Create super admin
``` bash
bin/console api-security:create-super-admin
```

Create user rights
``` bash
bin/console api-security:create-user-rights
```


## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email siim.liimand@gmail.com instead of using the issue tracker.

## Credits

- [Siim Liimand][link-author]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/sf4/api-security.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/sf4/api-security/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/sf4/api-security.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/sf4/api-security.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/sf4/api-security.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/sf4/api-security
[link-travis]: https://travis-ci.org/sf4/api-security
[link-scrutinizer]: https://scrutinizer-ci.com/g/sf4/api-security/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/sf4/api-security
[link-downloads]: https://packagist.org/packages/sf4/api-security
[link-author]: https://github.com/siimliimand
