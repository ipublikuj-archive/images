<?php
/**
 * IStorage.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Images!
 * @subpackage	Storage
 * @since		5.0
 *
 * @date		09.02.15
 */

namespace IPub\Images\Storage;

use Nette;
use Nette\Http;

use IPub;
use IPub\Images\Image;
use IPub\Images\Validators;

interface IStorage
{
	/**
	 * @return Validators\Validator
	 */
	public function getValidator();

	/**
	 * @param string $namespace
	 *
	 * @return $this
	 */
	public function setNamespace($namespace = NULL);

	/**
	 * @return string
	 */
	public function getNamespace();

	/**
	 * @param string $namespace
	 *
	 * @return bool
	 */
	public function namespaceExists($namespace);

	/**
	 * @param string $filename
	 *
	 * @return Image\Image
	 */
	public function get($filename);

	/**
	 * @param Http\FileUpload $file
	 * @param string $namespace
	 *
	 * @return Image\Image
	 */
	public function upload(Http\FileUpload $file, $namespace);

	/**
	 * @param string $content
	 * @param string $filename
	 * @param string $namespace
	 *
	 * @return Image\Image
	 */
	public function save($content, $filename, $namespace);

	/**
	 * @param string $filename
	 *
	 * @return $this
	 */
	public function delete($filename);

	/**
	 * @param string $filename
	 * @param string $size
	 * @param array|string|null $algorithm
	 * @param bool $strictMode
	 *
	 * @return string
	 */
	public function request($filename, $size, $algorithm = NULL, $strictMode = FALSE);

	/**
	 * @return string
	 */
	public function __toString();
}