<?php
/**
 * Macros.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Images!
 * @subpackage     Latte
 * @since          1.0.0
 *
 * @date           05.04.14
 */

declare(strict_types = 1);

namespace IPub\Images\Latte;

use Nette;

use Latte;
use Latte\Compiler;
use Latte\MacroNode;
use Latte\PhpWriter;
use Latte\Macros\MacroSet;

use IPub;
use IPub\Images;
use IPub\Images\Helpers;

/**
 * Latte macros
 *
 * @package        iPublikuj:Images!
 * @subpackage     Latte
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Macros extends MacroSet
{
	/**
	 * Register latte macros
	 *
	 * @param Compiler $compiler
	 *
	 * @return static
	 */
	public static function install(Compiler $compiler)
	{
		$me = new static($compiler);

		/**
		 * {src provider:storage://[namespace/$name[, $width, $height[, $algorithm]]}
		 */
		self::registerMacro('src', $me);
		self::registerMacro('img', $me);

		return $me;
	}

	/**
	 * @param MacroNode $node
	 * @param PhpWriter $writer
	 *
	 * @return string
	 */
	public function macroSrc(MacroNode $node, PhpWriter $writer) : string
	{
		$arguments = self::prepareMacroArguments($node->args);

		$arguments = implode(',', array_map(function ($value, $key) {
			return '"' . $key . '" => '. ($value ? '"' . $value . '"' : 'NULL');
		}, array_values($arguments), array_keys($arguments)));

		return $writer->write('echo %escape(
			property_exists($this, \'filters\') ?
				call_user_func($this->filters->imageLink, array(' . $arguments . ')) : 
				call_user_func_array([$template, \'imageLink\'], array(' . $arguments . '))
		)');
	}

	/**
	 * @param string $macro
	 *
	 * @return array
	 */
	public static function prepareMacroArguments(string $macro) : array
	{
		$arguments = array_map(function ($value) {
			return trim($value);
		}, explode(',', $macro));

		$arguments = array_merge(
			Helpers\Converters::parseImageString($arguments[0]),
			[
				'size'      => (isset($arguments[1]) && !empty($arguments[1])) ? $arguments[1] : NULL,
				'algorithm' => (isset($arguments[2]) && !empty($arguments[2])) ? $arguments[2] : NULL,
			]
		);

		return $arguments;
	}

	/**
	 * @param string $name
	 * @param Macros $macros
	 */
	private static function registerMacro(string $name, Macros $macros)
	{
		$macros->addMacro($name, function (MacroNode $node, PhpWriter $writer) use ($macros) {
			return $macros->macroSrc($node, $writer);
		}, NULL, function (MacroNode $node, PhpWriter $writer) use ($macros) {
			return ' ?> ' . ($node->htmlNode->name === 'a' ? 'href' : 'src') . '="<?php ' . $macros->macroSrc($node, $writer) . ' ?>"<?php ';
		});
	}
}
