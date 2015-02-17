<?php
/**
 * Helpers.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Images!
 * @subpackage	Templating
 * @since		5.0
 *
 * @date		05.04.14
 */

namespace IPub\Images\Templating;

use Nette;

use Latte\Engine;

use IPub;
use IPub\Images;
use IPub\Images\Exceptions;
use IPub\Images\Image;

if (!function_exists('id') && PHP_VERSION_ID < 50400) { # workaround for php < 5.4
	function id($obj) { return $obj; }
}

class Helpers extends Nette\Object
{
	/**
	 * @var Images\ImagesLoader
	 */
	protected $imagesLoader;

	/**
	 * @param Images\ImagesLoader $imagesLoader
	 */
	public function __construct(Images\ImagesLoader $imagesLoader)
	{
		$this->imagesLoader = $imagesLoader;
	}

	/**
	 * @deprecated
	 *
	 * @param $method
	 *
	 * @return array
	 */
	public function loader($method)
	{
		if (method_exists($this, $method)) {
			return callback($this, $method);
		}
	}

	/**
	 * Register template filters
	 *
	 * @param Engine $engine
	 */
	public function register(Engine $engine)
	{
		$engine->addFilter('isSquare', array($this, 'isSquare'));
		$engine->addFilter('isHigher', array($this, 'isHigher'));
		$engine->addFilter('isWider', array($this, 'isWider'));
		$engine->addFilter('fromString', array($this, 'fromString'));
		$engine->addFilter('getImagesLoader', array($this, 'getImagesLoader'));
	}

	/**
	 * @param string $file
	 *
	 * @return bool
	 */
	public function isSquare($file)
	{
		$size = $this->fromString($file);

		return $size->getWidth() === $size->getHeight();
	}

	/**
	 * @param string $file
	 *
	 * @return bool
	 */
	public function isHigher($file)
	{
		$size = $this->fromString($file);

		return $size->getWidth() < $size->getHeight();
	}

	/**
	 * @param string $file
	 *
	 * @return bool
	 */
	public function isWider($file)
	{
		$size = $this->fromString($file);

		return $size->getWidth() > $size->getHeight();
	}

	/**
	 * @param string $file
	 *
	 * @return Images\Size
	 *
	 * @throws Exceptions\InvalidArgumentException
	 * @throws Exceptions\InvalidStateException
	 */
	public function fromString($file)
	{
		// Extract info from file string
		preg_match("/\b(?P<storage>[a-zA-Z]+)\:\/\/(?:(?<namespace>[a-zA-Z0-9\/-]+)\/)?(?<name>[a-zA-Z0-9-]+).(?P<extension>[a-zA-Z]{3}+)/i", $file, $matches);

		if (isset($matches['storage']) && ($storage = $this->imagesLoader->getStorage($matches['storage']))) {
			if (isset($matches['namespace']) && trim($matches['namespace'])) {
				$storage->setNamespace(trim($matches['namespace']));
			}

			$image = $storage->get($matches['name'] .'.'. $matches['extension']);

			if ($image instanceof Image\Image) {
				if (PHP_VERSION_ID < 50400) {
					return id($image)->getSize();
				}

				return $image->getSize();

			} else {
				throw new Exceptions\FileNotFoundException("Image: '$file' in storage: '$storage' was not found.");
			}

		} else {
			throw new Exceptions\InvalidStateException("Images storage for file: '$file' was not found.");
		}
	}

	/**
	 * @return Images\ImagesLoader
	 */
	public function getImagesLoaderService()
	{
		return $this->imagesLoader;
	}
}