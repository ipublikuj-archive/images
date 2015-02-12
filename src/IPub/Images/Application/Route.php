<?php
/**
 * Route.php
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

namespace IPub\Images\Application;

use Nette;
use Nette\Application;
use Nette\Utils;

use IPub;
use IPub\Images;

class Route extends Application\Routers\Route
{
	public function __construct($mask, $metadata = [], $flags = 0)
	{
		// Define micromodule presenter
		$metadata['presenter'] = 'IPub:Images';

		parent::__construct($mask, $metadata, $flags);
	}

	/**
	 * @param Application\IRouter $router
	 * @param Route $extensionRoute
	 *
	 * @throws Utils\AssertionException
	 */
	public static function prependTo(Application\IRouter &$router, self $extensionRoute)
	{
		if (!$router instanceof Application\Routers\RouteList) {
			throw new Utils\AssertionException(
				'If you want to use IPub\Images then your main router '.
				'must be an instance of Nette\Application\Routers\RouteList'
			);
		}

		// Add extension route to router
		$router[] = $extensionRoute;

		$lastKey = count($router) - 1;

		foreach ($router as $i => $route) {
			if ($i === $lastKey) {
				break;
			}

			$router[$i+1] = $route;
		}

		$router[0] = $extensionRoute;
	}
}