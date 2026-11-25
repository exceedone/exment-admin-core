<?php

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Laravel\BrowserKitTesting\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected $baseUrl = 'http://localhost:8000';

    /**
     * The assets are published once per test run, not once per test.
     *
     * @var bool
     */
    protected static $published = false;

    /**
     * Boots the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../vendor/laravel/laravel/bootstrap/app.php';

        $app->booting(function () {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Admin', \ExmentAdminCore\Admin\Facades\Admin::class);
        });

        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        $app->register('ExmentAdminCore\Admin\AdminServiceProvider');

        return $app;
    }

    public function setUp(): void
    {
        parent::setUp();

        // Start from the config the package ships and change only what the tests need.
        // A separate full copy of the config went out of date before (it had no `auth.excepts`,
        // so the login page redirected to itself forever).
        $adminConfig = array_replace_recursive(
            require __DIR__.'/../config/admin.php',
            require __DIR__.'/config/admin.php'
        );

        $this->app['config']->set('database.default', 'mysql');
        $this->app['config']->set('database.connections.mysql.host', env('MYSQL_HOST', '127.0.0.1'));
        $this->app['config']->set('database.connections.mysql.port', env('MYSQL_PORT', '3306'));
        $this->app['config']->set('database.connections.mysql.database', env('MYSQL_DATABASE', 'laravel_admin_test'));
        $this->app['config']->set('database.connections.mysql.username', env('MYSQL_USER', 'root'));
        $this->app['config']->set('database.connections.mysql.password', env('MYSQL_PASSWORD', 'password'));
        $this->app['config']->set('app.key', 'AckfSECXIvnK5r28GVIWUAxmbBSjTsmF');
        $this->app['config']->set('session.driver', 'array');
        $this->app['config']->set('cache.default', 'array');
        // the cheapest bcrypt cost: the tests make up to 100 users at a time
        $this->app['config']->set('hashing.bcrypt.rounds', 4);
        $this->app['config']->set('filesystems', require __DIR__.'/config/filesystems.php');
        $this->app['config']->set('admin', $adminConfig);

        foreach (Arr::dot(Arr::get($adminConfig, 'auth'), 'auth.') as $key => $value) {
            $this->app['config']->set($key, $value);
        }

        if (!static::$published) {
            // `admin:install` keeps an app/Admin directory that is already there, so remove the one
            // an earlier run left: the controllers must come from the stubs under test.
            (new Filesystem())->deleteDirectory(admin_path());

            $this->artisan('vendor:publish', ['--provider' => 'ExmentAdminCore\Admin\AdminServiceProvider', '--force' => true]);

            static::$published = true;
        }

        Schema::defaultStringLength(191);

        // A test run that was stopped half way leaves its tables behind, so always start empty.
        Schema::dropAllTables();

        $this->artisan('admin:install');

        $this->migrateTestTables();

        if (file_exists($routes = admin_path('routes.php'))) {
            require $routes;
        }

        require __DIR__.'/routes.php';
    }

    public function tearDown(): void
    {
        Schema::dropAllTables();

        parent::tearDown();
    }

    /**
     * run package database migrations.
     *
     * @return void
     */
    public function migrateTestTables()
    {
        $fileSystem = new Filesystem();

        $fileSystem->requireOnce(__DIR__.'/migrations/2016_11_22_093148_create_test_tables.php');

        (new CreateTestTables())->up();
    }
}
