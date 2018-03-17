<?php
/**
 * Test: IPub\Images\Templating
 * @testCase
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Images!
 * @subpackage     Tests
 * @since          2.0.0
 *
 * @date           13.05.16
 */

declare(strict_types = 1);

namespace IPubTests\Images;

use Nette;

use Tester;
use Tester\Assert;

use IPub\Images;
use IPub\Images\Templating;

require __DIR__ . '/../bootstrap.php';

class TemplatingTest extends Tester\TestCase
{
	public function testTemplateHelpers() : void
	{
		$dic = $this->createContainer();

		/** @var Templating\Helpers $helpers */
		$helpers = $dic->getService('images.helpers');

		Assert::true($helpers->isSquare('default://ipublikuj-logo-large.png'));
		Assert::false($helpers->isHigher('default://ipublikuj-logo-large.png'));
		Assert::false($helpers->isWider('default://ipublikuj-logo-large.png'));
	}

	/**
	 * @return Nette\DI\Container
	 */
	protected function createContainer() : Nette\DI\Container
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

\run(new TemplatingTest());
