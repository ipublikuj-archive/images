<?php
/**
 * ImageExtension.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Images!
 * @subpackage     DI
 * @since          1.0.0
 *
 * @date           05.04.14
 */

namespace IPub\Images\DI;

use Nette;
use Nette\DI;
use Nette\Utils;
use Nette\PhpGenerator as Code;

use IPub;
use IPub\Images;
use IPub\Images\Application;
use IPub\Images\Storage;
use IPub\Images\Templating;
use IPub\Images\Validators;

use IPub\IPubModule;

/**
 * Images extension container
 *
 * @package        iPublikuj:Images!
 * @subpackage     DI
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class ImagesExtension extends DI\CompilerExtension
{
	// Define tag string for router services
	const TAG_EXTENSION_ROUTES = 'ipub.images.routes';

	/**
	 * @var array
	 */
	private $defaults = [
		'routes'                => [],
		'providers' => [
			'presenter' => Images\Providers\PresenterProvider::CLASS_NAME
		],
		'prependRoutesToRouter' => TRUE,
		'rules'                 => [],
		'wwwDir'                => NULL,
	];

	public function loadConfiguration()
	{
		// Get container builder
		$builder = $this->getContainerBuilder();
		// Get extension configuration
		$configuration = $this->getConfig($this->defaults);

		// Check for valid values
		Utils\Validators::assert($configuration['wwwDir'], 'string', 'Web public dir');
		Utils\Validators::assert($configuration['providers'], 'array', 'Images providers');
		Utils\Validators::assert($configuration['routes'], 'array', 'Images routes');

		// Extension loader
		$loader = $builder->addDefinition($this->prefix('loader'))
			->setClass(Images\ImagesLoader::CLASS_NAME);

		// Images presenter
		$builder->addDefinition($this->prefix('presenter'))
			->setClass(IPubModule\ImagesPresenter::CLASS_NAME, [
				$configuration['wwwDir'],
			]);

		// Create default storage validator
		$validator = $builder->addDefinition($this->prefix('validator.default'))
			->setClass(Validators\Validator::CLASS_NAME);

		foreach ($configuration['rules'] as $rule) {
			// Check for valid rules values
			Utils\Validators::assert($rule['width'], 'int', 'Rule width');
			Utils\Validators::assert($rule['height'], 'int', 'Rule height');

			$validator->addSetup('$service->addRule(?, ?, ?, ?)', [
				$rule['width'],
				$rule['height'],
				isset($rule['algorithm']) ? $rule['algorithm'] : NULL,
				isset($rule['storage']) ? $rule['storage'] : NULL,
			]);
		}

		if ($configuration['routes']) {
			$router = $builder->addDefinition($this->prefix('router'))
				->setClass('Nette\Application\Routers\RouteList')
				->addTag($this->prefix('routeList'))
				->setAutowired(FALSE);

			$i = 0;

			foreach ($configuration['routes'] as $mask => $metadata) {
				if (!is_array($metadata)) {
					$mask = $metadata;
					$metadata = [];
				}

				$builder->addDefinition($this->prefix('route.' . $i))
					->setClass(Application\Route::CLASS_NAME, [$mask, $metadata])
					->setAutowired(FALSE)
					->addTag(self::TAG_EXTENSION_ROUTES)
					->setInject(FALSE);

				// Add route to router
				$router->addSetup('$service[] = ?', [
					$this->prefix('@route.' . $i),
				]);

				$i++;
			}
		}

		foreach ($configuration['providers'] as $name => $provider) {
			$this->compiler->parseServices($builder, [
				'services' => [$this->prefix('provider.' . $name) => $provider],
			]);
			$loader->addSetup('registerProvider', [$name, $this->prefix('@provider.' . $name)]);
		}

		// Update presenters mapping
		$builder->getDefinition('nette.presenterFactory')
			->addSetup('if (method_exists($service, ?)) { $service->setMapping([? => ?]); } '
				. 'elseif (property_exists($service, ?)) { $service->mapping[?] = ?; }',
				['setMapping', 'IPub', 'IPub\IPubModule\*\*Presenter', 'mapping', 'IPub', 'IPub\IPubModule\*\*Presenter']
			);

		// Register template helpers
		$builder->addDefinition($this->prefix('helpers'))
			->setClass(Templating\Helpers::CLASS_NAME)
			->setFactory($this->prefix('@loader') . '::createTemplateHelpers')
			->setInject(FALSE);
	}

	/**
	 * @inheritdoc
	 */
	public function beforeCompile()
	{
		parent::beforeCompile();

		// Get container builder
		$builder = $this->getContainerBuilder();
		// Get extension configuration
		$configuration = $this->getConfig($this->defaults);

		if ($configuration['prependRoutesToRouter']) {
			$router = $builder->getByType('Nette\Application\IRouter');

			if ($router !== NULL) {
				if (!$router instanceof DI\ServiceDefinition) {
					$router = $builder->getDefinition($router);
				}

			} else {
				$router = $builder->getDefinition('router');
			}

			foreach (array_keys($builder->findByTag(self::TAG_EXTENSION_ROUTES)) as $service) {
				$router->addSetup('IPub\Images\Application\Route::prependTo($service, ?)', ['@'. $service]);
			}
		}

		// Install extension latte macros
		$latteFactory = $builder->getDefinition($builder->getByType('\Nette\Bridges\ApplicationLatte\ILatteFactory') ?: 'nette.latteFactory');

		$latteFactory
			->addSetup('IPub\Images\Latte\Macros::install(?->getCompiler())', ['@self'])
			->addSetup('addFilter', ['isSquare', [$this->prefix('@helpers'), 'isSquare']])
			->addSetup('addFilter', ['isHigher', [$this->prefix('@helpers'), 'isHigher']])
			->addSetup('addFilter', ['isWider', [$this->prefix('@helpers'), 'isWider']])
			->addSetup('addFilter', ['getImagesLoaderService', [$this->prefix('@helpers'), 'getImagesLoaderService']]);
	}

	/**
	 * @param Nette\Configurator $configurator
	 * @param string $extensionName
	 */
	public static function register(Nette\Configurator $configurator, $extensionName = 'images')
	{
		$configurator->onCompile[] = function (Nette\Configurator $configurator, Nette\DI\Compiler $compiler) use ($extensionName) {
			$compiler->addExtension($extensionName, new ImagesExtension());
		};
	}
}
