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
use IPub\Images\Helpers;

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
	 * @param Flysystem\MountManager $mountManager
	 * @param Application\LinkGenerator $linkGenerator
	 */
	public function __construct(
		Flysystem\MountManager $mountManager,
		Application\LinkGenerator $linkGenerator
	) {
		$this->mountManager = $mountManager;
		$this->linkGenerator = $linkGenerator;
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
			throw new Exceptions\FileNotFoundException('Image: "' . ($namespace === NULL ? NULL : $namespace . DIRECTORY_SEPARATOR) . $filename . '" in storage: "' . $storage . '" was not found.');
		}

		// Parse size
		$size = Helpers\Converters::createSizeString($size);

		// Parse algorithm
		$algorithm = Helpers\Converters::createAlgorithmString($algorithm);

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
