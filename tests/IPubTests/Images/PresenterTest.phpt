<?php
/**
 * Test: IPub\Images\Presenter
 * @testCase
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Images!
 * @subpackage     Tests
 * @since          1.0.0
 *
 * @date           28.02.15
 */

namespace IPubTests\Images;

use Nette;
use Nette\Application;
use Nette\Application\UI;

use Tester;
use Tester\Assert;

use IPub;
use IPub\Images;

use League\Flysystem;

require_once __DIR__ . '/TestCase.php';

class PresenterTest extends TestCase
{
	public function testRendering()
	{
		/** @var Images\ImagesLoader $loader */
		$loader = $this->container->getService('images.loader');
		/** @var Flysystem\FilesystemInterface $storage */
		$storage = $loader->getStorage('default');

		$filePath = __DIR__ . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'ipublikuj-logo-large.png';

		/** Upload image to storage */
		$storage->write('ipublikuj-logo-large.png', file_get_contents($filePath));

		// Create test presenter
		$presenter = $this->createPresenter();

		// Create GET request
		$request = new Application\Request('Test', 'GET', ['action' => 'default']);
		// & fire presenter & catch response
		$response = $presenter->run($request);

		Assert::true($response instanceof Nette\Application\Responses\TextResponse);
		Assert::true($response->getSource() instanceof Nette\Application\UI\ITemplate);

		$dq = Tester\DomQuery::fromHtml((string) $response->getSource());

		Assert::true($dq->has('a'));

		// Get all <img /> elements
		$imgElements = $dq->find('img');

		// Get all <a /> elements
		$aElements = $dq->find('a');

		Assert::same('http:///images/original/ipublikuj-logo-large.png?storage=default', (string) $aElements[0]->attributes()->{'href'});
		Assert::same('http:///images/50x50-4/ipublikuj-logo-large.png?storage=default', (string) $imgElements[0]->attributes()->{'src'});
	}

	public function tearDown()
	{
		parent::tearDown();

		/** @var Images\ImagesLoader $loader */
		$loader = $this->container->getService('images.loader');
		/** @var Flysystem\FilesystemInterface $storage */
		$storage = $loader->getStorage('default');

		if ($storage->has('ipublikuj-logo-large.png')) {
			$storage->delete('ipublikuj-logo-large.png');
		}
	}
}

\run(new PresenterTest());
