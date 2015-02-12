<?php
/**
 * Generator.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Images!
 * @subpackage	common
 * @since		5.0
 *
 * @date		11.02.15
 */

namespace IPub\Images;

use Nette;
use Nette\Application;
use Nette\Http;
use Nette\Utils;

use Tracy;

use IPub;
use IPub\Images;
use IPub\Images\Storage;
use IPub\Images\Validators;

class Generator extends Nette\Object
{
	/**
	 * @var string
	 */
	private $webDir;

	/**
	 * @var ImagesLoader
	 */
	private $imagesLoader;

	/**
	 * @var Http\IRequest
	 */
	private $httpRequest;

	/**
	 * @var Http\IResponse
	 */
	private $httpResponse;

	/**
	 * @param string $webDir
	 * @param ImagesLoader $imagesLoader
	 * @param Http\IRequest $httpRequest
	 * @param Http\IResponse $httpResponse
	 */
	public function __construct($webDir, ImagesLoader $imagesLoader, Http\IRequest $httpRequest, Http\IResponse $httpResponse)
	{
		$this->webDir = $webDir;
		$this->imagesLoader = $imagesLoader;
		$this->httpRequest = $httpRequest;
		$this->httpResponse = $httpResponse;
	}

	/**
	 * @param string $namespace
	 * @param string $size
	 * @param string $filename
	 * @param string $extension
	 * @param string $algorithm
	 * @param string $storage
	 *
	 * @throws Application\BadRequestException
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function generateImage($namespace, $size, $filename, $extension, $algorithm, $storage)
	{
		$storage = $this->imagesLoader->getStorage($storage);

		$width = $height = 0;
		if (strpos($size, 'x') !== FALSE) {
			list($width, $height) = explode("x", $size);
			$size = (int) $width .'x'. (int) $height;

		} else if ($size != 'original') {
			$width = (int) $size;
		}

		if (!$storage->getValidator()->validate($width, $height, $algorithm)) {
			throw new Application\BadRequestException;
		}

		$image = $storage
					->setNamespace($namespace)
					->get($filename .'.'. $extension);

		if (!$image instanceof Images\Image\Image && !$image instanceof Utils\Image) {
			$this->imageNotFound();
		}

		$destination = $this->webDir . '/' . $this->httpRequest->getUrl()->getPath();
		$dirname = dirname($destination);
		if (!is_dir($dirname)) {
			$success = @mkdir($dirname, 0777, TRUE);
			if (!$success) {
				throw new Application\BadRequestException;
			}
		}

		if ($image instanceof Utils\Image) {
			$success = $image->save($destination, 90);

			if (!$success) {
				throw new Application\BadRequestException;
			}

			$image->send();

		} else if ((string) $image && is_file((string) $image)) {
			try {
				Utils\FileSystem::copy((string) $image, $destination);

				(new Nette\Application\Responses\FileResponse($destination))->send($this->httpRequest, $this->httpResponse);

			} catch (\Exception $e) {
				throw new Application\BadRequestException;
			}

		} else {
			throw new Application\BadRequestException;
		}

		exit;
	}

	/**
	 * return void
	 */
	private function imageNotFound()
	{
		$this->httpResponse->setHeader('Content-Type', 'image/jpeg');
		$this->httpResponse->setCode(Http\IResponse::S404_NOT_FOUND);

		exit;
	}
}