# Images providers

If you want to create your own custom images providers, you have to create service which will implement `\IPub\Images\Providers\IProvider`.
Images extension will search for services implementing this interface and register them into the loader.

Your custom provider must implement two methods `getName` and `request`

```php
namespace MyApplication\Services;

use IPub\Images\Providers\IProvider;

class MyOwnImagesProvider implements IProvider
{
    /**
     * @var string
     */
    private $webPath;

    /**
     * @var SomeModel
     */
    private $someModel;

    public function __construct(SomeModel $someModel, $webPath)
    {
        $this->someModel = $someModel;
        $this->webPath = $webPath;
    }

    public function getName()
    {
        return 'myName';
    }
    
    public function request(string $storage, string $namespace = NULL, string $filename, string $size = NULL, string $algorithm = NULL) : string
    {
        return $this->webPath . '/' . $this->someModel->findImage($filename, $namespace);
    }
}
```

Method `getName` should return string without whitespaces, special chars etc. because this name is used in template helpers and other request for images path generation.

## More

- [Read more about images generation](https://github.com/iPublikuj/images/blob/master/docs/en/generation.md)
- [Read abou basic implementation](https://github.com/iPublikuj/images/blob/master/docs/en/index.md)
