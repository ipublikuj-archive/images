<?php
/**
 * Helpers.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Images!
 * @subpackage     Templating
 * @since          1.0.0
 *
 * @date           05.04.14
 */

namespace IPub\Images\Templating;

use Nette;
use Nette\Utils;

use Latte\Engine;

use IPub;
use IPub\Images;
use IPub\Images\Exceptions;
use IPub\Images\Image;

use League\Flysystem;

/**
 * Templates helpers
 *
 * @package        iPublikuj:Images!
 * @subpackage     Templating
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Helpers extends Nette\Object
{
	/**
	 * Define class name
	 */
	const CLASS_NAME = __CLASS__;

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
	 * Register template filters
	 *
	 * @param Engine $engine
	 */
	public function register(Engine $engine)
	{
		$engine->addFilter('isSquare', [$this, 'isSquare']);
		$engine->addFilter('isHigher', [$this, 'isHigher']);
		$engine->addFilter('isWider', [$this, 'isWider']);
		$engine->addFilter('getImagesLoader', [$this, 'getImagesLoader']);
	}

	/**
	 * @param string $file
	 *
	 * @return bool
	 */
	public function isSquare($file)
	{
		$image = $this->fromString($file);

		return $image->getWidth() === $image->getHeight();
	}

	/**
	 * @param string $file
	 *
	 * @return bool
	 */
	public function isHigher($file)
	{
		$image = $this->fromString($file);

		return $image->getWidth() < $image->getHeight();
	}

	/**
	 * @param string $file
	 *
	 * @return bool
	 */
	public function isWider($file)
	{
		$image = $this->fromString($file);

		return $image->getWidth() > $image->getHeight();
	}

	/**
	 * @param string $file
	 *
	 * @return Utils\Image
	 *
	 * @throws Exceptions\InvalidArgumentException
	 * @throws Exceptions\InvalidStateException
	 */
	private function fromString($file)
	{
		// Extract info from file string
		preg_match("/\b(?P<storage>[a-zA-Z]+)\:\/\/(?:(?<namespace>[a-zA-Z0-9\/-]+)\/)?(?<name>[a-zA-Z0-9-]+).(?P<extension>[a-zA-Z]{3}+)/i", $file, $matches);

		$namespace = NULL;

		if (isset($matches['namespace']) && trim($matches['namespace'])) {
			$namespace = trim($matches['namespace']) . DIRECTORY_SEPARATOR;
		}

		$filePath = $namespace . $matches['name'] . '.' . $matches['extension'];

		if (isset($matches['storage']) && ($storage = $matches['storage'])) {
			try {
				$fileSystem = $this->imagesLoader->getStorage($storage);

				try {
					$image = $fileSystem->read($filePath);

					$image = Utils\Image::fromString($image);

					return $image;

				} catch (Flysystem\FileNotFoundException $ex) {
					throw new Exceptions\FileNotFoundException('Image: "' . $filePath . '" in storage: "' . $storage . '" was not found.');
				}

			} catch (\LogicException $ex) {
				throw new Exceptions\InvalidStateException('Images storage: "' . $storage . '" for file: "'. $filePath .'" was not found.');
			}
		}

		throw new Exceptions\InvalidStateException('Images storage for file: "'. $filePath .'" was not found.');
	}

	/**
	 * @return Images\ImagesLoader
	 */
	public function getImagesLoaderService()
	{
		return $this->imagesLoader;
	}
}
