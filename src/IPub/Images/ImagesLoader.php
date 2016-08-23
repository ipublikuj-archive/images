<?php
/**
 * ImagesLoader.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Images!
 * @subpackage     common
 * @since          1.0.0
 *
 * @date           09.02.15
 */

declare(strict_types = 1);

namespace IPub\Images;

use Nette;
use Nette\Application;
use Nette\Utils;

use IPub;
use IPub\Images;
use IPub\Images\Exceptions;
use IPub\Images\Providers;
use IPub\Images\Templating;

use League\Flysystem;

/**
 * Images loader
 *
 * @package        iPublikuj:Images!
 * @subpackage     common
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ImagesLoader extends Nette\Object
{
	/**
	 * Define class name
	 */
	const CLASS_NAME = __CLASS__;

	/**
	 * @var Providers\IProvider[]
	 */
	private $providers = [];

	/**
	 * @var Flysystem\MountManager
	 */
	private $mountManager;

	/**
	 * @param Flysystem\MountManager $mountManager
	 */
	public function __construct(
		Flysystem\MountManager $mountManager
	) {
		$this->mountManager = $mountManager;
	}

	/**
	 * @param array $arguments
	 *
	 * @return string
	 * 
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function request(array $arguments) : string
	{
		if (!isset($arguments['provider']) || $arguments['provider'] === NULL) {
			throw new Exceptions\InvalidArgumentException('Please provide image provider name.');
		}

		if (!isset($arguments['storage']) || $arguments['storage'] === NULL) {
			throw new Exceptions\InvalidArgumentException('Please provide image storage name.');
		}

		if (!isset($arguments['filename']) || $arguments['filename'] === NULL) {
			throw new Exceptions\InvalidArgumentException('Please provide filename.');
		}

		return $this->getProvider($arguments['provider'])->request(
			$arguments['storage'],
			$arguments['namespace'],
			$arguments['filename'],
			$arguments['size'],
			$arguments['algorithm']
		);
	}

	/**
	 * @param string $name
	 *
	 * @return Flysystem\FilesystemInterface
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function getStorage(string $name) : Flysystem\FilesystemInterface
	{
		try {
			return $this->mountManager->getFilesystem($name);

		} catch (\LogicException $ex) {
			throw new Exceptions\InvalidArgumentException(sprintf('Images storage: "%s" is not registered.', $name));
		}
	}

	/**
	 * @param string $name
	 *
	 * @return Providers\IProvider
	 * 
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function getProvider(string $name) : Providers\IProvider
	{
		if (isset($this->providers[$name])) {
			return $this->providers[$name];
		}

		throw new Exceptions\InvalidArgumentException(sprintf('Image provider "%s" is not registered.', $name));
	}

	/**
	 * @param string $name
	 * @param Providers\IProvider $provider
	 */
	public function registerProvider(string $name, Providers\IProvider $provider)
	{
		$this->providers[$name] = $provider;
	}

	/**
	 * @return Templating\Helpers
	 */
	public function createTemplateHelpers()
	{
		return new Templating\Helpers($this);
	}
}
