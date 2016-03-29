<?php
/**
 * Size.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Images!
 * @subpackage     Image
 * @since          1.0.0
 *
 * @date           05.04.14
 */

namespace IPub\Images\Image;

use Nette;

/**
 * Saved image size entity
 *
 * @package        iPublikuj:Images!
 * @subpackage     Image
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class Size extends Nette\Object
{
	/**
	 * @var float|int
	 */
	private $width;

	/**
	 * @var float|int
	 */
	private $height;

	/**
	 * @param float|int $width
	 * @param float|int $height
	 */
	public function __construct($width, $height)
	{
		$this->width = $width;
		$this->height = $height;
	}

	/**
	 * @return float|int
	 */
	public function getHeight()
	{
		return $this->height;
	}

	/**
	 * @return float|int
	 */
	public function getWidth()
	{
		return $this->width;
	}

	/**
	 * @return bool
	 */
	public function isSquare()
	{
		return $this->getWidth() === $this->getHeight();
	}

	/**
	 * @return bool
	 */
	public function isHigher()
	{
		return $this->getWidth() < $this->getHeight();
	}

	/**
	 * @return bool
	 */
	public function isWider()
	{
		return $this->getWidth() > $this->getHeight();
	}

	/**
	 * @param string $file
	 *
	 * @return Size
	 */
	public static function fromFile($file)
	{
		list($width, $height) = @getimagesize($file);

		return new Size($width, $height);
	}
}
