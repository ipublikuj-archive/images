<?php
/**
 * DefaultStorage.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Images!
 * @subpackage	Storage
 * @since		5.0
 *
 * @date		09.02.15
 */

namespace IPub\Images\Storage;

use Nette;

interface IStorage
{
	/**
	 * @return string
	 */
	public function __toString();
}