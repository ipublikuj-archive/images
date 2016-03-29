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

require_once __DIR__ . '/TestCase.php';

class PresenterTest extends TestCase
{
	public function testCssDefaultComponent()
	{
		// Create test presenter
		$presenter = $this->createPresenter();

		$loader = $this->container->getService('images.loader');

		/** @var Images\Storage\FileStorage $storage */
		$storage = $loader->getStorage('default');
		$storage->setPresenter($presenter);

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

		Assert::same('/images/original/ipublikuj-logo-large.png?storage=' . (string) $storage, (string) $aElements[0]->attributes()->{'href'});
		Assert::same('/images/50x50-4/ipublikuj-logo-large.png?storage=' . (string) $storage, (string) $imgElements[0]->attributes()->{'src'});
	}
}

\run(new PresenterTest());
