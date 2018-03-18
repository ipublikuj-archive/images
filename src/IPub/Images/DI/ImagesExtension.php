<?php
/**
 * ImageExtension.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:Images!
 * @subpackage     DI
 * @since          1.0.0
 *
 * @date           05.04.14
 */

declare(strict_types = 1);

namespace IPub\Images\DI;

use Nette;
use Nette\DI;
use Nette\Utils;

use IPub\Images;
use IPub\Images\Application;
use IPub\Images\Exceptions;
use IPub\Images\Templating;
use IPub\Images\Validators;

use IPub\IPubModule;

/**
 * Images extension container
 *
 * @package        iPublikuj:Images!
 * @subpackage     DI
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class ImagesExtension extends DI\CompilerExtension
{
	// Define tag string for router services
	const TAG_IMAGES_ROUTES = 'ipub.images.routes';

	// Define tag string for providers services
	const TAG_IMAGES_PROVIDERS = 'ipub.images.providers';

	/**
	 * @var array
	 */
	private $defaults = [
		'routes'                => [],
		'presenterProvider'     => TRUE,
		'prependRoutesToRouter' => TRUE,
		'rules'                 => [],
		'wwwDir'                => NULL,
	];

	/**
	 * @return void
	 *
	 * @throws Exceptions\InvalidArgumentException
	 * @throws Utils\AssertionException
	 */
	public function loadConfiguration() : void
	{
		// Get container builder
		$builder = $this->getContainerBuilder();
		// Get extension configuration
		$configuration = $this->getExtensionConfig();

		// Check for valid values
		Utils\Validators::assert($configuration['routes'], 'array', 'Images routes');
		Utils\Validators::assert($configuration['rules'], 'array', 'Images rules');

		// Extension loader
		$builder->addDefinition($this->prefix('loader'))
			->setType(Images\ImagesLoader::class);

		// Create default storage validator
		$validator = $builder->addDefinition($this->prefix('validator.default'))
			->setType(Validators\Validator::class);

		$this->registerRules($configuration['rules'], $validator);

		if ($configuration['presenterProvider'] === TRUE) {
			$this->registerPresenter();
		}

		if ($configuration['routes']) {
			$this->registerRoutes($configuration['routes']);
		}

		// Register template helpers
		$builder->addDefinition($this->prefix('helpers'))
			->setType(Templating\Helpers::class)
			->setFactory($this->prefix('@loader') . '::createTemplateHelpers')
			->setInject(FALSE);
	}

	/**
	 * {@inheritdoc}
	 */
	public function beforeCompile() : void
	{
		parent::beforeCompile();

		// Get container builder
		$builder = $this->getContainerBuilder();
		// Get extension configuration
		$configuration = $this->getExtensionConfig();

		if ($configuration['prependRoutesToRouter']) {
			$router = $builder->getByType('Nette\Application\IRouter');

			if ($router !== NULL) {
				if (!$router instanceof DI\ServiceDefinition) {
					$router = $builder->getDefinition($router);
				}

			} else {
				$router = $builder->getDefinition('router');
			}

			foreach (array_keys($builder->findByTag(self::TAG_IMAGES_ROUTES)) as $service) {
				$router->addSetup('IPub\Images\Application\Route::prependTo($service, ?)', ['@' . $service]);
			}
		}

		// Get images loader service
		$loader = $builder->getDefinition($builder->getByType(Images\ImagesLoader::class));

		// Get all registered providers
		foreach ($builder->findByType(Images\Providers\IProvider::class) as $service) {
			// Register all images providers which are now allowed
			$loader->addSetup('$service->registerProvider(?->getName(), ?)', [$service, $service]);
		}

		// Install extension latte macros
		$latteFactory = $builder->getDefinition($builder->getByType('\Nette\Bridges\ApplicationLatte\ILatteFactory') ?: 'nette.latteFactory');

		$latteFactory
			->addSetup('IPub\Images\Latte\Macros::install(?->getCompiler())', ['@self'])
			->addSetup('addFilter', ['isSquare', [$this->prefix('@helpers'), 'isSquare']])
			->addSetup('addFilter', ['isHigher', [$this->prefix('@helpers'), 'isHigher']])
			->addSetup('addFilter', ['isWider', [$this->prefix('@helpers'), 'isWider']])
			->addSetup('addFilter', ['imageLink', [$this->prefix('@helpers'), 'imageLink']]);
	}

	/**
	 * @param Nette\Configurator $configurator
	 * @param string $extensionName
	 */
	public static function register(Nette\Configurator $configurator, string $extensionName = 'images') : void
	{
		$configurator->onCompile[] = function (Nette\Configurator $configurator, Nette\DI\Compiler $compiler) use ($extensionName) {
			$compiler->addExtension($extensionName, new ImagesExtension());
		};
	}

	/**
	 * @return void
	 *
	 * @throws Utils\AssertionException
	 */
	private function registerPresenter() : void
	{
		// Get container builder
		$builder = $this->getContainerBuilder();
		// Get extension configuration
		$configuration = $this->getExtensionConfig();

		// For this provider wwwDir have to be defined
		Utils\Validators::assert($configuration['routes'], 'array:1..', 'Images routes');
		Utils\Validators::assert($configuration['wwwDir'], 'string', 'Web public dir');

		// Presenter provider
		$builder->addDefinition($this->prefix('providers.presenter'))
			->setType(Images\Providers\PresenterProvider::class);

		// Images presenter
		$builder->addDefinition($this->prefix('presenter'))
			->setType(IPubModule\ImagesPresenter::class)
			->setArguments([
				$configuration['wwwDir'],
			]);

		// Update presenters mapping
		$builder->getDefinition('nette.presenterFactory')
			->addSetup('if (method_exists($service, ?)) { $service->setMapping([? => ?]); } '
				. 'elseif (property_exists($service, ?)) { $service->mapping[?] = ?; }',
				['setMapping', 'IPub', 'IPub\IPubModule\*\*Presenter', 'mapping', 'IPub', 'IPub\IPubModule\*\*Presenter']
			);
	}

	/**
	 * @param array $routes
	 *
	 * @return void
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	private function registerRoutes(array $routes = []) : void
	{
		// Get container builder
		$builder = $this->getContainerBuilder();

		$router = $builder->addDefinition($this->prefix('router'))
			->setType(Nette\Application\Routers\RouteList::class)
			->addTag($this->prefix('routeList'))
			->setAutowired(FALSE);

		$i = 0;

		foreach ($routes as $mask => $attributes) {
			$metadata = [];
			$flags = 0;

			if (is_array($attributes) && array_key_exists('route', $attributes)) {
				$mask = $attributes['route'];

				if (array_key_exists('metadata', $attributes)) {
					$metadata = $attributes['metadata'];
				}

			} elseif (is_int($mask) === TRUE) {
				$mask = $attributes;
			}

			if (empty($mask) || is_string($mask) === FALSE) {
				throw new Images\Exceptions\InvalidArgumentException('Provided route is not valid.');
			}

			$builder->addDefinition($this->prefix('route.' . $i))
				->setType(Application\Route::class)
				->setArguments([$mask, $metadata, $flags])
				->setAutowired(FALSE)
				->addTag(self::TAG_IMAGES_ROUTES)
				->setInject(FALSE);

			// Add route to router
			$router->addSetup('$service[] = ?', [
				$this->prefix('@route.' . $i),
			]);

			$i++;
		}
	}

	/**
	 * @param array $rules
	 * @param DI\ServiceDefinition $validator
	 *
	 * @return void
	 *
	 * @throws Utils\AssertionException
	 */
	private function registerRules(array $rules = [], DI\ServiceDefinition $validator) : void
	{
		foreach ($rules as $rule) {
			// Check for valid rules values
			Utils\Validators::assert($rule['width'], 'int|null', 'Rule width');
			Utils\Validators::assert($rule['height'], 'int|null', 'Rule height');

			$validator->addSetup('$service->addRule(?, ?, ?, ?)', [
				$rule['width'],
				$rule['height'],
				isset($rule['algorithm']) ? $rule['algorithm'] : NULL,
				isset($rule['storage']) ? $rule['storage'] : NULL,
			]);
		}
	}

	/**
	 * @return array
	 */
	private function getExtensionConfig() : array
	{
		return $this->getConfig($this->defaults);
	}
}
