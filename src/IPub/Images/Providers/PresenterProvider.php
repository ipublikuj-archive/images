<?php
/**
 * PresenterProvider.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Images!
 * @subpackage     Providers
 * @since          2.0.0
 *
 * @date           12.05.16
 */

declare(strict_types = 1);

namespace IPub\Images\Providers;

use Nette;
use Nette\Application;
use Nette\Utils;

use IPub\Images;
use IPub\Images\Exceptions;
use IPub\Images\Helpers;
use IPub\Images\Validators;

use League\Flysystem;

/**
 * Presenter provider
 *
 * @package        iPublikuj:Images!
 * @subpackage     Providers
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class PresenterProvider implements IProvider
{
  use Nette\SmartObject;

	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @var Validators\Validator
	 */
	private $validator;

	/**
	 * @var Flysystem\MountManager
	 */
	private $mountManager;

	/**
	 * @var Application\LinkGenerator
	 */
	private $linkGenerator;

	/**
	 * @param Validators\Validator $validator
	 * @param Flysystem\MountManager $mountManager
	 * @param Application\LinkGenerator $linkGenerator
	 */
	public function __construct(
		Validators\Validator $validator,
		Flysystem\MountManager $mountManager,
		Application\LinkGenerator $linkGenerator
	) {
		$this->validator = $validator;
		$this->mountManager = $mountManager;
		$this->linkGenerator = $linkGenerator;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName() : string
	{
		return 'presenter';
	}

	/**
	 * {@inheritdoc}
	 */
	public function request(string $storage, string $namespace = NULL, string $filename, string $size = NULL, string $algorithm = NULL) : string
	{
		try {
			$fileSystem = $this->mountManager->getFilesystem($storage);

		} catch (\LogicException $ex) {
			throw new Exceptions\InvalidArgumentException(sprintf('Images storage: "%s" is not registered.', $storage));
		}

		if (empty($filename)) {
			return '#';
		}

		if (!$fileSystem->has(($namespace === NULL ? NULL : $namespace . DIRECTORY_SEPARATOR) . $filename)) {
			throw new Exceptions\FileNotFoundException(sprintf('Image: "%s" in storage: "%s" was not found.', (($namespace === NULL ? NULL : $namespace . DIRECTORY_SEPARATOR) . $filename), $storage));
		}

		// Parse size
		$size = Helpers\Converters::createSizeString($size);

		list($width, $height) = Helpers\Converters::parseSizeString($size);

		// Parse algorithm
		$algorithm = Helpers\Converters::createAlgorithmString($algorithm);

		$algorithmForValidation = Helpers\Converters::parseAlgorithm($algorithm);

		// Extract algorithm
		if ($algorithmForValidation === NULL) {
			$algorithmForValidation = Utils\Image::FIT;
		}

		// Validate params
		if ($size !== 'original' && !$this->validator->validate($width, $height, $algorithmForValidation, $storage)) {
			throw new Exceptions\NotAllowedImageSizeException(sprintf('Size "%s" of image "%s" is not allowed in defined rules', $size, (($namespace === NULL ? NULL : $namespace . DIRECTORY_SEPARATOR) . $filename)));
		}

		// Get file info
		$file = new \SplFileInfo($filename);

		try {
			// Generate image url
			return $this->linkGenerator->link('IPub:Images:', [
				'storage'   => $storage,
				'namespace' => $namespace,
				'filename'  => basename($file->getBasename(), '.' . $file->getExtension()),
				'extension' => $file->getExtension(),
				'size'      => $size,
				'algorithm' => $algorithm
			]);

		} catch (Application\UI\InvalidLinkException $ex) {
			throw new Exceptions\InvalidStateException('Link for presenter "IPub:Images:", can\'t be created. Is your route correctly defined?');
		}
	}
}
