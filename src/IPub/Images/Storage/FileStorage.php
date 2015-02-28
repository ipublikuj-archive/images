<?php
/**
 * FileStorage.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Images!
 * @subpackage	Storages
 * @since		5.0
 *
 * @date		09.02.15
 */

namespace IPub\Images\Storage;

use Nette;
use Nette\Application;
use Nette\Http;
use Nette\Utils;

use IPub;
use IPub\Images;
use IPub\Images\Files;
use IPub\Images\Exceptions;
use IPub\Images\Image;
use IPub\Images\Validators;

abstract class FileStorage extends Nette\Object implements IStorage
{
	/**
	 * @var string
	 */
	private $storageDir;

	/**
	 * @var string
	 */
	private $webDir;

	/**
	 * @var string|null
	 */
	private $namespace = NULL;

	/**
	 * @var Application\Application
	 */
	private $application;

	/**
	 * @var Validators\IValidator
	 */
	private $validator;

	/**
	 * @var Files\Browser
	 */
	private $browser;

	/**
	 * @var Application\IPresenter
	 */
	private $presenter;

	/**
	 * @param string $storageDir
	 * @param string $webDir
	 * @param Validators\Validator $validator
	 * @param Application\Application $application
	 */
	public function __construct($storageDir, $webDir, Validators\Validator $validator, Application\Application $application)
	{
		if (!is_dir($storageDir)) {
			Utils\FileSystem::createDir($storageDir);
		}
		$this->storageDir = $storageDir;

		if (!is_dir($webDir)) {
			Utils\FileSystem::createDir($webDir);
		}
		$this->webDir = $webDir;

		$this->application = $application;
		$this->validator = $validator;

		$this->browser = new Files\Browser($this);
	}

	/**
	 * @return Validators\Validator
	 */
	public function getValidator()
	{
		return $this->validator;
	}

