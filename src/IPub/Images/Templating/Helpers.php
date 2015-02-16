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
	 * @return Image\Size
	 */
	public function fromString($file)
	{
		if (PHP_VERSION_ID < 50400) {
			return id(new Image\Image($file))->getSize();
		}

		return (new Image\Image($file))->getSize();
	}

	/**
	 * @return Images\ImagesLoader
	 */
	public function getImagesLoaderService()
	{
		return $this->imagesLoader;
	}
}