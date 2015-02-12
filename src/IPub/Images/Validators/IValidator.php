<?php
/**
 * IValidator.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Images!
 * @subpackage	Validators
 * @since		5.0
 *
 * @date		11.02.15
 */

namespace IPub\Images\Validators;

interface IValidator
{
	/**
	 * @param int $width
	 * @param int $height
	 * @param int $algorithm
	 *
	 * @return bool
	 */
	public function validate($width, $height, $algorithm);
}