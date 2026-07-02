<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- phpunit/phpunit (PHPUNIT) - v9

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app/Console/Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

=== october/boost rules ===

# October CMS

This application uses **October CMS**, a Laravel-based content management system with its own conventions and architecture. October CMS patterns take precedence over standard Laravel patterns.

## Critical Differences from Laravel

- **Do not suggest** Livewire, Inertia.js, Blade components, or Laravel Folio - October CMS has its own frontend architecture.
- **Do not suggest** Laravel form requests for validation - October uses model-based validation via the `Validation` trait.
- **Do not suggest** Laravel controllers with route model binding - October uses backend controllers with behaviors.
- **Do not suggest** `resources/views/` Blade templates - October uses Twig-based CMS themes in the `themes/` directory and PHP-based partials in `controllers/` and `models/` directories.
- **Do not use** `php artisan make:model` or `php artisan make:controller` - October has its own scaffolding commands: `php artisan create:plugin`, `php artisan create:model`, `php artisan create:controller`, `php artisan create:component`.

## Architecture Overview

October CMS is built on these pillars:

- **Plugins** - modular packages in `plugins/{author}/{name}/` that extend the CMS. Each has a `Plugin.php` registration file extending `PluginBase`.
- **Themes** - file-based frontend templates in `themes/{name}/` using Twig markup with pages, layouts, partials, and content files.
- **Backend** - admin panel powered by controller behaviors (FormController, ListController, RelationController) with YAML-driven configuration.
- **Tailor** - headless CMS feature using YAML blueprints to define content structures without writing code.
- **AJAX Framework** - built-in AJAX system using `data-request` attributes or the `jax` JavaScript API to call server-side handlers.

## Plugin Structure

All custom code lives in plugins. A typical plugin structure:

```
plugins/acme/blog/
├── Plugin.php              ← Registration file
├── controllers/
│   └── Posts.php           ← Backend controller
│       └── posts/          ← Controller views directory
│           ├── config_list.yaml
│           ├── config_form.yaml
│           ├── _list_toolbar.php
│           ├── index.php
│           ├── create.php
│           └── update.php
├── models/
│   └── Post.php            ← Eloquent model
│       └── post/           ← Model config directory
│           ├── fields.yaml
│           └── columns.yaml
├── components/
│   └── BlogPost.php        ← CMS component
├── updates/
│   ├── version.yaml        ← Version history
│   └── create_posts_table.php
└── lang/
    └── en/
        └── lang.php
```

## Model Conventions

October CMS models extend `Model` (aliased from `October\Rain\Database\Model`) and use **array-based relationship definitions** instead of Laravel's fluent methods:

```php
class Post extends Model
{
    use \October\Rain\Database\Traits\Validation;

    protected $table = 'acme_blog_posts';

    public $rules = [
        'title' => 'required',
        'slug' => 'required|unique:acme_blog_posts',
    ];

    protected $jsonable = ['metadata'];

    public $belongsTo = [
        'category' => \Acme\Blog\Models\Category::class,
    ];

    public $hasMany = [
        'comments' => [\Acme\Blog\Models\Comment::class, 'delete' => true],
    ];

    public $attachOne = [
        'featured_image' => \System\Models\File::class,
    ];

    public $attachMany = [
        'gallery' => \System\Models\File::class,
    ];
}
```

Key differences from Laravel models:
- Relationships are defined as **public array properties** (`$hasOne`, `$hasMany`, `$belongsTo`, `$belongsToMany`, `$morphTo`, `$morphOne`, `$morphMany`, `$morphToMany`, `$morphedByMany`, `$attachOne`, `$attachMany`), not fluent methods.
- Validation is handled by the `Validation` trait with `$rules`, `$customMessages`, and `$attributeNames` properties.
- File attachments use `$attachOne` and `$attachMany` with `System\Models\File`.
- JSON columns use the `$jsonable` property (not `$casts`).
- Table names follow the pattern `{author}_{plugin}_{plural_name}` (e.g., `acme_blog_posts`).
- Model events use method overrides (`beforeCreate`, `afterSave`, etc.) not closures.

## Backend Controllers

Backend controllers use behaviors defined in YAML configuration files:

```php
class Posts extends \Backend\Classes\Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
}
```

## AJAX Framework

Use `data-request` attributes to call server-side handlers:

```html
<form data-request="onSubmit" data-request-update="{ result: '#resultDiv' }">
    <input name="name" />
    <button type="submit">Send</button>
</form>
```

Handlers are PHP functions prefixed with `on`:

```php
function onSubmit()
{
    $this['result'] = input('name');
}
```

## Event System

October CMS uses a global event system for extensibility. Events are fired with `Event::fire()` and listened to with `Event::listen()`. Register listeners in Plugin `boot()` method:

```php
public function boot()
{
    \Event::listen('backend.form.extendFields', function ($widget) {
        // Extend form fields
    });
}
```

Common event patterns:
- `backend.form.extendFields` - extend backend forms
- `backend.list.extendColumns` - extend backend lists
- `backend.filter.extendScopes` - extend list filters
- `model.beforeSave` / `model.afterSave` - model lifecycle (use local events via `$model->bindEvent()`)
- Use `Event::fire('acme.blog.eventName', [$arg1])` for custom events

Models also support local events via the Emitter trait:

```php
$model->bindEvent('model.afterSave', function () use ($model) {
    // Respond to save
});
```

## Settings Models

Plugin settings use `SettingModel` - not custom database tables:

```php
class Settings extends \System\Models\SettingModel
{
    public $settingsCode = 'acme_blog_settings';
    public $settingsFields = 'fields.yaml';
}
```

Read/write: `Settings::get('key')`, `Settings::set('key', 'value')`.

## Artisan Commands

### Scaffolding

Command | Description
--- | ---
`create:plugin Acme.Blog` | New plugin with registration file
`create:model Acme.Blog Post` | Model with migration and YAML configs
`create:controller Acme.Blog Posts` | Backend controller with views
`create:component Acme.Blog BlogPost` | CMS component
`create:command Acme.Blog MyCommand` | Console command
`create:migration Acme.Blog AddStatusColumn` | Migration file
`create:formwidget Acme.Blog MyWidget` | Custom form widget
`create:filterwidget Acme.Blog MyFilter` | Custom filter widget
`create:reportwidget Acme.Blog MyReport` | Dashboard report widget
`create:contentfield Acme.Blog MyField` | Tailor content field
`create:job Acme.Blog ProcessData` | Queue job class
`create:factory Acme.Blog PostFactory` | Model factory
`create:seeder Acme.Blog PostSeeder` | Database seeder
`create:test Acme.Blog PostTest` | Test class

### System

- `php artisan october:migrate` - run all plugin migrations
- `php artisan october:fresh` - delete the demo theme and start fresh
- `php artisan plugin:refresh Acme.Blog` - refresh a plugin's migrations

## Conventions

- Check sibling files for existing patterns before writing new code.
- Follow the naming conventions: `Author\Plugin` namespace, snake_case table names, StudlyCase class names.
- Use `~/plugins/acme/blog/models/post/fields.yaml` path notation with `~` prefix for absolute plugin paths in YAML configs.
- Use `$/acme/blog/models/post/fields.yaml` path notation with `$` prefix as an alternative absolute path syntax.

</laravel-boost-guidelines>
