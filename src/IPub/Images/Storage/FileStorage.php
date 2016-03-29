<?php
/**
 * FileStorage.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Images!
 * @subpackage     Storage
 * @since          1.0.0
 *
 * @date           09.02.15
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

/**
 * Basic file storage for images
 *
 * @package        iPublikuj:Images!
 * @subpackage     Storage
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
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
	 * @var Application\UI\Presenter
	 */
	private $presenter;

	/**
	 * @param string $storageDir
	 * @param string $webDir
	 * @param Validators\Validator $validator
	 * @param Application\Application $application
	 */
	public function __construct(
		$storageDir,
		$webDir,
		Validators\Validator $validator,
		Application\Application $application
	) {
		if (!is_dir($storageDir)) {
			Utils\FileSystem::createDir($storageDir);
		}

		$this->setStorageDir($storageDir);

		if (!is_dir($webDir)) {
			Utils\FileSystem::createDir($webDir);
		}

		$this->setWebDir($webDir);

		$this->application = $application;
		$this->validator = $validator;
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
	 * @throw Exceptions\DirectoryNotFoundException
	 */
	public function setStorageDir($dir)
	{
		if (!is_dir($dir)) {
			throw new Exceptions\DirectoryNotFoundException('Directory "'. $dir .'" does not exist.');
		}

		$this->storageDir = $dir;
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
	 * @throw Exceptions\DirectoryNotFoundException
	 */
	public function setWebDir($dir)
	{
		if (!is_dir($dir)) {
			throw new Exceptions\DirectoryNotFoundException('Directory "'. $dir .'" does not exist.');
		}

		$this->webDir = $dir;
	}

	/**
	 * @return string
	 */
	public function getWebDir()
	{
		return $this->webDir;
	}

	/**
	 * @inheritdoc
	 */
	public function setNamespace($namespace = NULL)
	{
		if ($namespace === NULL) {
			$this->namespace = NULL;

		} else {
			$this->namespace = trim(trim($namespace), DIRECTORY_SEPARATOR);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}

	/**
	 * @inheritdoc
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
	 * @inheritdoc
	 */
	public function get($filename)
	{
		if ($absoluteName = realpath($this->getStorageDir() . DIRECTORY_SEPARATOR . ($this->getNamespace() ? $this->getNamespace() . DIRECTORY_SEPARATOR : '') . $filename)) {
			return new Image\Image($absoluteName);
		}

		return NULL;
	}

	/**
	 * @inheritdoc
	 */
	public function upload(Http\FileUpload $file, $namespace = NULL)
	{
		if (!$file->isOk() || !$file->isImage()) {
			throw new Exceptions\InvalidArgumentException;
		}

		// Create filename with path
		$absoluteName = $this->generateUniqueFilename($file->getSanitizedName(), $namespace ?: $this->getNamespace());

		$file->move($absoluteName);

		$image = new Image\Image($absoluteName);

		return $image;
	}

	/**
	 * @inheritdoc
	 */
	public function save($content, $filename, $namespace = NULL)
	{
		// Create filename with path
		$absoluteName = $this->generateUniqueFilename($filename, $namespace ?: $this->getNamespace());

		file_put_contents($absoluteName, $content);

		$image = new Image\Image($absoluteName);

		return $image;
	}

	/**
	 * @inheritdoc
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
	 * @inheritdoc
	 */
	public function request($filename, $size, $algorithm = NULL, $strictMode = FALSE)
	{
		// Get file info
		$file = new \SplFileInfo($filename);

		// Generate image url
		return $this->getPresenter()->link(':IPub:Images:', [
			'storage'   => (string) $this,
			'namespace' => $this->getNamespace(),
			'filename'  => basename($file->getBasename(), '.' . $file->getExtension()),
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

		if (!is_file($absoluteName = $path . $filename)) {
			return $absoluteName;
		}

		do {
			$name = Utils\Random::generate(10) . '.' . $filename;
		} while (is_file($absoluteName = $path . $name));

		return $absoluteName;
	}

	/**
	 * @return Application\UI\Presenter
	 */
	private function getPresenter()
	{
		if (!$this->presenter) {
			$this->presenter = $this->application->getPresenter();
		}

		return $this->presenter;
	}

	/**
	 * @param Application\UI\Presenter $presenter
	 *
	 * @return $this
	 */
	public function setPresenter(Application\UI\Presenter $presenter)
	{
		$this->presenter = $presenter;

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function __toString()
	{
		return 'default';
	}
}
