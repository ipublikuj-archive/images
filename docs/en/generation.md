# Generation images url

Image url can be generated automatically by Latte macros in templates. But in some cases you want to generate image url in presenters or components. For this cases every storage service must have generator method.

```php
class YourPresenter extends BasePresenter
{
	public function handleUpload(Nette\Http\FileUpload $file)
	{
		// Get images storage
		$storage = $this->imagesLoader->getStorage('nameOfYourStorage');

		// Upload image to storage
		$image = $storage->upload($file);

		// Generate image url and put it into AJAX response
		$this->payload->image = $storage->request($image->getFile(), '800x600', \Nette\Utils\Image::FIT);
	}
}
```

In this step image is not generated yet, only link is generated. Image will be generated on first request by client.

## Generating responsive images

For this you need use some external JS library, eg. [jQuery Picture](http://jquerypicture.com/)

```html
<a n:src="'imageStorage://namespace/image.jpg'">
    <picture data-settings="[]">
        <source n:src="'imageStorage://namespace/image.jpg', '768x'" >
        <source n:src="'imageStorage://namespace/image.jpg', '1200x'" media="(min-width: 768px)">
        <source n:src="'imageStorage://namespace/image.jpg'" media="(min-width: 768px)">
        <noscript>
            <img n:src="'imageStorage://namespace/image.jpg'">
        </noscript>
    </picture>
</a>
```