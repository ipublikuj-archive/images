# Images

Simple image storage and generator for [Nette Framework](http://nette.org/)

## Installation

The best way to install ipub/images is using  [Composer](http://getcomposer.org/):

```json
{
	"require": {
		"ipub/images": "dev-master"
	}
}
```

After that you have to register extension in config.neon.

```neon
extensions:
	images: IPub\Images\DI\ImagesExtension
```

Package contains trait, which you will have to use in class, where you want to access to the storage. This works only for PHP 5.4+, for older version you can simply copy trait content and paste it into class where you want to use it.

```php
<?php

class BasePresenter extends Nette\Application\UI\Presenter
{

	use IPub\Images\TImages;

}
```

## Usage

Basic concept of this extensions is to create several storage places for each module in you application. Sou you can have eshop images storage for eshop module, or users avatars storage to store users avatars.

Second important think is, all storages services can store files outside of document root folder even on some cloud servers like AWS or own cloud.

### Setting up default storage

This extension has default file storage with this configuration:

```neon
images:
	storage:
		default:
			service		: @images.storage.default
			route		: "/images[/<namespace .+>]/<size>[-<algorithm>]/<filename>.<extension>"
			storageDir	: %wwwDir%/media
			rules		: []
```
In **service** section you can define service which is for getting, saving and deleting images
In **route** section you can define your default route for this storage.
The **storageDir** section is for specifying location where original images are stored.
And the **rules** section is for configuring rules of images sizes and alogirthms used to generate images.

### Saving images

In forms or in components or even in presenters

```php

	/**
	 * @inject
	 * @var IPub\Images\ImageLoader
	 */
	public $imagesLoader;


	public function handleUpload(Nette\Http\FileUpload $file)
	{
		$this->imagesLoader
			->getStorage('nameOfYourStorage')
				->upload($fileUpload); // saves to %storageDir%/filename.jpg

		# or

		$this->imagesLoader
			->getStorage('nameOfYourStorage')
				->setNamespace("products")
				->upload($fileUpload); // saves to %storageDir%/products/filename.jpg
	}
```

### Using in Latte

```html
<a href="{src 'products/filename.jpg'}"><img n:src="'filename.jpg', '200x200', 'fill'" /></a>
```

output:

```html
<a href="/images/products/original/filename.jpg"><img n:img="/images/200x200-fill/filename.jpg" /></a>
```

### Resizing flags

For resizing (third argument) you can use these keywords - `fit`, `fill`, `exact`, `stretch`, `shrink_only`. For details see comments above [these constants](http://api.nette.org/2.0/source-common.Image.php.html#105)
