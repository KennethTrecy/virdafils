# VirDaFils (Virtual Database Filesystem)
Virdafils is a file storage driver for [Laravel Framework]. This driver allows the developer to
treat directories/files as records in a database.

## Origin
Some parts of the repository was based from [`plugin`] branch of [Web Template].

## Installation
1. Put the following information in the your `composer.json`:
   ```
   {
      // Your specified properties like name, type, license, etc...

      "require": {
         // other dependencies here...

         "kennethtrecy/virdafils": "^0.4.0"
      },

      // Your other properties like require-dev, autoload, etc...

      // Add the repository to instruct where to find the package
      "repositories": [
         {
            "type": "composer",
            "url": "https://raw.githubusercontent.com/KennethTrecy/PHP_packages/master"
         }
      ],


      "config": {
         // Other configurations here...

         "secure-http": true
      }
   }
   ```
2. Run `composer install`
3. Run `php artisan vendor:publish --provider="\KennethTrecy\Virdafils\VirdafilsServiceProvider"` or
   `php artisan vendor:publish --provider="\\KennethTrecy\\Virdafils\\VirdafilsServiceProvider"`
   depending on your OS.
4. Run `php artisan migrate:fresh`
5. Add the disk configuration to your `config/filesystems.php`:
   ```
   return [
      // other options...

      "disks" => [
         // other disks...

         // Add the disk configuration in the array
         "virdafils" => [
            "driver" => "virdafils",
            "root" => "/",
            "visibility" => "private"
         ]
      ]
      // other options...
   ]
   ```
6. (Optional) Specify the default disk in your `.env`:
   ```
   FILESYSTEM_DRIVER=virdafils
   ```

## Documentation
You can generate the documentation offline using
[phpDocumentor](https://docs.phpdoc.org/guide/getting-started/installing.html).
1. Choose one of the installation options of
   [phpDocumentor](https://docs.phpdoc.org/guide/getting-started/installing.html).
2. Run `git clone git@github.com:KennethTrecy/virdafils.git`.
3. Run `cd virdafils`.
4. Run `php phpDocumentor.phar` or `phpDocumentor`, or other commands depending on your installation
   option.
5. Visit the [hidden_docs/index.html](hidden_docs/index.html) in your preferred browser.

## Notes
This is a newly-created project which may have bugs. If you found one, please file an issue.

Current version of this package is compatible with [Laravel Framework] version 9 and above only.

Use this package's v0.2.1 is compatible with version 8. However, there are may stil be bugs.

PRs are welcome!

## Author
Virdafils was created by Kenneth Trecy Tobias.

[`plugin`]: https://github.com/KennethTrecy/web_template/tree/plugin
[Web Template]: http://github.com/KennethTrecy/web_template
[Laravel Framework]: https://laravel.com
