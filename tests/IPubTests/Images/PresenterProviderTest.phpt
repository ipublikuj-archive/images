<?php
/**
 * Test: IPub\Images\Providers
 * @testCase
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Images!
 * @subpackage     Tests
 * @since          2.0.0
 *
 * @date           12.05.15
 */

namespace IPubTests\Images;

use Nette;

use Tester;
use Tester\Assert;

use IPub;
use IPub\Images;
use IPub\Images\Providers;

use League\Flysystem;

require_once __DIR__ . '/TestCase.php';

class PresenterProviderTest extends TestCase
{
	public function testRegisteringProviders()
	{
		$provider = $this->container->getService('images.providers.presenter');

		Assert::true($provider instanceof Providers\PresenterProvider);

		/** @var Images\ImagesLoader $loader */
		$loader = $this->container->getService('images.loader');

		Assert::true($loader instanceof IPub\Images\ImagesLoader);
		Assert::true($loader->getProvider('presenter') instanceof Providers\PresenterProvider);
	}

	public function testPresenterProvider()
	{
		/** @var Images\ImagesLoader $loader */
		$loader = $this->container->getService('images.loader');
		/** @var Providers\PresenterProvider $provider */
		$provider = $loader->getProvider('presenter');
		/** @var Flysystem\FilesystemInterface $storage */
		$storage = $loader->getStorage('default');

		$filePath = __DIR__ . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'ipublikuj-logo-large.png';

		/** Upload image to storage */
		$storage->write('logo/ipublikuj-logo-large.png', file_get_contents($filePath));

		$url = $provider->request('default', 'logo', 'ipublikuj-logo-large.png', '50x50');
		Assert::same('/images/logo/50x50/ipublikuj-logo-large.png?storage=default', $url);
		$url = $provider->request('default', 'logo', 'ipublikuj-logo-large.png', '120x120');
		Assert::same('/images/logo/120x120/ipublikuj-logo-large.png?storage=default', $url);
		$url = $provider->request('default', 'logo', 'ipublikuj-logo-large.png', '50x50', 'fit');
		Assert::same('/images/logo/50x50-fit/ipublikuj-logo-large.png?storage=default', $url);
	}
}

\run(new PresenterProviderTest());
