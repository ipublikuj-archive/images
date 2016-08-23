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

declare(strict_types = 1);

namespace IPub\IPubModule;

use Nette;
use Nette\Application;
use Nette\Http;
use Nette\Utils;

use IPub;
use IPub\Images;
use IPub\Images\Exceptions;
use IPub\Images\Helpers;
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
		string $webDir,
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
	public function run(Application\Request $request) : Application\IResponse
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

		// Parse parameters from storage
		$storage = $this->getParameter($params, 'storage', TRUE);
		$filename = $this->getParameter($params, 'filename', TRUE);
		$extension = $this->getParameter($params, 'extension', TRUE);
		$namespace = $this->getParameter($params, 'namespace');
		$size = $this->getParameter($params, 'size');
		$algorithm = $this->getParameter($params, 'algorithm');

		$this->generateImage($storage, $namespace, $filename, $extension, $size, $algorithm);
	}

	/**
	 * @param string $storage
	 * @param string|NULL $namespace
	 * @param string $filename
	 * @param string $extension
	 * @param string|NULL $size
	 * @param string|NULL $algorithm
	 *
	 * @throws Application\BadRequestException
	 */
	private function generateImage(string $storage, string $namespace = NULL, string $filename, string $extension, string $size = NULL, string $algorithm = NULL)
	{
		try {
			$fileSystem = $this->mountManager->getFilesystem($storage);

			list($width, $height) = Helpers\Converters::parseSizeString($size);

			$algorithm = Helpers\Converters::parseAlgorithm($algorithm);

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

				if ($image === FALSE) {
					throw new Application\BadRequestException('Image can\'t be read.');
				}

				$destination = $this->webDir . $this->httpRequest->getUrl()->getPath();

				$dirName = dirname($destination);

				if (!is_dir($dirName) && !$success = @mkdir($dirName, 0777, TRUE)) {
					throw new Application\BadRequestException('Destination web folder is not writable.');
				}

				$mimeType = $fileSystem->getMimetype($file);

				$this->createImage($image, ($mimeType ? $mimeType : NULL), $width, $height, $algorithm);

			} catch (Flysystem\FileNotFoundException $ex) {
				throw new Application\BadRequestException('File not found.');
			}

		} catch (\LogicException $ex) {
			throw new Application\BadRequestException('Storage is not registered in IPub\Flysystem');
		}
	}

	/**
	 * @param string $imageContent
	 * @param string|NULL $mimeType
	 * @param int|NULL $width
	 * @param int|NULL $height
	 * @param int $algorithm
	 *
	 * @throws Application\BadRequestException
	 */
	private function createImage(string $imageContent, string $mimeType = NULL, int $width = NULL, int $height = NULL, int $algorithm)
	{
		$destination = $this->webDir . $this->httpRequest->getUrl()->getPath();

		// Check if file is allowed image type
		if (in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'], TRUE)) {
			// ...& create image object
			$image = Utils\Image::fromString($imageContent);

			// Process image resizing etc.
			if ($width !== NULL || $height !== NULL) {
				$image->resize($width, $height, $algorithm);
			}

			// Save into new place
			$success = $image->save($destination, 90);

			if (!$success) {
				throw new Application\BadRequestException(sprintf('Image can\'t be save into destination web folder: "%s"', $destination));
			}

			$image->send();

		} else {
			try {
				Utils\FileSystem::write($destination, $imageContent);

				(new Images\Application\ImageResponse($destination, $mimeType))->send($this->httpRequest, $this->httpResponse);

			} catch (\Exception $ex) {
				throw new Application\BadRequestException(sprintf('Image can\'t be saved into destination web folder: "%s"', $destination));
			}
		}
	}

	/**
	 * @param array $params
	 * @param string $key
	 * @param bool $required
	 *
	 * @return string|null
	 *
	 * @throws Application\BadRequestException
	 */
	private function getParameter(array $params, string $key, bool $required = FALSE)
	{
		if (!isset($params[$key])) {
			if ($required) {
				throw new Application\BadRequestException(sprintf('Parameter "%s" is missing.', $key));
			}

			return NULL;
		}

		return $params[$key];
	}
}
