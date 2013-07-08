<?php
class Wizkunde_ConfigurableBundle_Block_Catalog_Product_View_Options extends Mage_Catalog_Block_Product_View_Options
{
	protected $_product;

	protected $_optionRenders = array();

	public function __construct()
	{
		parent::__construct();
		$this->addOptionRenderer(
			'default',
			'catalog/product_view_options_type_default',
			'catalog/product/view/options/type/default.phtml'
		);
	}

	/**
	 * Retrieve product object
	 *
	 * @return Mage_Catalog_Model_Product
	 */
	public function getProduct()
	{
		if (!$this->_product) {
			if (Mage::registry('product')) {
				if (Mage::registry('current_producta')) {
					$single= Array();
					$single = Mage::registry('current_producta');
					$this->_product = array_shift($single);
					Mage::unregister('current_producta');
					if (isset($single)) {
					   Mage::register('current_producta',$single);
					}
					return $this->_product;
				}
				$this->_product = Mage::registry('current_product');
			} else {
				$this->_product = Mage::getSingleton('catalog/product');
			}
		}
		return $this->_product;
	}

	/**
	 * Set product object
	 *
	 * @param Mage_Catalog_Model_Product $product
	 * @return Mage_Catalog_Block_Product_View_Options
	 */
	public function setProduct(Mage_Catalog_Model_Product $product = null)
	{
		$this->_product = $product;
		return $this;
	}

	/**
	 * Add option renderer to renderers array
	 *
	 * @param string $type
	 * @param string $block
	 * @param string $template
	 * @return Mage_Catalog_Block_Product_View_Options
	 */
	public function addOptionRenderer($type, $block, $template)
	{
		$this->_optionRenders[$type] = array(
			'block' => $block,
			'template' => $template,
			'renderer' => null
		);
		return $this;
	}

	/**
	 * Get option render by given type
	 *
	 * @param string $type
	 * @return array
	 */
	public function getOptionRender($type)
	{
		if (isset($this->_optionRenders[$type])) {
			return $this->_optionRenders[$type];
		}

		return $this->_optionRenders['default'];
	}

	public function getGroupOfOption($type)
	{
		$group = Mage::getSingleton('catalog/product_option')->getGroupByType($type);

		return $group == '' ? 'default' : $group;
	}

	/**
	 * Get product options
	 *
	 * @return array
	 */
	public function getOptions()
	{
		$options = $this->getProduct()->getOptions();
		return $options;
	}

	public function getOptionsForJson()
	{
		$ret = Array();
		$options = Array ();
		$bundles = Mage::registry('current_producta');
		if (isset($bundles)) {
		foreach  ($bundles as $single) {
			$options[] =  $single->getOptions();
		}
		foreach ($options as $option) {
			$ret+=$option;
		}
		return $ret;
		}
		else {
			return $this->getProduct()->getOptions();
		}
	}

	public function hasOptions()
	{
		if ($this->getOptions()) {
			return true;
		}
		return false;
	}

	public function getJsonConfig()
	{
		$config = array();

		foreach ($this->getOptionsForJson() as $option) {
			/* @var $option Mage_Catalog_Model_Product_Option */
			$priceValue = 0;
			if ($option->getGroupByType() == Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT) {
				$_tmpPriceValues = array();
				foreach ($option->getValues() as $value) {
					/* @var $value Mage_Catalog_Model_Product_Option_Value */
				   $_tmpPriceValues[$value->getId()] = Mage::helper('core')->currency($value->getPrice(true), false, false);
				}
				$priceValue = $_tmpPriceValues;
			} else {
				$priceValue = Mage::helper('core')->currency($option->getPrice(true), false, false);
			}
			$config[$option->getId()] = $priceValue;
		}

		return Mage::helper('core')->jsonEncode($config);
	}

	/**
	 * Get option html block
	 *
	 * @param Mage_Catalog_Model_Product_Option $option
	 */
	public function getOptionHtml(Mage_Catalog_Model_Product_Option $option, $product_id = 0, $visibility = true)
	{
		$renderer = $this->getOptionRender(
			$this->getGroupOfOption($option->getType())
		);
		if (is_null($renderer['renderer'])) {
			$renderer['renderer'] = $this->getLayout()->createBlock($renderer['block'])
				->setTemplate($renderer['template']);
		}
		return $renderer['renderer']
			->setProduct($this->getProduct())
			->setOption($option)
			->setVisibility($visibility)
			->setProductId($product_id)
			->toHtml();
	}
}
