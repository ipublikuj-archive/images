# Images

[![Build Status](https://img.shields.io/travis/iPublikuj/images.svg?style=flat-square)](https://travis-ci.org/iPublikuj/images)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/iPublikuj/images.svg?style=flat-square)](https://scrutinizer-ci.com/g/iPublikuj/images/?branch=master)
[![Latest Stable Version](https://img.shields.io/packagist/v/ipub/images.svg?style=flat-square)](https://packagist.org/packages/ipub/images)
[![Composer Downloads](https://img.shields.io/packagist/dt/ipub/images.svg?style=flat-square)](https://packagist.org/packages/ipub/images)

Image storage & generator for [Nette Framework](http://nette.org/)

## Installation

The best way to install ipub/images is using  [Composer](http://getcomposer.org/):

```json
{
	"require": {
		"ipub/images": "dev-master"
	}
}
```

or

```sh
$ composer require ipub/images:@dev
```

After that you have to register extension in config.neon.

```neon
extensions:
	images: IPub\Images\DI\ImagesExtension
```

## Documentation

Learn how to store and serve images using in [documentation](https://github.com/iPublikuj/images/blob/master/docs/en/index.md).

***
Homepage [http://www.ipublikuj.eu](http://www.ipublikuj.eu) and repository [http://github.com/iPublikuj/images](http://github.com/iPublikuj/images).