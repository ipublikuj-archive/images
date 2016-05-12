<?php
/**
 * Presenter.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Images!
 * @subpackage     Application
 * @since          1.0.0
 *
 * @date           09.02.15
 */

namespace IPub\IPubModule;

use Nette;
use Nette\Application;
use Nette\Http;
use Nette\Utils;

use IPub;
use IPub\Images;
use IPub\Images\Exceptions;
use IPub\Images\Validators;

use League\Flysystem;

/**
 * Micro-module presenter for handling images requests
 *
 * @package        iPublikuj:Images!
 * @subpackage     Application
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class ImagesPresenter extends Nette\Object implements Application\IPresenter
{
	/**
	 * Define class name
	 */
	const CLASS_NAME = __CLASS__;

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
	 * @var Images\ImagesLoader
	 */
	private $imagesLoader;

	/**
	 * @var Validators\Validator
	 */
	private $validator;

	/**
	 * @var Flysystem\MountManager
	 */
	private $mountManager;

	/**
	 * @var string
	 */
	private $webDir;

	/**
	 * @param string $webDir
	 * @param Images\ImagesLoader $imagesLoader
	 * @param Validators\Validator $validator
	 * @param Flysystem\MountManager $mountManager
	 * @param Http\IRequest|NULL $httpRequest
	 * @param Http\IResponse $httpResponse
	 * @param Application\IRouter|NULL $router
	 */
	public function __construct(
		$webDir,
		Images\ImagesLoader $imagesLoader,
		Validators\Validator $validator,
		Flysystem\MountManager $mountManager,
		Http\IRequest $httpRequest = NULL,
		Http\IResponse $httpResponse,
		Application\IRouter $router = NULL
	) {
		$this->webDir = $webDir;

		$this->imagesLoader = $imagesLoader;
		$this->validator = $validator;
		$this->mountManager = $mountManager;
		$this->httpRequest = $httpRequest;
		$this->httpResponse = $httpResponse;
		$this->router = $router;
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

		$this->generateImage($storage, $namespace, $filename, $extension, $size, $algorithm);
	}

	/**
	 * @param string $storage
	 * @param string $namespace
	 * @param string $size
	 * @param string $filename
	 * @param string $extension
	 * @param string $algorithm
	 *
	 * @throws Application\BadRequestException
	 * @throws Exceptions\InvalidArgumentException
	 */
	private function generateImage($storage, $namespace, $filename, $extension, $size, $algorithm)
	{
		try {
			$fileSystem = $this->mountManager->getFilesystem($storage);

			$width = $height = 0;

			$size = Utils\Strings::lower($size);

			// Extract size
			if (strpos($size, 'x') !== FALSE) {
				list($width, $height) = explode('x', $size);

			} elseif ($size !== 'original') {
				$width = (int) $size;

			} elseif ($size === 'original') {
				$width = $height = NULL;
			}

			// Extract algorithm
			if ($algorithm === NULL) {
				$algorithm = Utils\Image::FIT;
			}

			// Validate params
			if (!$this->validator->validate($width, $height, $algorithm, $storage)) {
				throw new Application\BadRequestException;
			}

			try {
				$file = $namespace . DIRECTORY_SEPARATOR . $filename . '.' . $extension;

				$image = $fileSystem->read($file);

				$destination = $this->webDir . $this->httpRequest->getUrl()->getPath();

				$dirname = dirname($destination);

				if (!is_dir($dirname) && !$success = @mkdir($dirname, 0777, TRUE)) {
					throw new Application\BadRequestException;
				}

				$mimeType = $fileSystem->getMimetype($file);

				// Check if file is allowed image type
				if (in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'])) {
					// ...& create image object
					$image = Utils\Image::fromString($image);
				}

				if ($image instanceof Utils\Image) {
					// Process image resizing etc.
					if ($width || $height) {
						$image->resize($width, $height, $algorithm);
					}

					// Save into new place
					$success = $image->save($destination, 90);

					if (!$success) {
						throw new Application\BadRequestException;
					}

					$image->send();

				} else {
					try {
						Utils\FileSystem::write($destination, $image);

						(new Images\Application\ImageResponse($destination, ($mimeType ? $mimeType : NULL)))->send($this->httpRequest, $this->httpResponse);

					} catch (\Exception $ex) {
						throw new Application\BadRequestException;
					}
				}

			} catch (Flysystem\FileNotFoundException $ex) {
				throw new Application\BadRequestException;
			}

		} catch (\LogicException $ex) {
			throw new Application\BadRequestException;
		}
	}
}
