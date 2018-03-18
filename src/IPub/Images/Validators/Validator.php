<?php
/**
 * Validator.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:Images!
 * @subpackage     Validators
 * @since          1.0.0
 *
 * @date           11.02.15
 */

declare(strict_types = 1);

namespace IPub\Images\Validators;

use Nette;

use IPub\Images\Exceptions;

/**
 * Image validator interface
 *
 * @package        iPublikuj:Images!
 * @subpackage     Validators
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @inspiredBy     dotBlue <http://dotblue.net>
 */
class Validator implements IValidator
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @var array[]
	 */
	private $rules = [];

	/**
	 * Adds rule
	 *
	 * @param int|NULL $width
	 * @param int|NULL $height
	 * @param int|string|NULL $algorithm
	 * @param string|NULL $storage
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function addRule(?int $width = NULL, ?int $height = NULL, $algorithm = NULL, ?string $storage = NULL)
	{
		if ($width === NULL && $height === NULL) {
			throw new Exceptions\InvalidArgumentException('Width or height have to be defined!');
		}

		$this->rules[] = [
			'width'     => $width ? (int) $width : NULL,
			'height'    => $height ? (int) $height : NULL,
			'algorithm' => $algorithm === NULL ? NULL : (string) $algorithm,
			'storage'   => $storage === NULL ? NULL : $storage,
		];
	}

	/**
	 * Validates whether provided arguments match at least one rule
	 *
	 * @param int|NULL $width
	 * @param int|NULL $height
	 * @param int $algorithm
	 * @param string|NULL $storage
	 *
	 * @return bool
	 */
	public function validate(?int $width = NULL, ?int $height = NULL, ?int $algorithm = NULL, ?string $storage = NULL) : bool
	{
		if (!count($this->rules)) {
			return TRUE;
		}

		foreach ($this->rules as $rule) {
			if ($rule['storage'] !== NULL && $rule['storage'] !== $storage) {
				continue;
			}

			if (($width === $rule['width'] || $rule['width'] === NULL) && ($height === $rule['height'] || $rule['height'] === NULL)) {
				if ($rule['algorithm'] !== NULL && $rule['algorithm'] !== $algorithm) {
					continue;
				}

				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * Returns all added rules
	 *
	 * @return array[]
	 */
	public function getRules() : array
	{
		return $this->rules;
	}
}
