<?php

namespace kartik\widgets;

use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\base\InvalidConfigException;
use yii\web\View;
use yii\web\JsExpression;

/**
 * DatePicker widget is a Yii2 wrapper for the Bootstrap DatePicker plugin. The
 * plugin is a fork of Stefan Petre's DatePicker (of eyecon.ro), with improvements
 * by @eternicode. 
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 * @see http://eternicode.github.io/bootstrap-datepicker/
 */
class DatePicker extends \yii\widgets\InputWidget {

	const TYPE_INPUT = 1;
	const TYPE_COMPONENT_PREPEND = 2;
	const TYPE_COMPONENT_APPEND = 3;
	const TYPE_RANGE = 4;
	const TYPE_INLINE = 5;
	
    /**
     * @var string the markup type of widget markup
	 * must be one of the TYPE constants. Defaults
	 * to [[TYPE_COMPONENT_PREPEND]]
     */
    public $type = self::TYPE_COMPONENT_PREPEND;
	
    /**
     * @var the size of the input - 'lg', 'md', 'sm', 'xs'
     */
	public $size;
	
    /**
     * @var array the HTML attributes for the input tag.
     */
    public $options = [];

    /**
     * @var the addon that will be prepended/appended for a 
	 * [[TYPE_COMPONENT_PREPEND]] and [[TYPE_COMPONENT_APPEND]]
     */
	public $addon = '<i class="glyphicon glyphicon-calendar"></i>';

    /**
     * @var string the model attribute 2 if you are using [[TYPE_RANGE]]
	 * for markup.
     */
	public $attribute2;
	
    /**
     * @var string the name of input number 2 if you are using [[TYPE_RANGE]]
	 * for markup
     */	
	public $name2;
	
    /**
     * @var string the name of value for input number 2 if you are using [[TYPE_RANGE]]
	 * for markup
     */	
	public $value2 = null;
	
	/**
     * @var array the HTML attributes for the input number 2 tag.
	 * if you are using [[TYPE_RANGE]] for markup
     */
	public $options2 = [];
	
	/**
     * @var string the range input separator
	 * if you are using [[TYPE_RANGE]] for markup.
	 * Defaults to 'to'
     */
	public $separator = 'to';

	/**
	 * @var array the datepicker plugin options
	 * @see http://bootstrap-datepicker.readthedocs.org/en/latest/options.html
	 */
	public $pluginOptions = [];
	
    /**
     * @var array DatePicker JQuery events. You must define events in
     * event-name => event-function format
     * for example:
     * ~~~
     * pluginEvents = [
     * 		"show" => "function(e) {  # `e` here contains the extra attributes }",
     * 		"hide" => "function(e) {  # `e` here contains the extra attributes }",
     * 		"clearDate" => "function(e) {  # `e` here contains the extra attributes }",
     * 		"changeDate" => "function(e) {  # `e` here contains the extra attributes }",
     * 		"changeYear" => "function(e) {  # `e` here contains the extra attributes }",
     * 		"changeMonth" => "function(e) {  # `e` here contains the extra attributes }",
     * ~~~
	 * @see http://bootstrap-datepicker.readthedocs.org/en/latest/events.html
     */
    public $pluginEvents = [];
	
	/**
	 * @var string identifier for the target datepicker element
	 */
	private $_id;
	
	private $_container = [];
	
    /**
     * Initializes the widget
     * @throw InvalidConfigException
     */
    public function init() {
        parent::init();
		if ($this->type === self::TYPE_RANGE && $this->attribute2 === null && $this->name2 === null) {
			throw new InvalidConfigException("Either 'name2' or 'attribute2' properties must be specified for a datepicker 'range' markup.");
		}
		if ($this->type < 1 || $this->type > 5 || !is_int($this->type)) {
			throw new InvalidConfigException("Invalid value for the property 'type'. Must be an integer between 1 and 5.");
		}
		$this->_id = ($this->type == self::TYPE_INPUT) ? '$("#' . $this->options['id'] . '")' : '$("#' . $this->options['id'] . '").parent()';
		$this->registerAssets();
        $this->renderInput();
		$value = $this->hasModel() ? $this->model[$this->attribute] : $this->value;
		/*
		if (strlen($value) > 0) {
			$date = strtotime($value);
			$date = 'new Date(' . Date("Y", $value) . ', ' . Date("m", $value) . ', ' . Date("d", $value) . ')';
			$js = "{$this->_id}.datepicker('update', '{$value}');";
			$this->getView()->registerJs($js);
		}
		*/
     }

