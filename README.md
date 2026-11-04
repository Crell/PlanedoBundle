# Planedo Bundle

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

Planedo is a blog aggregator bundle for Symfony 8.  It aims to be a mostly out-of-the-box setup for a Planet-style blog aggregator, or as close to that as Bundles allow anyway.

"Planedo" is Esperanto for "Planet," as in blog-aggregator "planet."

## Installation

There are two ways to set up Planedo Bundle.

If you want to just use Planedo as a stand-alone application and be done with it, install the Planedo application.

```shell
composer project-create crell/planedo
```

That will create a new project pre-configured to use with Planedo.  Nearly all meaningful functionality is in the bundle, so you can modify the application itself to your heart's content.  Future updates to Planedo itself will come via updating the bundle through Composer.

Alternatively, you may install Planedo Bundle directly in a Symfony 8 application of your choice, via composer.

```shell
composer require crell/planedo-bundle
```

If you take this approach, there are a few manual steps necessary to wire the bundle into the application.

### Requirements

* PHP 8.4 or later
* Symfony 8.0 or later
* Doctrine ORM 3 / DBAL 4 (via DoctrineBundle 3)
* EasyAdmin 5

### Enable the bundle

Add the bundle to your `bundles.php` file:

```php
Crell\Bundle\Planedo\PlanedoBundle::class => ['all' => true],
```

Planedo's admin area is built on EasyAdmin 5, which in turn renders its UI with Symfony UX Twig Components and the Twig html-extra functions.  If you do not use Symfony Flex, make sure these bundles are enabled as well and that a `config/packages/twig_component.yaml` exists (see the [EasyAdmin installation docs](https://symfony.com/bundles/EasyAdminBundle/current/index.html)):

```php
EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle::class => ['all' => true],
Symfony\UX\TwigComponent\TwigComponentBundle::class => ['all' => true],
Twig\Extra\TwigExtraBundle\TwigExtraBundle::class => ['all' => true],
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

The above setup assumes that you want Planedo's routes to be at the root of your site.  If not, add a `prefix` for either the front-end or the login/password-reset routes as you prefer.

EasyAdmin 5 generates the admin dashboard route (`/admin`) and all CRUD routes beneath it from the `#[AdminDashboard]` attribute on Planedo's dashboard controller.  For those routes to exist, your application must enable EasyAdmin's route loader.  Symfony Flex creates this file for you when EasyAdmin is installed; otherwise create it manually:

```yaml
# config/routes/easyadmin.yaml

easyadmin:
    resource: .
    type: easyadmin.routes
```

If you need the admin area under a different path, extend `Crell\Bundle\Planedo\Controller\Admin\DashboardController` and put your own `#[AdminDashboard(routePath: '/your-path', routeName: 'planedo_admin')]` attribute on the subclass.

### User and password management

Planedo provides its own user accounts and password handling.  A mechanism to disable is coming soon.

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

### Web server note

Feed entries are identified by their own URL, so the admin "detail" page of a feed entry has a URL like `/admin/feed-entry/https://example.com/post`.  If your web server collapses consecutive slashes (nginx does by default via `merge_slashes on`), disable that for the admin area or those detail pages will not resolve.

## Database

Planedo ships its Doctrine entities and a set of migrations in the `migrations/` directory for MySQL/MariaDB.  Either copy them into your application's migrations directory or generate a migration with `bin/console doctrine:migrations:diff` after enabling the bundle.

If you are upgrading from a 1.x release of this bundle, note that the `authors` columns changed from the Doctrine `array` type (removed in DBAL 4) to `json`.  The `Version20261019000000` migration converts existing data in place.

## Setting up cron tasks

Regardless of how you run Planedo, you will need to setup two cron tasks through the cron runner of your choice.  How often you run them is up to you, but at least daily is recommended.

The first update refetches feeds to get new entries:

```shell
bin/console planedo:feed:update-all
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

[link-packagist]: https://packagist.org/packages/crell/planedo-bundle
[link-scrutinizer]: https://scrutinizer-ci.com/g/Crell/PlanedoBundle/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/Crell/PlanedoBundle
[link-downloads]: https://packagist.org/packages/crell/planedo-bundle
[link-author]: https://github.com/Crell
[link-contributors]: ../../contributors
