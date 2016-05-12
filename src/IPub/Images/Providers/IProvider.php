<?php
/**
 * IProvider.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Images!
 * @subpackage     Providers
 * @since          2.0.0
 *
 * @date           12.05.16
 */

namespace IPub\Images\Providers;

use IPub;
use IPub\Images;
use IPub\Images\Exceptions;

/**
 * Image provider interface
 *
 * @package        iPublikuj:Images!
 * @subpackage     Providers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IProvider
{
	/**
	 * @param string $storage
	 * @param string $namespace
	 * @param string $filename
	 * @param string|NULL $size
	 * @param string|NULL $algorithm
	 *
	 * @return string
	 * 
	 * @throws Exceptions\InvalidArgumentException
	 * @throws Exceptions\InvalidStateException
	 * @throws Exceptions\FileNotFoundException
	 */
	public function request($storage, $namespace, $filename, $size = NULL, $algorithm = NULL);
}
