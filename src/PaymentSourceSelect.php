<?php
namespace TopShelfCraft\PaymentSourceSelect;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Fields;
use yii\base\Event;


/**
 * Module to encapsulate Payment Source Select functionality.
 *
 * This class will be available throughout the system via:
 * `Craft::$app->getModule('payment-source-select')`
 *
 * @see http://www.yiiframework.com/doc-2.0/guide-structure-modules.html
 *
 */
class PaymentSourceSelect extends Plugin
{

	/*
     * Statics
     * ===========================================================================
     */


	/**
	 * @param $message
	 * @param array $params
	 * @param null $language
	 *
	 * @return string
	 */
	public static function t($message, $params = [], $language = null)
	{
		return Craft::t(self::getInstance()->getHandle(), $message, $params, $language);
	}


	/*
     * Instance
     * ===========================================================================
     */


	/**
	 * @var bool
	 */
	public $hasCpSettings = false;

	/**
	 * @var bool
	 */
	public $hasCpSection = false;

	/**
	 * @var string
	 */
	public $schemaVersion = '0.0.1.0';


	/**
	 * @inheritdoc
	 */
	public function __construct($id, $parent = null, array $config = [])
	{

		$config['components'] = [];

		parent::__construct($id, $parent, $config);

	}


	/**
	 * Initializes the module.
	 */
	public function init()
	{

		Craft::setAlias('@payment-source-select', __DIR__);
		parent::init();

		$this->_registerEventHandlers();

	}


	/**
	 * Registers handlers for various Event hooks
	 */
	private function _registerEventHandlers()
	{

		/*
		 * Register the fieldtype
		 */
		Event::on(
			Fields::class,
			Fields::EVENT_REGISTER_FIELD_TYPES,
			function(RegisterComponentTypesEvent $event) {
				$event->types[] = PaymentSourceSelectField::class;
			}
		);

	}

}
