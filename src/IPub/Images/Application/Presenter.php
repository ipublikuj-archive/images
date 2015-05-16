<?php
/**
 * Presenter.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Images!
 * @subpackage	Application
 * @since		5.0
 *
 * @date		09.02.15
 */

namespace IPub\IPubModule;

use Nette;
use Nette\Application;
use Nette\Http;
use Nette\Utils;

use IPub;
use IPub\Images;
use IPub\Images\Exceptions;

class ImagesPresenter extends Nette\Object implements Application\IPresenter
{
	/**
	 * @var Http\IRequest
	 */
	private $httpRequest;

	/**
	 * @var Http\IResponse
	 */
	private $httpResponse;

	/**
	 * @var Application\IRouter
	 */
	private $router;

	/**
	 * @var Application\Request
	 */
	private $request;

	/**
	 * @var Images\Generator
	 */
	private $imagesLoader;

	/**
	 * @var string
	 */
	private $webDir;

	/**
	 * @param string $webDir
	 * @param Images\ImagesLoader $imagesLoader
	 * @param Http\IRequest $httpRequest
	 * @param Http\IResponse $httpResponse
	 * @param Application\IRouter $router
	 */
	public function __construct(
		$webDir,
		Images\ImagesLoader $imagesLoader,
		Http\IRequest $httpRequest = NULL,
		Http\IResponse $httpResponse,
		Application\IRouter $router = NULL
	) {
		$this->webDir = $webDir;

		$this->imagesLoader = $imagesLoader;
		$this->httpRequest  = $httpRequest;
		$this->httpResponse = $httpResponse;
		$this->router       = $router;
	}

	/**
	 * @param Application\Request $request
	 *
	 * @return Application\IResponse
	 *
	 * @throws Application\BadRequestException
	 */
	public function run(Application\Request $request)
	{
		$this->request = $request;

		if ($this->httpRequest && $this->router && !$this->httpRequest->isAjax() && ($request->isMethod('get') || $request->isMethod('head'))) {
			$refUrl = clone $this->httpRequest->getUrl();

			$url = $this->router->constructUrl($request, $refUrl->setPath($refUrl->getScriptPath()));

			if ($url !== NULL && !$this->httpRequest->getUrl()->isEqual($url)) {
				return new Application\Responses\RedirectResponse($url, Http\IResponse::S301_MOVED_PERMANENTLY);
			}
		}

		$params = $request->getParameters();

		if (!isset($params['storage'])) {
			throw new Application\BadRequestException('Parameter storage is missing.');

		} else {
			$storage = $params['storage'];
		}

		if (!isset($params['filename'])) {
			throw new Application\BadRequestException('Parameter filename is missing.');

		} else {
			$filename = $params['filename'];
		}

		if (!isset($params['extension'])) {
			throw new Application\BadRequestException('Parameter extension is missing.');

		} else {
			$extension = $params['extension'];
		}

		$namespace = isset($params['namespace']) ? $params['namespace'] : NULL;
		$size = isset($params['size']) ? $params['size'] : NULL;
		$algorithm = isset($params['algorithm']) ? $params['algorithm'] : NULL;

		$this->generateImage($namespace, $filename, $extension, $size, $algorithm, $storage);
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
	private function generateImage($namespace, $filename, $extension, $size, $algorithm, $storage)
	{
		$storage = $this->imagesLoader->getStorage($storage);

		// Extract size
		$width = $height = 0;
		$size = Utils\Strings::lower($size);
		if (strpos($size, 'x') !== FALSE) {
			list($width, $height) = explode("x", $size);

		} else if ($size != 'original') {
			$width = (int) $size;

		} else if ($size == 'original') {
			$width = $height = NULL;
		}

		// Extract algorithm
		if ($algorithm == NULL) {
			$algorithm = Utils\Image::FIT;

		} else if (!is_int($algorithm) && !is_array($algorithm)) {
			switch (strtolower($algorithm))
			{
				case "fit":
					$algorithm = Utils\Image::FIT;
					break;

				case "fill":
					$algorithm = Utils\Image::FILL;
					break;

				case "exact":
					$algorithm = Utils\Image::EXACT;
					break;

				case "shrink_only":
				case "shrinkonly":
				case "shrink-only":
					$algorithm = Utils\Image::SHRINK_ONLY;
					break;

				case "stretch":
					$algorithm = Utils\Image::STRETCH;
					break;

				default:
					$algorithm = ctype_digit($algorithm) ? (int) $algorithm : NULL;
			}

		} else {
			$algorithm = NULL;
		}

		// Validate params
		if (!$storage->getValidator()->validate($width, $height, $algorithm)) {
			throw new Application\BadRequestException;
		}

		$image = $storage
			->setNamespace($namespace)
			->get($filename .'.'. $extension);

		if (!$image instanceof Images\Image\Image && !$image instanceof Utils\Image) {
			$this->httpResponse->setHeader('Content-Type', 'image/jpeg');
			$this->httpResponse->setCode(Http\IResponse::S404_NOT_FOUND);

			exit;
		}

		$destination = $this->webDir . '/' . $this->httpRequest->getUrl()->getPath();
		$dirname = dirname($destination);
		if (!is_dir($dirname) && !$success = @mkdir($dirname, 0777, TRUE)) {
			throw new Application\BadRequestException;
		}

		if ($image instanceof Images\Image\Image) {
			$mime = Images\Files\MimeMapper::getMimeFromFilename($image->getFile());
			// Check if file is allowed image type
			if (in_array($mime, ['image/jpeg', 'image/png', 'image/gif'])) {
				// ...& create image object
				$image = Utils\Image::fromFile($image->getFile());
			}
		}

		if ($image instanceof Utils\Image) {
			// Process image resizing etc.
			$image->resize($width, $height, $algorithm);
			// Save into new place
			$success = $image->save($destination, 90);

			if (!$success) {
				throw new Application\BadRequestException;
			}

			$image->send();

		} else if ((string) $image && is_file((string) $image)) {
			try {
				Utils\FileSystem::copy((string) $image, $destination);

				(new Images\Application\ImageResponse($destination))->send($this->httpRequest, $this->httpResponse);

			} catch (\Exception $e) {
				throw new Application\BadRequestException;
			}

		} else {
			throw new Application\BadRequestException;
		}

		exit;
	}
}
