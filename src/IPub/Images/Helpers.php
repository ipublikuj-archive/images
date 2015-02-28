<?php
/**
 * Helpers.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Images!
 * @subpackage	common
 * @since		5.0
 *
 * @date		28.02.15
 */

namespace IPub\Images;

use Nette;
use Nette\Application;

class Helpers
{
	public static function prependRoute(Application\Routers\RouteList $router, Application\IRouter $route)
	{
		$router[] = $route;
		$lastKey = count($router) - 1;

		foreach ($router as $i => $r) {
			if ($i === $lastKey) {
				break;
			}

			$router[$i + 1] = $r;
		}

		$router[0] = $route;
	}
}