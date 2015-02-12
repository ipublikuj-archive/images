<?php
/**
 * TImages.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Images!
 * @subpackage	common
 * @since		5.0
 *
 * @date		05.04.14
 */

namespace IPub\Images;

use Nette;

use IPub\Images\Templating;

trait TImages
{
	/**
	 * @var ImagesLoader
	 */
	public $imagesLoader;

	/**
	 * @var Templating\Helpers
	 */
	private $imgHelpers;

	/**
	 * @param Templating\Helpers $imgHelpers
	 */
	public function injectImgHelpers(Templating\Helpers $imgHelpers)
	{
		$this->imgHelpers = $imgHelpers;
	}
}