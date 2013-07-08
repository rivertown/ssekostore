<?php
class Wizkunde_ConfigurableBundle_Block_Catalog_Product_View_Type_Bundle_Option_Multi
    extends Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Multi
{
    public function _construct()
    {
        $this->setTemplate('configurablebundle/catalog/product/view/type/bundle/option/multi.phtml');
    }

    public function getSelectionTitlePrice($_selection, $includeContainer = true)
    {
        $price = $this->getProduct()->getPriceModel()->getSelectionPreFinalPrice($this->getProduct(), $_selection, 1);
        $this->setFormatProduct($_selection);
        return $_selection->getName();
    }
}