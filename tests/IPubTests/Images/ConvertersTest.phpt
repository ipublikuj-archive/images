<?php
/**
 * Test: IPub\Images\Converters
 * @testCase
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Images!
 * @subpackage     Tests
 * @since          2.0.0
 *
 * @date           13.05.16
 */

namespace IPubTests\Images;

use Nette;
use Nette\Utils;

use Tester;
use Tester\Assert;

use IPub;
use IPub\Images;
use IPub\Images\Helpers;

require __DIR__ . '/../bootstrap.php';

class ConvertersTest extends Tester\TestCase
{
	/**
	 * @return array[]|array
	 */
	public function dataStringToSize()
	{
		return [
			['original', [NULL, NULL]],
			['50x50', [50, 50]],
			['50', [50, 0]],
			['something', [0, 0]],
		];
	}

	/**
	 * @return array[]|array
	 */
	public function dataSizeToString()
	{
		return [
			[NULL, 'original'],
			['50x50', '50x50'],
			['50', 50],
			['50x', 50],
		];
	}

	/**
	 * @return array[]|array
	 */
	public function dataStringToAlgorithm()
	{
		return [
			['', NULL],
			[NULL, NULL],
			['fit', Utils\Image::FIT],
			['exact', Utils\Image::EXACT],
			['unknown', NULL],
			[Utils\Image::FILL, Utils\Image::FILL],
			[50, NULL],
		];
	}

	/**
	 * @return array[]|array
	 */
	public function dataAlgorithmToString()
	{
		return [
			[Utils\Image::FIT, 'fit'],
			[Utils\Image::SHRINK_ONLY, 'shrink-only'],
			['fill', 'fill'],
			['notKnown', NULL],
			['shrink_only', 'shrink-only'],
			['shrinkonly', 'shrink-only'],
			[50, NULL],
		];
	}

	/**
	 * @return array[]|array
	 */
	public function dataParseImageString()
	{
		return [
			['presenter:filesystem://namespace/file.jpg', [
				'provider'  => 'presenter',
				'storage'   => 'filesystem',
				'namespace' => 'namespace',
				'filename'  => 'file.jpg',
			]],
			['presenter:filesystem://file.jpg', [
				'provider'  => 'presenter',
				'storage'   => 'filesystem',
				'namespace' => NULL,
				'filename'  => 'file.jpg',
			]],
			['filesystem://namespace/file.jpg', [
				'provider'  => NULL,
				'storage'   => 'filesystem',
				'namespace' => 'namespace',
				'filename'  => 'file.jpg',
			]],
			['filesystem://file.jpg', [
				'provider'  => NULL,
				'storage'   => 'filesystem',
				'namespace' => NULL,
				'filename'  => 'file.jpg',
			]],
		];
	}

	/**
	 * @dataProvider dataStringToSize
	 *
	 * @param string $string
	 * @param array $expected
	 */
	public function testParseSizeString(string $string, array $expected)
	{
		Assert::same($expected, Helpers\Converters::parseSizeString($string));
	}

	/**
	 * @dataProvider dataSizeToString
	 *
	 * @param string|NULL $size
	 * @param string|int $expected
	 */
	public function testCreateSizeString(string $size = NULL, $expected)
	{
		Assert::same($expected, Helpers\Converters::createSizeString($size));
	}

	/**
	 * @dataProvider dataStringToAlgorithm
	 *
	 * @param string|int|NULL $algorithm
	 * @param string|int $expected
	 */
	public function testParseAlgorithm($algorithm = NULL, $expected = NULL)
	{
		Assert::same($expected, Helpers\Converters::parseAlgorithm($algorithm));
	}

	/**
	 * @dataProvider dataAlgorithmToString
	 *
	 * @param string $algorithm
	 * @param string $expected
	 */
	public function testCreateAlgorithmString(string $algorithm, string $expected = NULL)
	{
		Assert::same($expected, Helpers\Converters::createAlgorithmString($algorithm));
	}

	/**
	 * @dataProvider dataParseImageString
	 *
	 * @param string $file
	 * @param array $expected
	 */
	public function testParseImageString(string $file, array $expected)
	{
		Assert::same($expected, Helpers\Converters::parseImageString($file));
	}
}

\run(new ConvertersTest());
