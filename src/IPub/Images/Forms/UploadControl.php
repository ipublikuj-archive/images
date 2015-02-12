<?php
/**
 * UploadControl.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Images!
 * @subpackage	Controls
 * @since		5.0
 *
 * @date		06.04.14
 */

namespace IPub\Images\Forms;

use Nette\Http;
use Nette\Forms;
use Nette\Utils;
use Nette\InvalidStateException;

class UploadControl extends \Nette\Forms\Controls\BaseControl
{
	/**
	 * @var array of function (UploadControl $control, Http\FileUpload[] $files)
	 */
	public $onUpload = array();

	/**
	 * @var Http\Request
	 */
	private $httpRequest;

	/**
	 * @var Http\Response
	 */
	private $httpResponse;

	/**
	 * @param NULL|string $label
	 */
	public function __construct($label = NULL)
	{
		parent::__construct($label);

		$this->monitor('Nette\Application\UI\Presenter');
		$this->control->type = 'file';
	}

	/**
	 * @param \Nette\ComponentModel\Container $parent
	 *
	 * @throws \Nette\InvalidStateException
	 *
	 * @return void
	 */
	protected function attached($parent)
	{
		if ($parent instanceof Forms\Form) {
			if ($parent->getMethod() !== Forms\Form::POST) {
				throw new InvalidStateException('File upload requires method POST.');
			}

			$parent->getElementPrototype()->enctype = 'multipart/form-data';

		} else if ($parent instanceof \Nette\Application\UI\Presenter) {
			if (!$this->httpRequest) {
				$this->httpRequest	= $parent->getContext()->httpRequest;
				$this->httpResponse	= $parent->getContext()->httpResponse;
			}
		}

		parent::attached($parent);
	}

	/**
	 * @return $this
	 */
	public function allowMultiple()
	{
		$this->control->multiple = TRUE;

		return $this;
	}

	/**
	 * Sets control's value.
	 *
	 * @param  array|Http\FileUpload
	 *
	 * @return $this
	 */
	public function setValue($value)
	{
		if (is_array($value)) {
			if (Utils\Validators::isList($value)) {
				foreach ($value as $i => $file) {
					$this->value[$i] = $file instanceof Http\FileUpload ? $file : new Http\FileUpload($file);
				}

			} else {
				$this->value = array(new Http\FileUpload($value));
			}

		} else if ($value instanceof Http\FileUpload) {
			$this->value = array($value);

		} else {
			$this->value = new Http\FileUpload(NULL);
		}

		return $this;
	}

	/**
	 *
	 */
	public function loadHttpData()
	{
		$this->value = $this->getHttpData(\Nette\Forms\Form::DATA_FILE);

		if ($this->value === NULL) {
			$this->value = new Http\FileUpload(NULL);
		}

		if ($this->value) {
			$this->onUpload($this, $this->value);
		}
	}

	/**
	 * @return string
	 */
	public function getHtmlName()
	{
		return parent::getHtmlName() . ($this->control->multiple ? '[]' : '');
	}

	/**
	 * @return \Nette\Utils\Html
	 */
	public function getControl()
	{
		return parent::getControl()->data('url', $this->form->action);
	}

	/**
	 * Has been any file uploaded?
	 *
	 * @return bool
	 */
	public function isFilled()
	{
		foreach ((array) $this->value as $file) {
			if (!$file instanceof Http\FileUpload || !$file->isOK()) {
				return FALSE;
			}
		}

		return (bool) $this->value;
	}

	/**
	 * Image validator: is file image?
	 *
	 * @param UploadControl $control
	 *
	 * @return bool
	 */
	public static function validateImage(UploadControl $control)
	{
		foreach ((array) $control->value as $file) {
			if (!$file instanceof Http\FileUpload || !$file->isImage()) {
				return FALSE;
			}
		}

		return (bool) $control->value;
	}

	/**
	 * @param string $method
	 */
	public static function register($method = 'addImageUpload')
	{
		$class = function_exists('get_called_class')?get_called_class():__CLASS__;
		\Nette\Forms\Container::extensionMethod(
			$method, function (\Nette\Forms\Container $container, $name, $label = NULL) use ($class) {
				return $container[$name] = new $class($label);
			}
		);
	}
}