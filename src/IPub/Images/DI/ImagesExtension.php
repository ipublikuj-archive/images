<?php
/**
 * ImageExtension.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Images!
 * @subpackage	DI
 * @since		5.0
 *
 * @date		05.04.14
 */

namespace IPub\Images\DI;

use Nette;
use Nette\DI;
use Nette\PhpGenerator as Code;

use Tracy;

class ImagesExtension extends DI\CompilerExtension
{
	/**
	 * @var array
	 */
	private $defaults = [
		'storage' => [
			'default' => [
				'service'       => '@images.storage.default',
				'route'         => '/images[/<namespace .+>]/<size>[-<algorithm>]/<filename>.<extension>',
				'storageDir'    => '%wwwDir%/media',
				'rules'         => [],
			],
		],
		'wwwDir' => NULL,
	];

	public function loadConfiguration()
	{
		$config = $this->getConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		// Extension loader
		$loader = $builder->addDefinition($this->prefix('loader'))
			->setClass('IPub\Images\ImagesLoader');

		$builder->addDefinition($this->prefix('filesBrowser'))
			->setClass('IPub\Images\Files\Browser');

		// Images generator
		$builder->addDefinition($this->prefix('generator'))
			->setClass('IPub\Images\Generator', [
				$config['wwwDir'],
			]);

		// Create default storage
		$builder->addDefinition($this->prefix('storage.default'))
			->setClass('IPub\Images\Storage\DefaultStorage', [$config['storage']['default']['storageDir'], $this->prefix('@route.default')])
			->addSetup('$service->setWebDir(?)', [$config['wwwDir']]);

		// Create default storage validator
		$builder->addDefinition($this->prefix('validator.default'))
			->setClass('IPub\Images\Validators\DefaultValidator');

		foreach($config['storage'] as $storageName => $storageParams) {
			// Add storage to loader
			$loader->addSetup('registerStorage', [$storageParams['service']]);

			if ($storageParams['route']) {
				// Create storage route for images
				$builder->addDefinition($this->prefix('route.' . $storageName))
					->setClass('IPub\Images\Application\Route', [$storageParams['route']])
					->setAutowired(FALSE)
					->setInject(FALSE);

				// Add route to router
				$builder->getDefinition('router')
					->addSetup('IPub\Images\Application\Route::prependTo($service, ?)', [$this->prefix('@route.' . $storageName)]);
			}

			foreach ($storageParams['rules'] as $rule) {
				$builder->getDefinition(trim($storageParams['service'], '@'))
					->addSetup('$service->getValidator()->addRule(?, ?, ?)', [
						$rule['width'],
						$rule['height'],
						isset($rule['algorithm']) ? $rule['algorithm'] : NULL,
					]);
			}
		}

		// Update presenters mapping
		$builder->getDefinition('nette.presenterFactory')
			->addSetup('if (method_exists($service, ?)) { $service->setMapping([? => ?]); } '
				.'elseif (property_exists($service, ?)) { $service->mapping[?] = ?; }',
				['setMapping', 'IPub', 'IPub\IPubModule\*\*Presenter', 'mapping', 'IPub', 'IPub\IPubModule\*\*Presenter']
			);

		// Register template helpers
		$builder->addDefinition($this->prefix('helpers'))
			->setClass('IPub\Images\Templating\Helpers')
			->setFactory($this->prefix('@loader') . '::createTemplateHelpers')
			->setInject(FALSE);

		// Install extension latte macros
		$latteFactory = $builder->hasDefinition('nette.latteFactory')
			? $builder->getDefinition('nette.latteFactory')
			: $builder->getDefinition('nette.latte');

		$latteFactory
			->addSetup('IPub\Images\Latte\Macros::install(?->getCompiler())', array('@self'))
			->addSetup('addFilter', array('isSquare', array($this->prefix('@helpers'), 'isSquare')))
			->addSetup('addFilter', array('isHigher', array($this->prefix('@helpers'), 'isHigher')))
			->addSetup('addFilter', array('isWider', array($this->prefix('@helpers'), 'isWider')))
			->addSetup('addFilter', array('fromString', array($this->prefix('@helpers'), 'fromString')))
			->addSetup('addFilter', array('getImagesLoaderService', array($this->prefix('@helpers'), 'getImagesLoaderService')));
	}

	/**
	 * @param Code\ClassType $class
	 */
	public function afterCompile(Code\ClassType $class)
	{
		parent::afterCompile($class);

		$initialize = $class->methods['initialize'];
		$initialize->addBody('IPub\Images\Forms\UploadControl::register();');
	}

	/**
	 * @param Nette\Configurator $config
	 * @param string $extensionName
	 */
	public static function register(Nette\Configurator $config, $extensionName = 'images')
	{
		$config->onCompile[] = function (Nette\Configurator $config, Nette\DI\Compiler $compiler) use ($extensionName) {
			$compiler->addExtension($extensionName, new ImagesExtension());
		};
	}
}