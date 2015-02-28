#Working with files storage

Package contains trait, which you will have to use in class, where you want to access to the images loader and helpers. This works only for PHP 5.4+, for older version you can simply copy trait content and paste it into class where you want to use it.

```php
<?php

class BasePresenter extends Nette\Application\UI\Presenter
{
	use IPub\Images\TImages;
}
```

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