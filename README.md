<p align="center">
  <a href="https://github.com/exceedone/exment-admin-core">
    <img src="https://open-admin.org/gfx/logo.png" alt="exment-admin-core" style="height:200px;background:transparent;">
  </a>
</p>

<p align="center">⛵ <code>exment-admin-core</code> is an admin interface builder for Laravel that helps you build CRUD screens with just a few lines of code.</p>

<p align="center">This package is a fork of <a href="https://github.com/open-admin-org/open-admin" target="_blank">open-admin</a> / <a href="https://github.com/dedermus/open-admin-core" target="_blank">open-admin-core</a>, adapted for <a href="https://exment.net" target="_blank">Exment</a>.</p>

<p align="center">
  <a href="docs/en">Documentation (en)</a> |
  <a href="docs/zh">Documentation (zh)</a> |
  <a href="CHANGELOG.md">Changelog</a>
</p>

<p align="center">
    <a href="LICENSE">
        <img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="License MIT">
    </a>
    <img src="https://img.shields.io/badge/PHP-%5E8.2-777bb4.svg?style=flat-square" alt="PHP ^8.2">
    <img src="https://img.shields.io/badge/Laravel-12-ff2d20.svg?style=flat-square" alt="Laravel 12">
</p>

<p align="center">
    Inspired by <a href="https://github.com/sleeping-owl/admin" target="_blank">SleepingOwlAdmin</a>, <a href="https://github.com/zofe/rapyd-laravel" target="_blank">rapyd-laravel</a> and <a href="https://github.com/z-song/laravel-admin/" target="_blank">laravel-admin</a>.
</p>


Requirements
------------
- PHP ^8.2
- Laravel ^12.0
- Fileinfo PHP extension

Installation
------------

First install Laravel 12 and make sure the database connection settings are correct.

Create the framework skeleton:
```
composer create-project laravel/laravel example-app
```

Set the application URL, timezone and locale. In Laravel 12 `config/app.php` reads these from the environment, so edit `.env`:
```
APP_URL=http://localhost
APP_TIMEZONE=UTC
APP_LOCALE=en
```
```
php artisan storage:link
```
Create a database named `new_base` (or any other name you prefer).

Configure the database connection in `.env` (example settings for a development environment):
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=new_base
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

### Then install the admin panel