	/**
	 * @param string $dir
	 *
	 * @return $this
	 *
	 * @throw Exceptions\DirectoryNotFoundException
	 */
	public function setStorageDir($dir)
	{
		if (!is_dir($dir)) {
			throw new Exceptions\DirectoryNotFoundException("Directory '$dir' does not exist.");
		}

		$this->storageDir = $dir;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getStorageDir()
	{
		return $this->storageDir;
	}

	/**
	 * @param string $dir
	 *
	 * @return $this
	 *
	 * @throw Exceptions\DirectoryNotFoundException
	 */
	public function setWebDir($dir)
	{
		if (!is_dir($dir)) {
			throw new Exceptions\DirectoryNotFoundException("Directory '$dir' does not exist.");
		}

		$this->webDir = $dir;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getWebDir()
	{
		return $this->webDir;
	}

	/**
	 * @param string $namespace
	 *
	 * @return $this
	 */
	public function setNamespace($namespace = NULL)
	{
		if (!$namespace) {
			$this->namespace = NULL;

		} else {
			$this->namespace = trim(trim($namespace), DIRECTORY_SEPARATOR);
		}

		return $this;
	}

	/**
	 * @return string
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}

	/**
	 * @param string $namespace
	 *
	 * @return bool
	 */
	public function namespaceExists($namespace)
	{
		return is_dir($this->getStorageDir() . DIRECTORY_SEPARATOR . $namespace);
	}

	/**
	 * @param string|NULL $namespace
	 *
	 * @return string
	 */
	public function getNamespacePath($namespace = NULL)
	{
		return $this->getStorageDir() . DIRECTORY_SEPARATOR . ($namespace ?: $this->getNamespace());
	}

	/**
	 * @param string $namespace
	 *
	 * @return IStorage
	 */
	public function createNamespace($namespace)
	{
		$storage = clone $this;
		$storage->setNamespace($namespace);

		return $storage;
	}

	/**
	 * @param string $filename
	 *
	 * @return Image\Image
	 */
	public function get($filename)
	{
		if ($absoluteName = realpath($this->getStorageDir() . DIRECTORY_SEPARATOR . ($this->getNamespace() ? $this->getNamespace() . DIRECTORY_SEPARATOR : '') . $filename)) {
			return new Image\Image($absoluteName);
		}

		return NULL;
	}

	/**
	 * @param Http\FileUpload $file
	 * @param string $namespace
	 *
	 * @return Image\Image
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function upload(Http\FileUpload $file, $namespace = NULL)
	{
		if (!$file->isOk() || !$file->isImage()) {
			throw new Exceptions\InvalidArgumentException;
		}

		// Create filename with path
		$absoluteName = $this->generateUniqueFilename($file->getSanitizedName(), $namespace?:$this->getNamespace());

		$file->move($absoluteName);

		$image = new Image\Image($absoluteName);

		return $image;
	}

	/**
	 * @param string $content
	 * @param string $filename
	 * @param string $namespace
	 *
	 * @return Image\Image
	 */
	public function save($content, $filename, $namespace = NULL)
	{
		// Create filename with path
		$absoluteName = $this->generateUniqueFilename($filename, $namespace?:$this->getNamespace());

		file_put_contents($absoluteName, $content);

		$image = new Image\Image($absoluteName);

		return $image;
	}

	/**
	 * @param string $filename
	 *
	 * @return $this
	 *
	 * @throws Nette\IOException
	 */
	public function delete($filename)
	{
		/** @var $file \SplFileInfo */
		foreach (Utils\Finder::findFiles($filename)->from($this->getStorageDir() . ($this->getNamespace() ? DIRECTORY_SEPARATOR . $this->getNamespace() : "")) as $file) {
			Utils\FileSystem::delete($file->getPathname());
		}

		/** @var $file \SplFileInfo */
		foreach (Utils\Finder::findFiles($filename)->from($this->getWebDir() . ($this->getNamespace() ? DIRECTORY_SEPARATOR . $this->getNamespace() : "")) as $file) {
			Utils\FileSystem::delete($file->getPathname());
		}

		return $this;
	}

	/**
	 * @param string $filename
	 *
	 * @return string
	 */
	public function getOriginalFile($filename)
	{
		return realpath($this->getStorageDir() . DIRECTORY_SEPARATOR . ($this->getNamespace() ? $this->getNamespace() . DIRECTORY_SEPARATOR : '') . $filename);
	}

	/**
	 * @param string $filename
	 * @param string $size
	 * @param array|string|null $algorithm
	 * @param bool $strictMode
	 *
	 * @return string
	 */
	public function request($filename, $size, $algorithm = NULL, $strictMode = FALSE)
	{
		// Get file info
		$file = new \SplFileInfo($filename);

		// Generate image url
		return $this->getPresenter()->link(':IPub:Images:', [
			'storage'   => (string) $this,
			'namespace' => $this->getNamespace(),
			'filename'  => basename($file->getBasename(), '.'. $file->getExtension()),
			'extension' => $file->getExtension(),
			'size'      => $size,
			'algorithm' => $algorithm
		]);
	}

	/**
	 * @param string $filename
	 * @param string|null $namespace
	 *
	 * @return string
	 */
	private function generateUniqueFilename($filename, $namespace = NULL)
	{
		$path = $this->getStorageDir() . DIRECTORY_SEPARATOR . ($namespace ? $namespace . DIRECTORY_SEPARATOR : '');

		if (!file_exists($absoluteName = $path . $filename)) {
			return $absoluteName;
		}

		do {
			$name = Utils\Random::generate(10) . '.' . $filename;
		} while (file_exists($absoluteName = $path . $name));

		return $absoluteName;
	}

	/**
	 * @return Application\IPresenter
	 */
	private function getPresenter()
	{
		if (!$this->presenter) {
			$this->presenter = $this->application->getPresenter();
		}

		return $this->presenter;
	}

	/**
	 * @param Application\IPresenter $presenter
	 *
	 * @return $this
	 */
	public function setPresenter(Application\IPresenter $presenter)
	{
		$this->presenter = $presenter;

		return $this;
	}

	/**
	 * @param string $dir
	 *
	 * @return void
	 *
	 * @throws Nette\IOException
	 */
	private static function mkdir($dir)
	{
		Utils\FileSystem::createDir($dir);
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return 'default';
	}
}