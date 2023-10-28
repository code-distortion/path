# Path

[![Latest Version on Packagist](https://img.shields.io/packagist/v/code-distortion/path.svg?style=flat-square)](https://packagist.org/packages/code-distortion/path)
![PHP Version](https://img.shields.io/badge/PHP-8.0%20to%208.3-blue?style=flat-square)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/code-distortion/path/run-tests.yml?branch=main&style=flat-square)](https://github.com/code-distortion/path/actions)
[![Buy The World a Tree](https://img.shields.io/badge/treeware-%F0%9F%8C%B3-lightgreen?style=flat-square)](https://plant.treeware.earth/code-distortion/path)
[![Contributor Covenant](https://img.shields.io/badge/contributor%20covenant-v2.1%20adopted-ff69b4.svg?style=flat-square)](.github/CODE_OF_CONDUCT.md)

***code-distortion/path*** is a package that lets you deal with paths in a normalised way.



## Installation

Install the package via composer:

``` bash
composer require code-distortion/path
```



## Usage

```php
Use CodeDistortion\Path\Path;

$path = Path::new('/path/to/file.txt');

(string) $path;             // '/path/to/file.txt' (castable to a string)
$path->getDir();            // '/path/to/' (the dir as a new Path object)
$path->getFilename();       // 'file.txt'
$path->getFilename(false);  // 'file'
$path->getExtension();      // '.txt'
$path->getExtension(false); // 'txt'
$path->isAbsolute();        // true
$path->isRelative();        // false
```

> ***Note:*** This package is designed to work with paths as strings in *memory*.
> 
> It ***doesn't deal with actual directories or files*** in any way. It doesn't care what's in the filesystem.

> ***Note:*** This package takes the opinion that paths for directories end with a trailing slash, and paths for files do not.

If you want to make sure the input you're dealing with starts off specifically as a directory, or a file, you can specify this when creating the object.

```php
// dir paths
Path::new('/path/to/thing/');   // '/path/to/thing/'
Path::newDir('/path/to/thing'); // '/path/to/thing/' (enforces that it's a dir)
// file paths
Path::new('/path/to/thing');      // '/path/to/thing'
Path::newFile('/path/to/thing/'); // '/path/to/thing' (enforces that it's a file)
```

You can remove unnecessary parts from a path.

```php
Path::new('/a//b/.././c')->resolve(); // '/a/c' - removes unnecessary parts
```

You can add paths together, using one as the base.

```php
Path::newDir('/path/to/uploads/')->add('my-file.txt');           // '/path/to/uploads/my-file.txt'
// make sure that your file doesn't break out from the base
Path::newDir('/path/to/uploads/')->add('../my-file.txt');        // '/path/to/uploads/my-file.txt'
// or allow it to break out
Path::newDir('/path/to/uploads/')->add('../my-file.txt', false); // '/path/to/my-file.txt'
```

Path will normalise forward and backslashes in the input, generating output in your native OS format (but the output separator can be overridden).

```php
Path::newDir('\\path\\to\\file.txt');               // '/path/to/file.txt' (on a *nix OS)
Path::newDir('/path/to/file.txt');                  // '/path/to/file.txt' (on a *nix OS)
Path::newDir('/path/to/file.txt')->separator('\\'); // '\path\to\file.txt'
```

You can clone a Path object.

```php
$pathA = Path::new('/path/to/file.txt');
$pathB = $pathA->copy();
$pathB !== $pathA; // true
```

The `PathImmutable` class is also available. Each change to a `PathImmutable` object returns a new instance. Both `Path` and `PathImmutable` implement `CodeDistortion\Path\PathInterface`. 

```php
$pathA = PathImmutable::newDir('/path/to/');
$pathB = $pathA->add('file.txt');
$pathA !== $pathB; // true
```



## Testing This Package

- Clone this package: `git clone https://github.com/code-distortion/path.git .`
- Run `composer install` to install dependencies
- Run the tests: `composer test`



## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.



### SemVer

This library uses [SemVer 2.0.0](https://semver.org/) versioning. This means that changes to `X` indicate a breaking change: `0.0.X`, `0.X.y`, `X.y.z`. When this library changes to version 1.0.0, 2.0.0 and so forth, it doesn't indicate that it's necessarily a notable release, it simply indicates that the changes were breaking.



## Treeware

This package is [Treeware](https://treeware.earth). If you use it in production, then we ask that you [**buy the world a tree**](https://plant.treeware.earth/code-distortion/path) to thank us for our work. By contributing to the Treeware forest you’ll be creating employment for local families and restoring wildlife habitats.



## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.



### Code of Conduct

Please see [CODE_OF_CONDUCT](.github/CODE_OF_CONDUCT.md) for details.



### Security

If you discover any security related issues, please email tim@code-distortion.net instead of using the issue tracker.



## Credits

- [Tim Chandler](https://github.com/code-distortion)



## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