    /**
     * Renders the source Input for the DatePicker plugin.
     * Graceful fallback to a normal HTML  text input - in 
	 * case JQuery is not supported by the browser
     */
    protected function renderInput() {
		if ($this->type == self::TYPE_INLINE) {
			if (empty($this->options['readonly'])) {
				$this->options['readonly'] = true;
			}
			if (empty($this->options['class'])) {
				$this->options['class'] = 'form-control input-sm text-center';
			}
		}
		else {
			Html::addCssClass($this->options, 'form-control');
		}
		
		if ($this->hasModel()) {
			echo $this->parseMarkup(Html::activeTextInput($this->model, $this->attribute, $this->options));
		}
		else {
			echo $this->parseMarkup(Html::textInput($this->name, $this->value, $this->options));
		}
    }
	
    /**
     * Parses the input to render based on markup type
	 * @param the input
     */	
	protected function parseMarkup($input) {
		if ($this->type == self::TYPE_INPUT || $this->type == self::TYPE_INLINE) {
			if (isset($this->size)) {
				Html::addCssClass($this->options, 'input-' . $this->size);
			}
		}
		elseif (isset($this->size)) {
				Html::addCssClass($this->_container, 'input-group input-group-' . $this->size);
		}
		else {
			Html::addCssClass($this->_container, 'input-group');
		}
		if ($this->type == self::TYPE_INPUT) {
			return $input;
		}
		if ($this->type == self::TYPE_COMPONENT_PREPEND) {
			Html::addCssClass($this->_container, 'date');
			return Html::tag('div', "<span class='input-group-addon'>{$this->addon}</span>{$input}", $this->_container);
		}
		if ($this->type == self::TYPE_COMPONENT_APPEND) {
			Html::addCssClass($this->_container, 'date');
			return Html::tag('div', "{$input}<span class='input-group-addon'>{$this->addon}</span>", $this->_container);
		}
		if ($this->type == self::TYPE_RANGE) {
			Html::addCssClass($this->_container, 'input-daterange');
			if (!isset($this->options2['id'])) {
				$this->options2['id'] = $this->hasModel() ? Html::getInputId($this->model, $this->attribute2) : $this->getId() . '-2';
			}
			Html::addCssClass($this->options2, 'form-control');
			$input2 = $this->hasModel() ? 
				Html::activeTextInput($this->model, $this->attribute2, $this->options2) : 
				Html::textInput($this->name2, $this->value2, $this->options2);
			return Html::tag('div', "{$input}<span class='input-group-addon'>{$this->separator}</span>{$input2}", $this->_container);
		}
		if ($this->type == self::TYPE_INLINE) {
			$this->_id = $this->options['id'] . '-inline';
			$this->_container['id'] = $this->_id;
			return Html::tag('div', '', $this->_container) . $input;
		}
	}
	
    /**
     * Adds an asset to the view
     */
    protected function addAsset($view, $file, $type) {
        if ($type == 'css' || $type == 'js') {
            $asset = $view->getAssetManager();
            $name = DatePickerAsset::classname();
            $bundle = $asset->bundles[$name];
            if ($type == 'css') {
                $bundle->css[] = $file;
            }
            else {
                $bundle->js[] = $file;
            }
            $asset->bundles[$name] = $bundle;
            $view->setAssetManager($asset);
        }
    }
	
    /**
     * Registers the needed assets
     */
    public function registerAssets() {
        $view = $this->getView();
        DatePickerAsset::register($view);
        if (!empty($this->pluginOptions['language'])) {
            $this->addAsset($view, 'js/locales/bootstrap-datepicker.' . $this->pluginOptions['language'] . '.js', 'js');
        }
        $js = "{$this->_id}.datepicker(" . Json::encode($this->pluginOptions) . ");";
		if ($this->type == self::TYPE_INLINE) {
			$this->pluginEvents = array_merge($this->pluginEvents, ['changeDate' => 'function (e) { $("#' . $this->options['id'] . '").val(e.format());} ']);
		}
       if (!empty($this->pluginEvents)) {
            $js .= "\n{$this->_id}";
            foreach ($this->pluginEvents as $event => $function) {
                $func = new JsExpression($function);
                $js .= ".on('{$event}', {$func})\n";
            }
        }
        $view->registerJs($js);
    }

}