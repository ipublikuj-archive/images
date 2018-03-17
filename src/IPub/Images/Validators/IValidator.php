<?php
/**
 * IValidator.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec https://www.ipublikuj.eu
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
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IValidator
{
	/**
	 * @param int|NULL $width
	 * @param int|NULL $height
	 * @param int|NULL $algorithm
	 *
	 * @return bool
	 */
	public function validate(?int $width, ?int $height, ?int $algorithm) : bool;
}
