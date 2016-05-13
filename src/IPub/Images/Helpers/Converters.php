<?php
/**
 * Converters.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Images!
 * @subpackage     Helpers
 * @since          2.0.0
 *
 * @date           13.05.16
 */

namespace IPub\Images\Helpers;

use Nette;
use Nette\Utils;

/**
 * Attributes parsers
 *
 * @package        iPublikuj:Images!
 * @subpackage     Helpers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class Converters
{
	/**
	 * Parse size string into width and height
	 *
	 * @param string $size
	 *
	 * @return array
	 */
	public static function parseSizeString($size)
	{
		$width = $height = 0;

		$size = Utils\Strings::lower($size);

		// Extract size
		if (strpos($size, 'x') !== FALSE) {
			list($width, $height) = explode('x', $size);

		} elseif ($size !== 'original') {
			$width = (int) $size;

		} elseif ($size === 'original') {
			$width = $height = NULL;
		}

		return [$width, $height];
	}

	/**
	 * Create size string for provider eg.: original, 50x150, 50
	 *
	 * @param string|int|NULL $size
	 *
	 * @return int|string
	 */
	public static function createSizeString($size)
	{
		if (empty($size) || $size === NULL) {
			$size = 'original';

		} elseif (strpos($size, 'x') !== FALSE) {
			list($width, $height) = explode('x', $size);

			if ((int) $height > 0) {
				$size = (int) $width . 'x' . (int) $height;

			} else {
				$size = (int) $width;
			}

		} else {
			$size = (int) $size;
		}

		return $size;
	}

	/**
	 * @param string|int $algorithm
	 *
	 * @return int|NULL
	 */
	public static function parseAlgorithm($algorithm)
	{
		if (empty($algorithm) || $algorithm === NULL) {
			$algorithm = NULL;

		} elseif ($algorithm === NULL) {
			$algorithm = Utils\Image::FIT;

		} elseif (!is_int($algorithm) && !is_array($algorithm)) {
			switch (strtolower($algorithm)) {
				case 'fit':
					$algorithm = Utils\Image::FIT;
					break;

				case 'fill':
					$algorithm = Utils\Image::FILL;
					break;

				case 'exact':
					$algorithm = Utils\Image::EXACT;
					break;

				case 'shrink_only':
				case 'shrinkonly':
				case 'shrink-only':
					$algorithm = Utils\Image::SHRINK_ONLY;
					break;

				case 'stretch':
					$algorithm = Utils\Image::STRETCH;
					break;

				default:
					$algorithm = NULL;
			}

		} elseif (is_int($algorithm)) {
			if (!in_array($algorithm, [Utils\Image::FIT, Utils\Image::FILL, Utils\Image::EXACT, Utils\Image::SHRINK_ONLY, Utils\Image::STRETCH])) {
				$algorithm = NULL;
			}

		} else {
			$algorithm = NULL;
		}

		return $algorithm;
	}

	/**
	 * Convert algorithm to test representation
	 *
	 * @param int|string $algorithm
	 *
	 * @return string|NULL
	 */
	public static function createAlgorithmString($algorithm)
	{
		if (is_int($algorithm)) {
			switch ($algorithm) {
				case Utils\Image::FIT:
					$algorithm = 'fit';
					break;

				case Utils\Image::FILL:
					$algorithm = 'fill';
					break;

				case Utils\Image::EXACT:
					$algorithm = 'exact';
					break;

				case Utils\Image::SHRINK_ONLY:
					$algorithm = 'shrink-only';
					break;

				case Utils\Image::STRETCH:
					$algorithm = 'stretch';
					break;

				default:
					$algorithm = NULL;
			}

		} elseif (is_string($algorithm)) {
			$algorithm = strtolower($algorithm);

			if (in_array($algorithm, ['shrink_only', 'shrinkonly', 'shrink-only'])) {
				$algorithm = 'shrink-only';

			} elseif (!in_array($algorithm, ['fit', 'fill', 'exact', 'stretch'])) {
				$algorithm = NULL;
			}
		}

		return $algorithm;
	}
}
