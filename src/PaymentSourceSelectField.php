<?php
namespace TopShelfCraft\PaymentSourceSelect;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\commerce\elements\Order;
use craft\commerce\models\PaymentSource;
use craft\commerce\Plugin as Commerce;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use GraphQL\Type\Definition\Type;
use yii\db\Schema;


class PaymentSourceSelectField extends Field
{

	/*
	 * Statics
	 * ---------------------------------------------------------------------
	 */

	/**
	 * @inheritdoc
	 */
	public static function displayName(): string
	{
		return PaymentSourceSelect::t('Payment Source Select');
	}

	/**
	 * @inheritdoc
	 */
	public static function supportedTranslationMethods(): array
	{
		return [
			self::TRANSLATION_METHOD_NONE,
		];
	}

	/**
	 * Returns the PHPDoc type this field’s values will have.
	 * (It will be used by the generated CustomFieldBehavior class.)
	 */
	public static function valueType(): string
	{
		return PaymentSource::class . "|null";
	}

	/*
	 * Instance
	 * ---------------------------------------------------------------------
	 */

	/**
	 * @inheritdoc
	 */
	public function getContentColumnType(): string
	{
		return Schema::TYPE_INTEGER;
	}

	/**
	 * @inheritdoc
	 * @since 3.5.0
	 */
	public function getContentGqlMutationArgumentType()
	{
		return [
			'name' => $this->handle,
			'type' => Type::int(),
			'description' => $this->instructions,
		];
	}

	/**
	 * @inheritdoc
	 */
	public function getContentGqlType()
	{
		return Type::int();
	}

	/**
	 * @inheritdoc
	 */
	public function getEagerLoadingGqlConditions()
	{
		// TODO
		return parent::getEagerLoadingGqlConditions();
	}

	/**
	 * @inheritdoc
	 */
	public function getTableAttributeHtml($value, ElementInterface $element): string
	{
		// TODO
		return (string)$value;
	}

	/**
	 * @inheritdoc
	 */
	public function modifyElementsQuery(ElementQueryInterface $query, $value): void
	{
		// TODO: Test this.
		if ($value instanceof PaymentSource)
		{
			$value = $value->id;
		}
		parent::modifyElementsQuery($query, $value);
	}

	/**
	 * @inheritdoc
	 */
	public function modifyElementIndexQuery(ElementQueryInterface $query): void
	{
		// TODO: Can we eager-load Payment Sources somehow?
		parent::modifyElementIndexQuery($query);
	}

	/**
	 * @inheritdoc
	 */
	public function normalizeValue($value, ElementInterface $element = null)
	{

		if (empty($value))
		{
			return null;
		}

		if ($value instanceof PaymentSource)
		{
			return $value;
		}

		if ($userId = $this->_getPaymentSourceUserId($element))
		{
			return Commerce::getInstance()->paymentSources->getPaymentSourceByIdAndUserId(
				$value,
				$userId
			);
		}

		return null;

	}

	/**
	 * @inheritdoc
	 */
	public function inputHtml($value, ElementInterface $element = null): string
	{

		if ($value instanceof PaymentSource)
		{
			$value = $value->id;
		}

		if (empty($element))
		{
			return "&mdash;";
		}

		if (!$this->_isValidElementType($element))
		{
			return Craft::$app->getView()->renderTemplate(
				'payment-source-select/field/invalidElementType',
				[
					'element' => $element,
				]
			);
		}

		if (!$element->id)
		{
			return Craft::$app->getView()->renderTemplate(
				'payment-source-select/field/unsavedElement',
				[
					'element' => $element,
				]
			);
		}

		return Craft::$app->getView()->renderTemplate(
			'_includes/forms/select',
			[
				'name' => $this->handle,
				'value' => $value,
				'options' => $this->_getPaymentSourceFormOptions($element),
			]
		);

	}

	/**
	 * @inheritdoc
	 */
	protected function searchKeywords($value, ElementInterface $element): string
	{
		// TODO
		return '';
	}

	/**
	 * @inheritdoc
	 */
	public function serializeValue($value, ElementInterface $element = null): ?int
	{
		if ($value instanceof PaymentSource)
		{
			$value = $value->id;
		}
		return (int)$value ?: null;
	}

	private function _isValidElementType(ElementInterface $element): bool
	{
		return ($element instanceof User || $element instanceof Order);
	}

	private function _getPaymentSourceFormOptions(ElementInterface $element): array
	{

		$userId = $this->_getPaymentSourceUserId($element);

		$paymentSources = Commerce::getInstance()->paymentSources->getAllPaymentSourcesByUserId($userId);
		$options = [];

		foreach ($paymentSources as $source)
		{
			$options[$source->id] = $source->description;
		}

		if (empty($options))
		{
			$entityDisplayName = $element instanceof Order ? Commerce::t('Customer') : $element::displayName();
			$emptyMessage = PaymentSourceSelect::t(
				"This {entityDisplayName} doesn't have any saved Payment Sources.",
				['entityDisplayName' => $entityDisplayName]
			);
			return [
				'none' => [
					'label' => "({$emptyMessage})",
					'value' => null,
					'disabled' => true,
				]
			];
		}

		$noneOption = [
			'label' => "(" . Craft::t('app', "None") . ")",
			'value' => null,
		];
		return [$noneOption] + $options;

	}

	private function _getPaymentSourceUserId(ElementInterface $element): ?int
	{

		if ($element instanceof User)
		{
			return $element->id;
		}

		if ($element instanceof Order)
		{
			return $element->getUser()->id;
		}

		return null;

	}

}
