<?php
/**
 * Image.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Images!
 * @subpackage	Image
 * @since		5.0
 *
 * @date		05.04.14
 */

namespace IPub\Images\Image;

use Nette;
use Nette\Http;

use IPub;
use IPub\Images;
use IPub\Images\Exceptions;
use IPub\Images\Storage;

class Image extends Nette\Object
{
	/**
	 * @var string
	 */
	private $file;

	/**
	 * @var Images\Size
	 */
	private $size;

	/**
	 * @param string $file
	 */
	public function __construct($file)
	{
		$this->file	= $file;
		$this->size	= Size::fromFile($file);
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
			$namespace = trim(trim($namespace), '/');

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
	 * @return Images\Size
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