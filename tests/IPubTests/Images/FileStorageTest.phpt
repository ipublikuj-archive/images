<?php
/**
 * Test: IPub\Images\FileStorage
 * @testCase
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Images!
 * @subpackage	Tests
 * @since		5.0
 *
 * @date		28.02.15
 */

namespace IPubTests\Images;

use Nette;

use Tester;
use Tester\Assert;

use IPub;
use IPub\Images;

require_once __DIR__ . '/TestCase.php';

class FileStorageTest extends TestCase
{
	public function testRegisteringStorage()
	{
		$storage = $this->container->getService('imagesStorage');

		Assert::true($storage instanceof DefaultImagesStorage);

		$loader = $this->container->getService('images.loader');

		Assert::true($loader instanceof IPub\Images\ImagesLoader);
		Assert::true($loader->getStorage('default') instanceof DefaultImagesStorage);
	}

	public function testDefaultStorage()
	{
		$loader = $this->container->getService('images.loader');
		$storage = $loader->getStorage('default');

		Assert::same(__DIR__ . DIRECTORY_SEPARATOR .'upload', $storage->getStorageDir());

		$storage->setNamespace('testing/namespace');
		Assert::same('testing/namespace', $storage->getNamespace());
		Assert::false($storage->namespaceExists('fake/namespace'));
		Assert::true($storage->namespaceExists('media'));
		Assert::same(__DIR__ . DIRECTORY_SEPARATOR .'upload' . DIRECTORY_SEPARATOR .'media', $storage->getNamespacePath('media'));

		$namespaceStorage = $storage->createNamespace('media');
		Assert::same('media', $namespaceStorage->getNamespace());
	}

	public function testHandleImage()
	{
		// Create test presenter
		$presenter = $this->createPresenter();

		$loader = $this->container->getService('images.loader');
		$storage = $loader->getStorage('default');
		$storage->setPresenter($presenter);

		$file = new Nette\Http\FileUpload([
			'name' => 'ipublikuj-logo-large.png',
			'type' => 'image/png',
			'size' => 8729,
			'tmp_name' => __DIR__ . DIRECTORY_SEPARATOR .'media'. DIRECTORY_SEPARATOR .'ipublikuj-logo-large.png',
			'error' => UPLOAD_ERR_OK,
		]);

		$uploadedImage = $storage->upload($file);

		// Backup back file
		copy($uploadedImage->getFile(), __DIR__ . DIRECTORY_SEPARATOR .'media'. DIRECTORY_SEPARATOR .'ipublikuj-logo-large.png');

		$savedImage = $storage->get($uploadedImage->getName());

		Assert::true($uploadedImage instanceof Images\Image\Image);
		Assert::true($savedImage instanceof Images\Image\Image);
		Assert::same(realpath((string) $uploadedImage), realpath((string) $savedImage));

		$savedImage = $storage->save(file_get_contents($uploadedImage->getFile()), 'save-'. $uploadedImage->getName());

		Assert::true($savedImage instanceof Images\Image\Image);
		Assert::same('save-'. $uploadedImage->getName(), $savedImage->getName());

		$url = $storage->request($uploadedImage->getFile(), '50x50');
		Assert::same('/images/50x50/'. $uploadedImage->getName() .'?storage='. (string) $storage, $url);
		$url = $storage->request($uploadedImage->getFile(), '120x120');
		Assert::same('/images/120x120/'. $uploadedImage->getName() .'?storage='. (string) $storage, $url);
		$url = $storage->request($uploadedImage->getFile(), '50x50', 'fit');
		Assert::same('/images/50x50-fit/'. $uploadedImage->getName() .'?storage='. (string) $storage, $url);

		// Backup back file
		copy($uploadedImage->getFile(), __DIR__ . DIRECTORY_SEPARATOR .'media'. DIRECTORY_SEPARATOR .'ipublikuj-logo-large.png');

		$storage->delete($uploadedImage->getName());
		$storage->delete($savedImage->getName());

		Assert::same(NULL, $storage->get($uploadedImage->getName()));
		Assert::same(NULL, $storage->get($savedImage->getName()));
	}
}

\run(new FileStorageTest());