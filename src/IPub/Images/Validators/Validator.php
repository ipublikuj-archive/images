<?php
/**
 * Validator.php
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

namespace IPub\Images\Validators;

use Nette;

/**
 * Image validator interface
 *
 * @package        iPublikuj:Images!
 * @subpackage     Validators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @inspiredBy     dotBlue <http://dotblue.net>
 */
class Validator extends Nette\Object implements IValidator
{
	/**
	 * Define class name
	 */
	const CLASS_NAME = __CLASS__;

	/**
	 * @var array[]
	 */
	private $rules = [];

	/**
	 * Adds rule
	 *
	 * @param int $width
	 * @param int $height
	 * @param int|string|NULL $algorithm
	 * @param string|NULL $storage
	 */
	public function addRule($width, $height, $algorithm = NULL, $storage = NULL)
	{
		$this->rules[] = [
			'width'     => (int) $width,
			'height'    => (int) $height,
			'algorithm' => $algorithm === NULL ? NULL : (string) $algorithm,
			'storage'   => $storage === NULL ? NULL : (string) $storage,
		];
	}

	/**
	 * Validates whether provided arguments match at least one rule
	 *
	 * @param int $width
	 * @param int $height
	 * @param int $algorithm
	 * @param string|NULL $storage
	 *
	 * @return bool
	 */
	public function validate($width, $height, $algorithm = NULL, $storage = NULL)
	{
		foreach ($this->rules as $rule) {
			if ($rule['storage'] !== NULL && $rule['storage'] !== $storage) {
				continue;
			}

			if (
				(int) $width !== $rule['width']
				|| (int) $height !== $rule['height']
				|| ($rule['algorithm'] !== NULL && $rule['algorithm'] !== $algorithm)
			) {
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * Returns all added rules
	 *
	 * @return array[]
	 */
	public function getRules()
	{
		return $this->rules;
	}
}
