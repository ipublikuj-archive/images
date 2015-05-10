# Quickstart

This extension brings you ability to upload and store images in your application and serve them on-demand to your visitors.

## Concept

Basic concept of this extensions is to create several storage places for each module in you application. Sou you can have e-shop images storage for e-shop module, or users avatars storage to store users avatars.

Second important think is, all storage services can store files outside of document root folder even on some cloud servers like AWS or own cloud.

## Installation

The best way to install ipub/flickr is using  [Composer](http://getcomposer.org/):

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

## Usage

### Basic configuration

At first you have to configure extension route:

```neon
images:
	routes:
		- "/images[/<namespace .+>]/<size>[-<algorithm>]/<filename>.<extension>"
	wwwDir: path/to/document/root
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

So you can define for example secured route as default or other params.

Second mandatory parameter is **wwwDir**. With this parameter you have to specify absolute path to your document root folder where will be saved generated images. 

> By default all these routes will be prepended before your other routes - assuming you use `Nette\Application\Routers\RouteList` as your root router. You can disable this by setting `prependRoutesToRouter: false`. Then it's your responsibility to plug extension router (service `images.router`) to your routing implementation.

### Setting up default storage

This extension has default abstract file storage and you have to create service for this storage:

```php
class YourFileStorage extends \IPub\Images\Storage\FileStorage
{
	public function __toString()
	{
		return 'storageName'; // Here you have to define your storage name. This name have to be unique
	}
}
```

And now you can register this new storage into services:

```neon
services
	myImagesStorage:
		class: Your\Namespace\YourFileStorage
		arguments: [%storageDir%, %wwwDir%]
```

This default file storage has two mandatory parameters:

* **storageDir** - absolute path to your storage directory where will be stored original files
* **wwwDir** - absolute path to you document root folder or some sub-folder which is in document root 

So when your images storage is defined, configured and registered as service, you have to register this storage into extension:

```neon
images:
	storage: [default: @myImagesStorage]
```

You can register as manny storage as you need.

### Namespaces

Namespaces can be understand as virtual folders, so you can split your images into folders. Eg. if you want to use this extension for e-shop products images and as namespace can be used product name or category.

### Using in Latte

This extension gives you new latte macro **n:src**. Now you're ready to use it.

```html
<a n:src="'storageName:://products/filename.jpg'"><img n:src="'storageName:://products/filename.jpg', '200x200', 'fill'" /></a>
```

output:

```html
<a href="/images/products/original/filename.jpg"><img src="/images/products/200x200-fill/filename.jpg" /></a>
```

Parameters of this macro are:

* **path** - full path to the image with storage name eg.: *eshopStorage://some/namespace/product-image.jpg*
* **size** - image size. It could be only width or width and height eg.: *150* or *50x50*
* **algorithm** - (optional) resize algorithm which is used to convert image

### Resizing algorithm

For resizing (third argument) you can use these keywords - `fit`, `fill`, `exact`, `stretch`, `shrink_only`. For details see comments above [these constants](http://api.nette.org/2.0/source-common.Image.php.html#105)
