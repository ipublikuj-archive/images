<?php
/**
 * Test: IPub\Images\Extension
 * @testCase
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:Images!
 * @subpackage     Tests
 * @since          1.0.0
 *
 * @date           28.02.15
 */

declare(strict_types = 1);

namespace IPubTests\Images;

use Nette;

use Tester;
use Tester\Assert;

use IPub\Images;

require __DIR__ . '/../bootstrap.php';

class ExtensionTest extends Tester\TestCase
{
	public function testCompilersServices() : void
	{
		$dic = $this->createContainer();

		Assert::true($dic->getService('images.loader') instanceof Images\ImagesLoader);
		Assert::true($dic->getService('images.providers.presenter') instanceof Images\Providers\PresenterProvider);
		Assert::true($dic->getService('images.validator.default') instanceof Images\Validators\Validator);
	}

	/**
	 * @return Nette\DI\Container
	 */
	protected function createContainer()
	{
		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);

		$config->addParameters([
			'wwwDir'   => realpath(__DIR__ . DIRECTORY_SEPARATOR . 'web'),
			'mediaDir' => realpath(__DIR__ . DIRECTORY_SEPARATOR . 'media'),
		]);

		Images\DI\ImagesExtension::register($config);

		$config->addConfig(__DIR__ . '/files/config.neon');

		return $config->createContainer();
	}
}

\run(new ExtensionTest());
