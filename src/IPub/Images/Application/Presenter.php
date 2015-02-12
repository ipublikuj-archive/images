<?php
/**
 * Presenter.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Images!
 * @subpackage	Application
 * @since		5.0
 *
 * @date		09.02.15
 */

namespace IPub\IPubModule;

use Nette;
use Nette\Application;
use Nette\Http;
use Nette\Utils;

use IPub;
use IPub\Images;

class ImagesPresenter extends Nette\Object implements Application\IPresenter
{
	/**
	 * @var Http\IRequest
	 */
	private $httpRequest;

	/**
	 * @var Application\IRouter
	 */
	private $router;

	/**
	 * @var Application\Request
	 */
	private $request;

	/**
	 * @var Images\Generator
	 */
	private $generator;

	/**
	 * @param Images\Generator $generator
	 * @param Http\IRequest $httpRequest
	 * @param Application\IRouter $router
	 */
	public function __construct(
		Images\Generator $generator,
		Http\IRequest $httpRequest = NULL,
		Application\IRouter $router = NULL
	) {
		$this->generator	= $generator;
		$this->httpRequest	= $httpRequest;
		$this->router		= $router;
	}

	/**
	 * @param Application\Request $request
	 *
	 * @return Application\IResponse
	 *
	 * @throws Application\BadRequestException
	 */
	public function run(Application\Request $request)
	{
		$this->request = $request;

		if ($this->httpRequest && $this->router && !$this->httpRequest->isAjax() && ($request->isMethod('get') || $request->isMethod('head'))) {
			$refUrl = clone $this->httpRequest->getUrl();

			$url = $this->router->constructUrl($request, $refUrl->setPath($refUrl->getScriptPath()));

			if ($url !== NULL && !$this->httpRequest->getUrl()->isEqual($url)) {
				return new Application\Responses\RedirectResponse($url, Http\IResponse::S301_MOVED_PERMANENTLY);
			}
		}

		$params = $request->getParameters();

		if (!isset($params['storage'])) {
			throw new Application\BadRequestException('Parameter storage is missing.');
		}

		if (!isset($params['filename'])) {
			throw new Application\BadRequestException('Parameter filename is missing.');
		}

		if (!isset($params['extension'])) {
			throw new Application\BadRequestException('Parameter extension is missing.');
		}

		call_user_func_array([$this->generator, 'generateImage'], [$params['namespace'], $params['size'], $params['filename'], $params['extension'], $params['algorithm'], $params['storage']]);
	}
}