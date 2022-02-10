# Planedo Bundle

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

Planedo is a blog aggregator bundle for Symfony 6.  It aims to be a mostly out-of-the-box setup for a Planet-style blog aggregator, or as close to that as Bundles allow anyway.

"Planedo" is Esperanto for "Planet," as in blog-aggregator "planet."

## Installation

There are two ways to set up Planedo Bundle.

If you want to just use Planedo as a stand-alone application and be done with it, install the Planedo application.

```shell
composer project-create crell/planedo
```

That will create a new project pre-configured to use with Planedo.  Nearly all meaningful functionality is in the bundle, so you can modify the application itself to your heart's content.  Future updates to Planedo itself will come via updating the bundle through Composer.

Alternatively, you may install Planedo Bundle directly in Symfony 6 application of your choice, via composer.

```shell
composer require crell/planedo-bundle
```

If you take this approach, there are a few manual steps necessary to wire the bundle into the application.

### Add a dev-dependency

At this time, Planedo uses the pre-release version of PSR-20 from the PHP-FIG.  It is not yet on Packagist, so you will need to include the following in your main application's `composer.json`:

```json
{
  "repositories": [
    {
      "type": "git",
      "url": "https://github.com/php-fig/clock.git"
    }
  ]
}
```

That will allow Composer to download the appropriate package.  This step will be unnecessary once PSR-20 is released.

### Enable the bundle

Add the bundle to your `bundles.php` file:

```php
Crell\Bundle\Planedo\CrellPlanedoBundle::class => ['all' => true],
```

### Routing

Create a new file named `config/routes/planedo.yaml` and give it the following content:

```yaml
# config/routes/planedo.yaml

planedo_admin:
    resource: '@PlanedoBundle/config/routes_admin.yaml'
    prefix: ''

planedo_public:
    resource: '@PlanedoBundle/config/routes_public.yaml'
    prefix: ''
```

The above setup assumes that you want Planedo's routes to be at the root of your site.  If not, add a `prefix` for either the front-end or admin routes as you prefer.

### User and password management

Planedo provides its own user accounts and password handling.  To use the provided tools, set the following configuration files:

```yaml
# config/reset_password.yaml

symfonycasts_reset_password:
    request_password_repository: Crell\Bundle\Planedo\Repository\ResetPasswordRequestRepository
```

```yaml
# config/security.yaml

security:
    // ...
    password_hashers:
        Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'
        Crell\Bundle\Planedo\Entity\User:
            algorithm: auto

    providers:
        app_user_provider:
            entity:
                class: Crell\Bundle\Planedo\Entity\User
                property: email
    firewalls:
        // ...
        main:
            lazy: true
            provider: app_user_provider
            form_login:
                login_path: planedo_login
                check_path: planedo_login
                enable_csrf: true
            logout:
                path: planedo_logout
            remember_me:
                secret: '%kernel.secret%' # required
                lifetime: 604800 # 1 week in seconds

    access_control:
         - { path: ^/admin, roles: ROLE_ADMIN }
```

If you set a prefix on the Planedo admin routes, adjust the `access_control` section accordingly.

You may also choose to ignore the provided user system and use your own.  In that case, ensure that users who should have access to the Planedo administrative area are given the `ROLE_ADMIN` role.  How you end up doing that is up to you.

## Configuration

Planedo includes its own minimal configuration in `config/planedo.yaml`.  There are three configuration options, all optional.  The defaults should be reasonable for most circumstances.

* `items_per_page` (default 10): This integer specifies how many feed entries will be shown per page on HTML lists, and will be shown total in RSS and Atom feeds.
* `purge_before` (default `-30 days`): Every time the purge cron job runs, any entries dated older than this will be deleted.  Entries older than this will also not be imported.  You may use any string that is valid according to PHP's [relative date format](https://www.php.net/manual/en/datetime.formats.relative.php).
* `use_plain_text` (default false): If set to true, RSS and Atom feeds will use a `text/plain` mime type instead of their appropriate XML mime type.  This is mainly useful for debugging and can be ignored 99% of the time.

For example:

```yaml
# config/planedo.yaml

planedo:
    items_per_page: 20
    purge_before: -60 days
```

## Initial setup

Once Planedo is set up, you will need to create the first admin user.  A CLI command is provided for that purpose.

```shell
bin/console planedo:create-user --email you@example.com
```

You will be prompted for a password, or you may provide one on the command line.  See the command's help text for more details.

You may now go to `<planedo admin prefix>/admin` to login, then start adding feeds.

## Setting up cron tasks

Regardless of how you run Planedo, you will need to setup two cron tasks through the cron runner of your choice.  How often you run them is up to you, but at least daily is recommended.

The first update refetches feeds to get new entries:

```shell
bin/console planedo:update-all
```

The second update deletes old entries (where "old" is defined by the `purge_before` configuration setting):

```shell
bin/console planedo:purge-old
```

## Queues

Planedo runs most tasks through Symfony's Message Bus system.  That allows it to be deferred to a queue.  While not required, it is *strongly recommended* that you wire the following messages to an async backend:

* `Crell\Bundle\Planedo\Message\ApproveEntries`
* `Crell\Bundle\Planedo\Message\PurgeOldEntries`
* `Crell\Bundle\Planedo\Message\RejectEntries`
* `Crell\Bundle\Planedo\Message\UpdateFeed`

## Theming

Out of the box, Planedo comes with barely any theming.  It works, but it's not pretty.

You are free to retheme any of the templates as you wish.  See the `templates/` directory for the full set that can be overridden.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email larry at garfieldtech dot com instead of using the issue tracker.

## Credits

- [Larry Garfield][link-author]
- [All Contributors][link-contributors]

## License

The Affero GPL version 3 or later. Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/Crell/PlanedoBundle.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/License-AGPLv3-green.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/Crell/PlanedoBundle.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/Crell/PlanedoBundle
[link-scrutinizer]: https://scrutinizer-ci.com/g/Crell/PlanedoBundle/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/Crell/PlanedoBundle
[link-downloads]: https://packagist.org/packages/Crell/PlanedoBundle
[link-author]: https://github.com/Crell
[link-contributors]: ../../contributors
