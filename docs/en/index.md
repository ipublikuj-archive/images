
Package contains trait, which you will have to use in class, where you want to access to the images loader and helpers. This works only for PHP 5.4+, for older version you can simply copy trait content and paste it into class where you want to use it.

```php
<?php

class BasePresenter extends Nette\Application\UI\Presenter
{

	use IPub\Images\TImages;

}
```


## Usage

Basic concept of this extensions is to create several storage places for each module in you application. Sou you can have e-shop images storage for e-shop module, or users avatars storage to store users avatars.

Second important think is, all storage services can store files outside of document root folder even on some cloud servers like AWS or own cloud.

### Basic setting up

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

### Setting up default storage

This extension has default file storage with this configuration:

```neon
images:
	storage:
		default:
			class		: IPub\Images\Storage\DefaultStorage
			defaults	: 
				storageDir : %wwwDir%/media
			rules		: []
```

In **service** section you can define service which is for getting, saving and deleting images
In **class** section you can define class which will be used for creating service. If this section is empty, you have to fill in **service** section
The **defaults** section you have to define all default and required arguments for creating service by class
And the **rules** section is for configuring rules of images sizes and algorithms used to generate images.

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
<a n:src="'products/filename.jpg'"><img n:src="'filename.jpg', '200x200', 'fill'" /></a>
```

output:

```html
<a href="/images/products/original/filename.jpg"><img n:img="/images/200x200-fill/filename.jpg" /></a>
```

### Resizing flags

For resizing (third argument) you can use these keywords - `fit`, `fill`, `exact`, `stretch`, `shrink_only`. For details see comments above [these constants](http://api.nette.org/2.0/source-common.Image.php.html#105)
