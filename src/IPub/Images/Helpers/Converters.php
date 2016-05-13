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

			settype($width, 'int');
			settype($height, 'int');

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

		} elseif (!is_int($algorithm) && !is_array($algorithm)) {
			switch (strtolower($algorithm)) {
				case 'fit':
					return Utils\Image::FIT;

				case 'fill':
					return Utils\Image::FILL;

				case 'exact':
					return Utils\Image::EXACT;

				case 'shrink_only':
				case 'shrinkonly':
				case 'shrink-only':
					return Utils\Image::SHRINK_ONLY;

				case 'stretch':
					return Utils\Image::STRETCH;

				default:
					return NULL;
			}

		} elseif (is_int($algorithm)) {
			if (!in_array($algorithm, [Utils\Image::FIT, Utils\Image::FILL, Utils\Image::EXACT, Utils\Image::SHRINK_ONLY, Utils\Image::STRETCH])) {
				return NULL;
			}

		} else {
			return NULL;
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
					return 'fit';

				case Utils\Image::FILL:
					return 'fill';

				case Utils\Image::EXACT:
					return 'exact';

				case Utils\Image::SHRINK_ONLY:
					return 'shrink-only';

				case Utils\Image::STRETCH:
					return 'stretch';

				default:
					return NULL;
			}

		} elseif (is_string($algorithm)) {
			$algorithm = strtolower($algorithm);

			if (in_array($algorithm, ['shrink_only', 'shrinkonly', 'shrink-only'])) {
				return 'shrink-only';

			} elseif (!in_array($algorithm, ['fit', 'fill', 'exact', 'stretch'])) {
				return NULL;
			}
		}

		return $algorithm;
	}
}
