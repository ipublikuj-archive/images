<?php
/**
 * Test: IPub\Images\RouterFactory
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec https://www.ipublikuj.eu
 * @package        iPublikuj:Images!
 * @subpackage     Tests
 * @since          2.0.0
 *
 * @date           10.08.16
 */

declare(strict_types = 1);

namespace IPubTests\Images;

use Nette;
use Nette\Application\Routers;

class RouterFactory
{
	/**
	 * @throws \LogicException
	 */
	final public function __construct()
	{
		throw new \LogicException('Class ' . get_class($this) . ' is static and cannot be instantiated.');
	}

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter() : Nette\Application\IRouter
	{
		$router = new Routers\RouteList;

		$router[] = new Routers\Route('<presenter>/<action>[/<id>]', 'Homepage:default');

		return $router;
	}
}
