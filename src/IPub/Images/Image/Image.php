<?php
/**
 * Image.php
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
use Nette\Http;

use IPub;
use IPub\Images;
use IPub\Images\Exceptions;
use IPub\Images\Storage;

/**
 * Saved image entity
 *
 * @package        iPublikuj:Images!
 * @subpackage     Image
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class Image extends Nette\Object
{
	/**
	 * @var string
	 */
	private $file;

	/**
	 * @var Size
	 */
	private $size;

	/**
	 * @param string $file
	 */
	public function __construct($file)
	{
		$this->file = $file;
		$this->size = Size::fromFile($file);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return substr($this->file, strrpos($this->file, DIRECTORY_SEPARATOR) + 1);
	}

	/**
	 * @return null|string
	 */
	public function getNamespace()
	{
		if (strrpos($this->file, DIRECTORY_SEPARATOR) !== FALSE) {
			$namespace = substr($this->file, 0, strrpos($this->file, DIRECTORY_SEPARATOR));
			$namespace = trim(trim($namespace), DIRECTORY_SEPARATOR);

			// Image namespace
			return $namespace ?: NULL;
		}

		return NULL;
	}

	/**
	 * @return bool
	 */
	public function exists()
	{
		return is_file($this->file);
	}

	/**
	 * @return string
	 */
	public function getFile()
	{
		return $this->file;
	}

	/**
	 * @return Size
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->file;
	}
}
