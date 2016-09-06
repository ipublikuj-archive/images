# Quickstart

This extension brings you ability to store images in your application and serve them on-demand to your visitors.

## Concept

Basic concept of this extensions is to use independent data storage based on [Flysystem extension](https://github.com/iPublikuj/flysystem). For example for each module or part of your application. Sou you can have e-shop images storage for 
e-shop module, or users avatars storage to store users avatars.

Second important think is, all storage services can store files outside of document root folder even on some cloud servers like AWS or own cloud.

## Installation

The best way to install ipub/images is using  [Composer](http://getcomposer.org/):

```sh
$ composer require ipub/images
```

After that you have to register extension in config.neon.

```neon
extensions:
	images: IPub\Images\DI\ImagesExtension
```

## Usage

### Basic configuration

At first you have to register your storage services in [Flysystem extension](https://github.com/iPublikuj/flysystem/blob/master/docs/en/index.md#quickstart).

### Providers

This extension come with default presenter provider which is registered automatically. If you want to use this provider, you have to specify at least one route and public web directory:

```neon
images:
	routes:
		- "/images[/<namespace .+>]/<size>[-<algorithm>]/<filename>.<extension>"
	wwwDir: path/to/document/root
	presenterProvider: true # Default value is true, if you want to disable set it to false
```

Required parameters for each route are:

* **namespace**: it is used for folders and sub-folders
* **size**: it define output image size
* **filename**: stripped filename without extension
* **extension**: filename extension

Routes can be defined with additional params like in Nette:

```neon
images:
	routes:
		"/images[/<namespace .+>]/<size>[-<algorithm>]/<filename>.<extension>"  :
			defaultParam : defaultValue
			otherParam : otherValue
```

Second mandatory parameter is **wwwDir**. With this parameter you have to specify absolute path to your document root folder where will be saved generated images. 

> By default all these routes will be prepended before your other routes - assuming you use `Nette\Application\Routers\RouteList` as your root router. You can disable this by setting `prependRoutesToRouter: false`. Then it's your responsibility to plug extension router (service `images.router`) to your routing implementation.

### Using in Latte

This extension gives you new latte macro **n:src**. Now you're ready to use it.

```html
<a n:src="providerName:storageName://products/filename.jpg"><img n:src="providerName:storageName://products/filename.jpg, 200x200, fill" /></a>
```

output:

```html
<a href="/images/products/original/filename.jpg"><img src="/images/products/200x200-fill/filename.jpg" /></a>
```

Parameters of this macro are:

* **path** - full path to the image with storage name and images provider eg.: *presenter:eshopStorage://some/namespace/product-image.jpg*
* **size** - image size. It could be only width or width and height eg.: *150* or *50x50*
* **algorithm** - (optional) resize algorithm which is used to convert image

### Resizing algorithm

For resizing (third argument) you can use these keywords - `fit`, `fill`, `exact`, `stretch`, `shrink_only`. For details see comments above [these constants](http://api.nette.org/2.0/source-common.Image.php.html#105)

## More

- [Read more images providers](https://github.com/iPublikuj/images/blob/master/docs/en/providers.md)
- [Read more about images generation](https://github.com/iPublikuj/images/blob/master/docs/en/generation.md)
