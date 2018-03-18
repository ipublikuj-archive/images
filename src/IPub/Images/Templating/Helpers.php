<?php
/**
 * Helpers.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:Images!
 * @subpackage     Templating
 * @since          1.0.0
 *
 * @date           05.04.14
 */

declare(strict_types = 1);

namespace IPub\Images\Templating;

use Nette;
use Nette\Utils;

use IPub\Images;
use IPub\Images\Exceptions;

use League\Flysystem;

/**
 * Templates helpers
 *
 * @package        iPublikuj:Images!
 * @subpackage     Templating
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class Helpers
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @var Images\ImagesLoader
	 */
	private $imagesLoader;

	/**
	 * @param Images\ImagesLoader $imagesLoader
	 */
	public function __construct(
		Images\ImagesLoader $imagesLoader
	) {
		$this->imagesLoader = $imagesLoader;
	}

	/**
	 * @param string $file
	 *
	 * @return bool
	 *
	 * @throws Exceptions\InvalidArgumentException
	 * @throws Exceptions\InvalidStateException
	 *
	 * @throws Exceptions\InvalidArgumentException
	 * @throws Exceptions\InvalidStateException
	 * @throws Utils\ImageException
	 */
	public function isSquare(string $file) : bool
	{
		$image = $this->fromString($file);

		return $image->getWidth() === $image->getHeight();
	}

	/**
	 * @param string $file
	 *
	 * @return bool
	 *
	 * @throws Exceptions\InvalidArgumentException
	 * @throws Exceptions\InvalidStateException
	 * @throws Utils\ImageException
	 */
	public function isHigher(string $file) : bool
	{
		$image = $this->fromString($file);

		return $image->getWidth() < $image->getHeight();
	}

	/**
	 * @param string $file
	 *
	 * @return bool
	 *
	 * @throws Exceptions\InvalidArgumentException
	 * @throws Exceptions\InvalidStateException
	 * @throws Utils\ImageException
	 */
	public function isWider(string $file) : bool
	{
		$image = $this->fromString($file);

		return $image->getWidth() > $image->getHeight();
	}

	/**
	 * @param array $parameters
	 *
	 * @return string
	 *
	 * @throws Exceptions\InvalidArgumentException
	 * @throws Exceptions\InvalidStateException
	 */
	public function imageLink(array $parameters) : string
	{
		return $this->imagesLoader->request($parameters);
	}

	/**
	 * @param string $file
	 *
	 * @return Utils\Image
	 *
	 * @throws Exceptions\InvalidArgumentException
	 * @throws Exceptions\InvalidStateException
	 * @throws Utils\ImageException
	 */
	private function fromString($file) : Utils\Image
	{
		$arguments = Images\Helpers\Converters::parseImageString($file);

		$namespace = NULL;

		if ($arguments['namespace']) {
			$namespace = $arguments['namespace'] . DIRECTORY_SEPARATOR;
		}

		$filePath = $namespace . $arguments['filename'];

		if (isset($arguments['storage']) && ($storage = $arguments['storage'])) {
			try {
				$fileSystem = $this->imagesLoader->getStorage($storage);

				try {
					$image = $fileSystem->read($filePath);

					$image = Utils\Image::fromString($image);

					return $image;

				} catch (Flysystem\FileNotFoundException $ex) {
					throw new Exceptions\FileNotFoundException(sprintf('Image: "%s" in storage: "%s" was not found.', $filePath, $storage));
				}

			} catch (\LogicException $ex) {
				throw new Exceptions\InvalidStateException(sprintf('Images storage: "%s" for file: "%s" was not found.', $filePath, $storage));
			}
		}

		throw new Exceptions\InvalidStateException('Images storage for file: "'. $filePath .'" was not found.');
	}
}
