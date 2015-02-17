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

use IPub\Images\Storage\DefaultStorage;
use Nette;
use Nette\Utils\Finder,
	Nette\Utils\Strings;

use IPub;
use IPub\Images\Exceptions;

class Browser extends Nette\Object
{
	/**
	 * @var string
	 */
	private $assetsDir;

	/**
	 * @var array
	 */
	private $generatedDirs;

	/**
	 * @var string
	 */
	private $originalPrefix;

	/**
	 * @param ImagesLoader $imagePipe
	 */
	public function __construct()
	{
		/*
		$this->assetsDir		= $imagePipe->getAssetsDir();
		$this->originalPrefix	= $imagePipe->getOriginalPrefix();
		$this->generatedDirs	= array(
			$imagePipe->getOriginalPrefix(),
			'[0-9]_[0-9]*x[0-9]*'
		);
		*/
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
		$files = array();
		$imageDir = $this->assetsDir . ($namespace ? DIRECTORY_SEPARATOR . $namespace : "") . DIRECTORY_SEPARATOR . $this->originalPrefix;

		/** @var $file \SplFileInfo */
		foreach (Finder::findFiles("*")->in($this->assetsDir, $imageDir) as $file) {
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
		$namespaces = array();

		/** @var $file \SplFileInfo */
		foreach (Finder::findDirectories("*")->in($this->assetsDir)->exclude($this->generatedDirs) as $file) {
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
		foreach (Finder::findFiles($param)->from($this->assetsDir) as $file) {
			/** @var \SplFileInfo $file */
			return $file->getPathname();
		}

		throw new Exceptions\FileNotFoundException("File $param not found.");
	}
}