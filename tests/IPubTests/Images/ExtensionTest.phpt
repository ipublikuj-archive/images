<?php
/**
 * Test: IPub\Images\Extension
 * @testCase
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Images!
 * @subpackage	Tests
 * @since		5.0
 *
 * @date		28.02.15
 */

namespace IPubTests\Images;

use Nette;

use Tester;
use Tester\Assert;

use IPub;
use IPub\Images;

require __DIR__ . '/../bootstrap.php';

class ExtensionTest extends Tester\TestCase
{
	/**
	 * @return \SystemContainer|\Nette\DI\Container
	 */
	protected function createContainer()
	{
		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);

		$config->addParameters([
			"wwwDir" => realpath(__DIR__ . DIRECTORY_SEPARATOR .'web'),
			"uploadDir" => realpath(__DIR__ . DIRECTORY_SEPARATOR .'upload'),
		]);

		Images\DI\ImagesExtension::register($config);

		$config->addConfig(__DIR__ . '/files/config.neon', $config::NONE);

		return $config->createContainer();
	}

	public function testCompilersServices()
	{
		$dic = $this->createContainer();

		Assert::true($dic->getService('images.loader') instanceof IPub\Images\ImagesLoader);
		Assert::true($dic->getService('images.validator.default') instanceof IPub\Images\Validators\Validator);
	}
}

\run(new ExtensionTest());