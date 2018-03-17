<?php
/**
 * Route.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec https://www.ipublikuj.eu
 * @package        iPublikuj:Images!
 * @subpackage     Application
 * @since          1.0.0
 *
 * @date           09.02.15
 */

declare(strict_types = 1);

namespace IPub\Images\Application;

use Nette\Application;
use Nette\Utils;

use IPub\Images;

/**
 * Micro-module router
 *
 * @package        iPublikuj:Images!
 * @subpackage     Application
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class Route extends Application\Routers\Route
{
	/**
	 * @param string $mask
	 * @param array $metadata
	 * @param int $flags
	 */
	public function __construct(string $mask, array $metadata = [], int $flags = 0)
	{
		// Define micro-module presenter
		$metadata['presenter'] = 'IPub:Images';

		parent::__construct($mask, $metadata, $flags);
	}

	/**
	 * @param Application\IRouter $router
	 * @param Route $extensionRoute
	 *
	 * @return void
	 *
	 * @throws Utils\AssertionException
	 */
	public static function prependTo(Application\IRouter &$router, self $extensionRoute) : void
	{
		if (!$router instanceof Application\Routers\RouteList) {
			throw new Utils\AssertionException(
				'If you want to use IPub\Images then your main router ' .
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

			$router[$i + 1] = $route;
		}

		$router[0] = $extensionRoute;
	}
}
