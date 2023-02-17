[![Web Front-end Tests](https://img.shields.io/github/actions/workflow/status/KennethTrecy/virdafils/back-end.yml?style=for-the-badge)](https://github.com/KennethTrecy/virdafils/actions/workflows/back-end.yml)
![GitHub lines](https://img.shields.io/github/license/KennethTrecy/virdafils?style=for-the-badge)
![GitHub release (latest SemVer)](https://img.shields.io/github/v/release/KennethTrecy/virdafils?style=for-the-badge&display_name=tag&sort=semver)
![GitHub closed issues count](https://img.shields.io/github/issues-closed/KennethTrecy/virdafils?style=for-the-badge)
![GitHub pull request count](https://img.shields.io/github/issues-pr-closed/KennethTrecy/virdafils?style=for-the-badge)
![Commits since latest version](https://img.shields.io/github/commits-since/KennethTrecy/virdafils/latest?style=for-the-badge)
![Lines of code](https://img.shields.io/tokei/lines/github/KennethTrecy/virdafils?style=for-the-badge)
![GitHub code size in bytes](https://img.shields.io/github/repo-size/KennethTrecy/virdafils?style=for-the-badge)

# VirDaFils (Virtual Database Filesystem)
Virdafils is a file storage driver for [Laravel Framework]. This driver allows the developer to
treat directories/files as records in a database.

## Origin
Some parts of the repository was based from [`plugin`] branch of [Web Template].

## Usage

### Installation
1. Put the following information in your `composer.json`:
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

### Documentation
You can generate the documentation offline using
[phpDocumentor](https://docs.phpdoc.org/guide/getting-started/installing.html).
1. Choose one of the installation options of
   [phpDocumentor](https://docs.phpdoc.org/guide/getting-started/installing.html).
2. Run `git clone git@github.com:KennethTrecy/virdafils.git`.
3. Run `cd virdafils`.
4. Run `php phpDocumentor.phar` or `phpDocumentor`, or other commands depending on your installation
   option.
5. Visit the [hidden_docs/index.html](hidden_docs/index.html) in your preferred browser.

### Initialization
If you want to contribute, the repository should be initialized to adhere in [Conventional Commits
specification] for organize commits and automated generation of change log.

#### Prerequisites
- [Node.js and NPM]
- [pnpm] (optional)

#### Instructions
By running the command below, all your commits will be linted to follow the [Conventional Commits
specification].
```
$ npm install
```

Or if you have installed [pnpm], run the following command:
```
$ pnpm install
```

To generate the change log automatically, run the command below:
```
$ npx changelogen --from=[tag name or branch name or commit itself] --to=master
```

## Notes
If you found a bug, please file an issue.

Current version of this package is compatible with [Laravel Framework] version 9 and above only.

Use this package's v0.2.1 is compatible with version 8. However, there are may still be bugs.

PRs are welcome!

### License
The repository is licensed under [MIT].

### Want to contribute?
Read the [contributing guide] for different ways to contribute in the project.

## Author
Virdafils was created by Kenneth Trecy Tobias.

[`plugin`]: https://github.com/KennethTrecy/web_template/tree/plugin
[Web Template]: http://github.com/KennethTrecy/web_template
[Laravel Framework]: https://laravel.com
[MIT]: https://github.com/KennethTrecy/web_template/blob/master/LICENSE
[Node.js and NPM]: https://nodejs.org/en/
[pnpm]: https://pnpm.io/installation
[Conventional Commits specification]: https://www.conventionalcommits.org/en/v1.0.0/
[contributing guide]: ./CONTRIBUTING.md
