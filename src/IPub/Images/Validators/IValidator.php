<?php
/**
 * IValidator.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Images!
 * @subpackage     Validators
 * @since          1.0.0
 *
 * @date           11.02.15
 */

declare(strict_types = 1);

namespace IPub\Images\Validators;

/**
 * Image validator interface
 *
 * @package        iPublikuj:Images!
 * @subpackage     Validators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IValidator
{
	/**
	 * @param int $width
	 * @param int $height
	 * @param int $algorithm
	 *
	 * @return bool
	 */
	public function validate(int $width, int $height, int $algorithm) : bool;
}
