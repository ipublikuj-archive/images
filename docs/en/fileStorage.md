#Working with files storage

Package contains trait, which you will have to use in class, where you want to access to the images loader and helpers. This works only for PHP 5.4+, for older version you can simply copy trait content and paste it into class where you want to use it.

```php
<?php

class BasePresenter extends Nette\Application\UI\Presenter
{
	use IPub\Images\TImages;
}
```

## Uploading images

In forms or in components or even in presenters

```php
class YourPresenter extends BasePresenter
{
	use IPub\Images\TImages;

	public function handleUpload(Nette\Http\FileUpload $file)
	{
		$this->imagesLoader
			->getStorage('nameOfYourStorage')
				->upload($file); // saves to %storageDir%/filename.jpg

		# or

		$this->imagesLoader
			->getStorage('nameOfYourStorage')
				->setNamespace("products")
				->upload($fileUpload); // saves to %storageDir%/products/filename.jpg

		# or

		$this->imagesLoader
			->getStorage('nameOfYourStorage')
				->upload($fileUpload, 'products'); // saves to %storageDir%/products/filename.jpg
	}
}
```

## Saving images

Images can be saved into storage even from content.

```php
class YourPresenter extends BasePresenter
{
	use IPub\Images\TImages;

	public function handleSaveFromUrl($url)
	{
		$this->imagesLoader
			->getStorage('nameOfYourStorage')
				->save(file_get_contents($url), 'filename.jpg'); // saves to %storageDir%/filename.jpg

		# or

		$this->imagesLoader
			->getStorage('nameOfYourStorage')
				->setNamespace("products")
				->save(file_get_contents($url), 'filename.jpg'); // saves to %storageDir%/products/filename.jpg

		# or

		$this->imagesLoader
			->getStorage('nameOfYourStorage')
				->save(file_get_contents($url), 'filename.jpg', 'products'); // saves to %storageDir%/products/filename.jpg
	}
}
```

## Deleting images

When you are deleting images, you have to delete them from storage dir and from web dir where this images are cached.

```php
class YourPresenter extends BasePresenter
{
	use IPub\Images\TImages;

	public function handleDelete($imageName)
	{
		$this->imagesLoader
			->getStorage('nameOfYourStorage')
				->delete($imageName);

		# or

		$this->imagesLoader
			->getStorage('nameOfYourStorage')
				->setNamespace("products")
				->delete($imageName);
	}
}
```

This will delete all image files in storage dir and also in web dir in selected namespace

## Creating new namespace

Default file storage has functionality to duplicate itself with defined namespace. It could be useful when you want to define more namespaced storage on startup.

```php
class YourPresenter extends BasePresenter
{
	use IPub\Images\TImages;

	/**
	 * @var IPub\Images\Storage\IStorage
	 */
	protected $eShopStorage;

	/**
	 * @var IPub\Images\Storage\IStorage
	 */
	protected $contentStorage;

	public function startup()
	{
		parent::startup();

		// Get file storage
		$fileStorage = $this->imagesLoader->getStorage('nameOfYourStorage')

		// Create namespace for eShop
		$this->eShopStorage = $fileStorage->createNamespace('eshop');
		
		// Create namespace for articles
		$this->contentStorage = $fileStorage->createNamespace('content');
	}
}
```