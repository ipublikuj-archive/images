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

namespace IPub\Images\Providers;

use Nette;
use Nette\Application;
use Nette\Utils;

use IPub;
use IPub\Images;
use IPub\Images\Exceptions;

use League\Flysystem;

/**
 * Presenter provider
 *
 * @package        iPublikuj:Images!
 * @subpackage     Providers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class PresenterProvider extends Nette\Object implements IProvider
{
	/**
	 * Define class name
	 */
	const CLASS_NAME = __CLASS__;

	/**
	 * @var Flysystem\MountManager
	 */
	private $mountManager;

	/**
	 * @var Application\LinkGenerator
	 */
	private $linkGenerator;

	/**
	 * @var Application\Application
	 */
	private $application;

	/**
	 * @param Flysystem\MountManager $mountManager
	 * @param Application\LinkGenerator $linkGenerator
	 * @param Application\Application $application
	 */
	public function __construct(
		Flysystem\MountManager $mountManager,
		Application\LinkGenerator $linkGenerator,
		Application\Application $application
	) {
		$this->mountManager = $mountManager;
		$this->linkGenerator = $linkGenerator;
		$this->application = $application;
	}

	/**
	 * @inheritdoc
	 */
	public function request($storage, $namespace, $filename, $size = NULL, $algorithm = NULL)
	{
		try {
			$fileSystem = $this->mountManager->getFilesystem($storage);

		} catch (\LogicException $ex) {
			throw new Exceptions\InvalidArgumentException('Images storage: "' . $storage . '" is not registered.');
		}

		if (empty($filename)) {
			return '#';
		}

		if (!$fileSystem->has(($namespace === NULL ? NULL : $namespace . DIRECTORY_SEPARATOR) . $filename)) {
			throw new Exceptions\FileNotFoundException('Image: "' . ($namespace === NULL ? NULL : $namespace . DIRECTORY_SEPARATOR) . '" in storage: "' . $storage . '" was not found.');
		}

		// Parse size
		if (empty($size) || $size === NULL) {
			$size = 'original';

		} elseif (strpos($size, 'x') !== FALSE) {
			list($width, $height) = explode('x', $size);

			if ((int) $height > 0) {
				$size = (int) $width . 'x' . (int) $height;

			} else {
				$size = (int) $width;
			}

		} else {
			$size = (int) $size;
		}

		// Parse algorithm
		if (empty($algorithm) || $algorithm === NULL) {
			$algorithm = NULL;

		} elseif ($algorithm === NULL) {
			$algorithm = Utils\Image::FIT;

		} elseif (!is_int($algorithm) && !is_array($algorithm)) {
			switch (strtolower($algorithm)) {
				case 'fit':
					$algorithm = Utils\Image::FIT;
					break;

				case 'fill':
					$algorithm = Utils\Image::FILL;
					break;

				case 'exact':
					$algorithm = Utils\Image::EXACT;
					break;

				case 'shrink_only':
				case 'shrinkonly':
				case 'shrink-only':
					$algorithm = Utils\Image::SHRINK_ONLY;
					break;

				case 'stretch':
					$algorithm = Utils\Image::STRETCH;
					break;

				default:
					$algorithm = NULL;
			}

		} else {
			$algorithm = NULL;
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