The package is installed from a Git repository (or from a local path during development) — add it to the `repositories` section of your project's `composer.json`:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/exceedone/exment-admin-core"
    }
]
```

For local development, a path repository pointing at the package directory is more convenient:

```json
"repositories": [
    {
        "type": "path",
        "url": "exment-admin-core"
    }
]
```

Then:

```
composer require exceedone/exment-admin-core
```

Next, run this command to publish the assets and the configuration:

```
php artisan vendor:publish --provider="ExmentAdminCore\Admin\AdminServiceProvider"
```
After running it you will find the configuration file at `config/admin.php`, where you can change the install directory, the database connection or the table names.

Also add the required disks to `config/filesystems.php`:
```
    'disks' => [

        ...

        'uploads' => [
            'driver' => 'local',
            'root' => public_path('uploads'),
            'url' => env('APP_URL').'/uploads',
            'visibility' => 'public',
        ],

        'admin' => [
            'driver' => 'local',
            'root' => public_path('uploads'),
            'url' => env('APP_URL').'/uploads',
            'visibility' => 'public',
        ],

        ...
```
Enabling HTTPS support:
```
    /*
    |--------------------------------------------------------------------------
    | Access via `https`
    |--------------------------------------------------------------------------
    |
    | If your page is going to be accessed via https, set it to `true`.
    |
    */
    'https' => env('ADMIN_HTTPS', true),
```


Finally, run the following command to complete the installation.
```
php artisan admin:install
```

Open `http://localhost/admin/` in your browser and log in with the username `admin` and the password `admin`.

Configuration
------------
The file `config/admin.php` contains the configuration array where you can find the default settings.

Updating
------------
Updating to a new version of exment-admin-core may require republishing the assets, which you can do with:
```
php artisan vendor:publish --tag=open-admin-assets --force
```

RTL (right-to-left) support
------------
Go to `<YOUR_PROJECT_PATH>\vendor\exceedone\exment-admin-core\src\Traits\HasAssets.php` and edit the `$baseCss` array to load the right-to-left (rtl) versions of the Bootstrap and AdminLTE CSS files.    
Change **bootstrap.min.css** to **bootstrap.rtl.min.css**    
Change **AdminLTE.min.css** to **AdminLTE.rtl.min.css**

## Extensions

> ⚠️ The extensions listed below are written for `laravel-admin` / `open-admin` and use the old namespaces (`Encore\Admin`, `OpenAdminCore\Admin`). **They do not work with `ExmentAdminCore\Admin` out of the box** — a fork with the namespace replaced is required. The tables are kept as a reference of what is available.

### Extensions by Zong

| Extension                                        | Description                                                                                     | laravel-admin |
| ------------------------------------------------ |-------------------------------------------------------------------------------------------------|---------------|
| [helpers](https://github.com/laravel-admin-extensions/helpers)             | Several tools to help you in development                                                        | ~1.0.2        |
| [media-manager](https://github.com/laravel-admin-extensions/media-manager) | Provides a web interface to manage local files                                                  | ~1.0.2        |
| [api-tester](https://github.com/laravel-admin-extensions/api-tester) | Helps you test your local Laravel APIs                                                          | ~1.0.2        |
| [scheduling](https://github.com/laravel-admin-extensions/scheduling) | Task scheduling manager for laravel-admin                                                       | ~1.5          |
| [redis-manager](https://github.com/laravel-admin-extensions/redis-manager) | Redis manager for laravel-admin                                                                 | ~1.5          |
| [backup](https://github.com/laravel-admin-extensions/backup) | An admin interface for managing backups                                                         | ~1.5          |
| [log-viewer](https://github.com/laravel-admin-extensions/log-viewer) | Log viewer for Laravel                                                                          | ~1.5          |
| [config](https://github.com/laravel-admin-extensions/config) | Config manager for laravel-admin                                                                | ~1.5          |
| [reporter](https://github.com/laravel-admin-extensions/reporter) | Provides a developer-friendly web interface to view the exception                               | ~1.5          |
| [wangEditor](https://github.com/laravel-admin-extensions/wangEditor) | A rich text editor based on [wangeditor](http://www.wangeditor.com/)                            | ~1.6          |
| [summernote](https://github.com/laravel-admin-extensions/summernote) | A rich text editor based on [summernote](https://summernote.org/)                               | ~1.6          |
| [china-distpicker](https://github.com/laravel-admin-extensions/china-distpicker) | A Chinese province/city/district picker based on [distpicker](https://github.com/fengyuanchen/distpicker) | ~1.6          |
| [simplemde](https://github.com/laravel-admin-extensions/simplemde) | A markdown editor based on [simplemde](https://github.com/sparksuite/simplemde-markdown-editor) | ~1.6          |
| [phpinfo](https://github.com/laravel-admin-extensions/phpinfo) | Integrate the `phpinfo` page into laravel-admin                                                 | ~1.6          |
| [php-editor](https://github.com/laravel-admin-extensions/php-editor) <br/> [python-editor](https://github.com/laravel-admin-extensions/python-editor) <br/> [js-editor](https://github.com/laravel-admin-extensions/js-editor)<br/> [css-editor](https://github.com/laravel-admin-extensions/css-editor)<br/> [clike-editor](https://github.com/laravel-admin-extensions/clike-editor)| Several programing language editor extensions based on code-mirror                              | ~1.6          |
| [star-rating](https://github.com/laravel-admin-extensions/star-rating) | Star Rating extension for laravel-admin                                                         | ~1.6          |
| [json-editor](https://github.com/laravel-admin-extensions/json-editor) | JSON Editor for Laravel-admin                                                                   | ~1.6          |
| [grid-lightbox](https://github.com/laravel-admin-extensions/grid-lightbox) | Turn your grid into a lightbox & gallery                                                        | ~1.6          |
| [daterangepicker](https://github.com/laravel-admin-extensions/daterangepicker) | Integrates daterangepicker into laravel-admin                                                   | ~1.6          |
| [material-ui](https://github.com/laravel-admin-extensions/material-ui) | Material-UI extension for laravel-admin                                                         | ~1.6          |
| [sparkline](https://github.com/laravel-admin-extensions/sparkline) | Integrates jQuery sparkline into laravel-admin                                                  | ~1.6          |
| [chartjs](https://github.com/laravel-admin-extensions/chartjs) | Use Chartjs in laravel-admin                                                                    | ~1.6          |
| [echarts](https://github.com/laravel-admin-extensions/echarts) | Use Echarts in laravel-admin                                                                    | ~1.6          |
| [simditor](https://github.com/laravel-admin-extensions/simditor) | Integrates simditor full-rich editor into laravel-admin                                         | ~1.6          |
| [cropper](https://github.com/laravel-admin-extensions/cropper) | A simple jQuery image cropping plugin.                                                          | ~1.6          |
| [composer-viewer](https://github.com/laravel-admin-extensions/composer-viewer) | A web interface of composer packages in laravel.                                                | ~1.6          |
| [data-table](https://github.com/laravel-admin-extensions/data-table) | Advanced table widget for laravel-admin                                                         | ~1.6          |
| [watermark](https://github.com/laravel-admin-extensions/watermark) | Text watermark for laravel-admin                                                                | ~1.6          |
| [google-authenticator](https://github.com/ylic/laravel-admin-google-authenticator) | Google authenticator                                                                            | ~1.6          |


### Extensions reworked by Open-Admin for Bootstrap 5.3

| Extension                                                        | Description                              | open-admin                              |
|------------------------------------------------------------------| ---------------------------------------- |---------------------------------------- |
| [helpers](https://github.com/dedermus/helpers)                   | Several tools to help you in development | ~1.0 |
| [media-manager](https://github.com/dedermus/media-manager)       | Provides a web interface to manage local files          | ~1.0 |
| [config](https://github.com/dedermus/config)                     | Config manager for open-admin            |~1.0 |
| [grid-sortable](https://github.com/dedermus/grid-sortable)       | Sortable grids                           |~1.0 |
| [Ckeditor](https://github.com/open-admin-org/ckeditor)           | Ckeditor for forms                       |~1.0 |
| [api-tester](https://github.com/dedermus/api-tester)             | Test api calls from the admin            |~1.0 |
| [scheduling](https://github.com/dedermus/scheduling)             | Show and test your cronjobs              |~1.0 |
| [phpinfo](https://github.com/open-admin-org/phpinfo)             | Show php info in the admin               |~1.0 |
| [log-viewer](https://github.com/dedermus/log-viewer)             | Log viewer for laravel                   |~1.0.12 |
| [page-designer](https://github.com/open-admin-org/page-designer) | Page designer to position items freely   |~1.0.18 |
| [reporter](https://github.com/open-admin-org/reporter)           | Provides a developer-friendly web interface to view the exception    |~1.0.18 |
| [redis-manager](https://github.com/open-admin-org/redis-manager) | Redis manager for open-admin             |~1.0.20 |


<!--
| [backup](https://github.com/open-admin-extensions/backup) | An admin interface for managing backups          |~1.5 |
| [wangEditor](https://github.com/open-admin-extensions/wangEditor) | A rich text editor based on [wangeditor](http://www.wangeditor.com/)         |~1.6 |
| [summernote](https://github.com/open-admin-extensions/summernote) | A rich text editor based on [summernote](https://summernote.org/)          |~1.6 |
| [simplemde](https://github.com/open-admin-extensions/simplemde) | A markdown editor based on [simplemde](https://github.com/sparksuite/simplemde-markdown-editor)          |~1.6 |
| [php-editor](https://github.com/open-admin-extensions/php-editor) <br/> [python-editor](https://github.com/open-admin-extensions/python-editor) <br/> [js-editor](https://github.com/open-admin-extensions/js-editor)<br/> [css-editor](https://github.com/open-admin-extensions/css-editor)<br/> [clike-editor](https://github.com/open-admin-extensions/clike-editor)| Several programing language editor extensions based on code-mirror          |~1.6 |
| [json-editor](https://github.com/open-admin-extensions/json-editor) | JSON Editor for Open-admin          |~1.6 |
| [composer-viewer](https://github.com/open-admin-extensions/composer-viewer) | A web interface of composer packages in laravel.          |~1.6 |
| [data-table](https://github.com/open-admin-extensions/data-table) | Advanced table widget for open-admin |~1.6 |
| [watermark](https://github.com/open-admin-extensions/watermark) | Text watermark for open-admin |~1.6 |
| [google-authenticator](https://github.com/ylic/open-admin-google-authenticator) | Google authenticator |~1.6 |
-->


## Credits

This project exists thanks to everyone who contributes. [[Contribute](CONTRIBUTING.md)]

`exment-admin-core` builds on the work of:

+ [laravel-admin](https://github.com/z-song/laravel-admin) by z-song
+ [open-admin](https://github.com/open-admin-org/open-admin)
+ [open-admin-core](https://github.com/dedermus/open-admin-core) by dedermus

Other
------------
`exment-admin-core` is based on the following plugins or services:

+ [Laravel](https://laravel.com/)
+ [AdminLTE](https://adminlte.io/)
+ [Datetimepicker](http://eonasdan.github.io/bootstrap-datetimepicker/)
+ [font-awesome](http://fontawesome.io)
+ [moment](http://momentjs.com/)
+ [Google map](https://www.google.com/maps)
+ [Tencent map](http://lbs.qq.com/)
+ [bootstrap-fileinput](https://github.com/kartik-v/bootstrap-fileinput)
+ [jquery-pjax](https://github.com/defunkt/jquery-pjax)
+ [Nestable](http://dbushell.github.io/Nestable/)
+ [toastr](http://codeseven.github.io/toastr/)
+ [X-editable](http://github.com/vitalets/x-editable)
+ [bootstrap-number-input](https://github.com/wpic/bootstrap-number-input)
+ [fontawesome-iconpicker](https://github.com/itsjavi/fontawesome-iconpicker)
+ [sweetalert2](https://github.com/sweetalert2/sweetalert2)

License
------------
`exment-admin-core` is licensed under [The MIT License (MIT)](LICENSE).
