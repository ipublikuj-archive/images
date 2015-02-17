<?php
/**
 * Browser.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Images!
 * @subpackage	Files
 * @since		5.0
 *
 * @date		05.04.14
 */

namespace IPub\Images\Files;

use Nette;
use Nette\Utils;

use IPub;
use IPub\Images\Exceptions;
use IPub\Images\Storage;

class Browser extends Nette\Object
{
	/**
	 * @var Storage\IStorage
	 */
	private $storage;

	/**
	 * @param Storage\IStorage $storage
	 */
	public function __construct(Storage\IStorage $storage)
	{
		$this->storage = $storage;
	}

	/**
	 * Returns all files in namespace
	 *
	 * @param null $namespace
	 *
	 * @return \SplFileInfo[]
	 */
	public function getNamespaceFiles($namespace = NULL)
	{
		$files = [];
		$imageDir = $this->storage->getStorageDir() . ($namespace ? DIRECTORY_SEPARATOR . $namespace : "");

		/** @var $file \SplFileInfo */
		foreach (Utils\Finder::findFiles("*")->in($this->storage->getStorageDir(), $imageDir) as $file) {
			$files[] = $file;
		}

		return $files;
	}

	/**
	 * Returns all declared namespaces
	 *
	 * @return array
	 */
	public function getDeclaredNamespaces()
	{
		$namespaces = [];

		/** @var $file \SplFileInfo */
		foreach (Utils\Finder::findDirectories("*")->in($this->storage->getStorageDir()) as $file) {
			$namespaces[] = $file->getFilename();
		}

		return $namespaces;
	}

	/**
	 * @param $param
	 *
	 * @return string
	 *
	 * @throws Exceptions\FileNotFoundException
	 */
	public function find($param)
	{
		foreach (Utils\Finder::findFiles($param)->from($this->storage->getStorageDir()) as $file) {
			/** @var \SplFileInfo $file */
			return $file->getPathname();
		}

		throw new Exceptions\FileNotFoundException("File $param not found.");
	}
}