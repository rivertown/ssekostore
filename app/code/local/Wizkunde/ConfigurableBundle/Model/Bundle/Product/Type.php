<?php

class Wizkunde_ConfigurableBundle_Model_Bundle_Product_Type extends Mage_Bundle_Model_Product_Type
{

    /**
     * Retrive bundle selections collection based on used options
     *
     * @param array $optionIds
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Bundle_Model_Mysql4_Selection_Collection
     */
    public function getSelectionsCollection($optionIds, $product = null)
    {
        if (!$this->getProduct($product)->hasData($this->_keySelectionsCollection)) {
            $selectionsCollection = Mage::getResourceModel('bundle/selection_collection')
                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                ->setFlag('require_stock_items', true)
                ->setPositionOrder()
                ->addStoreFilter($this->getStoreFilter($product))
                //->addFilterByRequiredOptions()
                ->setOptionIdsFilter($optionIds);
            $this->getProduct($product)->setData($this->_keySelectionsCollection, $selectionsCollection);
        }
        return $this->getProduct($product)->getData($this->_keySelectionsCollection);
    }

    /**
     * Checking if we can sale this bundle
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isSalable($product = null)
    {
        $optionCollection = $this->getOptionsCollection($product);
        foreach ($this->getSelectionsCollection($optionCollection->getAllIds(), $product) as $option) {
            if($option->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE){
                return true;
            }
        }

        $salable = Mage_Catalog_Model_Product_Type_Abstract::isSalable($product);
        if (!is_null($salable)) {
            return $salable;
        }

        $optionCollection = $this->getOptionsCollection($product);

        if (!count($optionCollection->getItems())) {
            return false;
        }

        $requiredOptionIds = array();

        foreach ($optionCollection->getItems() as $option) {
            if ($option->getRequired()) {
                $requiredOptionIds[$option->getId()] = 0;
            }
        }

        $selectionCollection = $this->getSelectionsCollection($optionCollection->getAllIds(), $product);

        if (!count($selectionCollection->getItems())) {
            return false;
        }
        $salableSelectionCount = 0;
        foreach ($selectionCollection as $selection) {
            if ($selection->isSalable()) {
                $requiredOptionIds[$selection->getOptionId()] = 1;
                $salableSelectionCount++;
            }

        }

        return (array_sum($requiredOptionIds) == count($requiredOptionIds) && $salableSelectionCount);
    }

    protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode)
    {
        /** @var $optionCollection Wizkunde_ConfigurableBundle_Model_Bundle_Mysql4_Option_Collection */
        $optionCollection = $product->getTypeInstance(true)->getOptionsCollection($product);

        $selectionCollection = $product->getTypeInstance(true)
                    ->getSelectionsCollection(
                        $product->getTypeInstance(true)->getOptionsIds($product),
                        $product
                    );

        $optionsArr = $optionCollection->getConfSelections($selectionCollection);

        $prodArr = $optionProdArr = array();

        if ($options = $buyRequest->getBundleOption()) {
            foreach($options as $option_id => $sel_id){
                $products = $optionsArr[$option_id];

                foreach($products as $_product){
                    $prodArr[$_product->getId()] = $option_id;
                    $optionProdArr[$option_id][] = $_product->getSelectionId();
                }
            }

            if($confProducts = $buyRequest->getData('super_attribute')){
                foreach($confProducts as $conf_product_id => $conf_prod_conf){

                    if($optionId = $prodArr[intval($conf_product_id)]){
                        $usedProd = $this->getItemByIdss($optionProdArr[$option_id],$conf_prod_conf, $product);
                        $usedItem = $usedProd->getItems();
                        unset($usedProd);
                        if(count($usedItem) > 1){
                            #SHow Error  We use only 1 product and not many
                        }else{
                            foreach($usedItem as $key => $value) {
                                $getNewkey = $key;
                                break;
                            }
                            //$getNewkey = key($usedItem);
                        }
                        $options[$option_id] = (string)$getNewkey;
                    }
                }
            }
            $buyRequest->setData('bundle_option', $options);

            $selectionIds = array_values($buyRequest->getBundleOption());

            $sel = $this->getSelectionsByIds((array)$selectionIds, $product);				           
            //$buyRequest->unsetData('super_attribute');
        }

        unset($optionCollection, $selectionCollection);
		$superAttributes = array_values($buyRequest->getSuperAttribute());
		$buyRequest->setData('super_attribute',$superAttributes[0]);
		
        $result = parent::_prepareProduct($buyRequest, $product, $processMode);
		$buyRequest->unsetData('super_attribute');

        return $result;

    }

    /**
     * Check is virtual product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isVirtual($product = null)
    {
        if ($this->getProduct($product)->hasCustomOptions()) {
            $customOption = $this->getProduct($product)->getCustomOption('bundle_selection_ids');
            $selectionIds = unserialize($customOption->getValue());

            if(strlen(implode('',$selectionIds)) == 0){
                return false;
            }

            $selections = $this->getSelectionsByIds($selectionIds, $product);
            $virtualCount = 0;
            foreach ($selections->getItems() as $selection) {
                if ($selection->isVirtual()) {
                    $virtualCount++;
                }
            }
            if ($virtualCount == count($selections)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return product weight based on weight_type attribute
     *
     * @param Mage_Catalog_Model_Product $product
     * @return decimal
     */
    public function getWeight($product = null)
    {
        if ($this->getProduct($product)->getData('weight_type')) {
            return $this->getProduct($product)->getData('weight');
        } else {
            $weight = 0;

            if ($this->getProduct($product)->hasCustomOptions()) {
                $customOption = $this->getProduct($product)->getCustomOption('bundle_selection_ids');

                $uns = unserialize($customOption->getValue());
                if(is_null($uns[0])){
                    return $weight;
                }
//die('ass');
                $selectionIds = unserialize($customOption->getValue());
                $selections = $this->getSelectionsByIds($selectionIds, $product);
                foreach ($selections->getItems() as $selection) {
                    $weight += $selection->getWeight();
                }
            }
  //          die('assaa');

            return $weight;
        }
    }

    /**
     * Retrieve product based on attributes
     *
     * @param array $selectionIds
     * @param array $attributes
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Bundle_Model_Mysql4_Selection_Collection
     */
    public function getItemByIdss($selectionIds, $attributes, $product = null)
    {
        sort($selectionIds);
        $storeId = $this->getProduct($product)->getStoreId();
        $usedProduct = Mage::getResourceModel('bundle/selection_collection')
            ->addAttributeToSelect('*')
            ->setFlag('require_stock_items', true)
            ->addStoreFilter($this->getStoreFilter($product))
            ->setStoreId($storeId)
            //->setPositionOrder()
//                ->addFilterByRequiredOptions()
            ->setSelectionIdsFilter($selectionIds)
            ;
		count($usedProduct);
        if(is_array($attributes)){

            foreach($attributes as $attribute_id => $attr_value){
                $attribute = Mage::getModel('eav/entity_attribute')->load($attribute_id);
                $attribute_code = $attribute->getAttributeCode();

                $usedProduct->addAttributeToSelect($attribute_code);
                $usedProduct->addAttributeToFilter($attribute_code, $attr_value);
            }
        }
        return $usedProduct;
    }

    /**
     * Retrieve bundle selections collection based on ids
     *
     * @param array $selectionIds
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Bundle_Model_Mysql4_Selection_Collection
     */
    public function getSelectionsByIds($selectionIds, $product = null)
    {
//echo 'JAAAA111'; die;
        sort($selectionIds);

        $usedSelections     = $this->getProduct($product)->getData($this->_keyUsedSelections);
        $usedSelectionsIds  = $this->getProduct($product)->getData($this->_keyUsedSelectionsIds);

//        if (!$usedSelections || serialize($usedSelectionsIds) != serialize($selectionIds)) {
            $storeId = $this->getProduct($product)->getStoreId();
            $usedSelections = Mage::getResourceModel('bundle/selection_collection')
                ->addAttributeToSelect('*')
                ->setFlag('require_stock_items', true)
                ->addStoreFilter($this->getStoreFilter($product))
                ->setStoreId($storeId)
                ->setPositionOrder()
//                ->addFilterByRequiredOptions()
                ->setSelectionIdsFilter($selectionIds)
            ;

            if (!Mage::helper('catalog')->isPriceGlobal() && $storeId) {
                $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
                $usedSelections->joinPrices($websiteId);
            }

            $this->getProduct($product)->setData($this->_keyUsedSelections, $usedSelections);
            $this->getProduct($product)->setData($this->_keyUsedSelectionsIds, $selectionIds);
//        }
//echo '$selections:<pre>';print_r($selectionIds);echo '</pre>'; die;

        return $usedSelections;
    }

    public function prepareForCartAdvanced(Varien_Object $buyRequest, $product = null, $processMode = null)
   {
         //return $this->prepareForCart($buyRequest, $product);
         //l('-----------------------------------------');
       //l($buyRequest->getData());
       //l((Mage_Catalog_Model_Product_Type::TYPE_BUNDLE == $product->getTypeId())?'bundle':'');
             $optionCollection = $product->getTypeInstance(true)->getOptionsCollection($product);
             $selectionCollection = $product->getTypeInstance(true)
                   ->getSelectionsCollection(
                       $product->getTypeInstance(true)->getOptionsIds($product),
                       $product
                   );
       $optionsArr = $optionCollection->getConfSelections($selectionCollection);
             //pr($optionsArr);
         if (!$processMode) {
           $processMode = self::PROCESS_MODE_FULL;
       }
             $additional_price = 0;
             $l_aBundleOptions = $buyRequest->getBundleOption();          $l_aSuperAttributes = $buyRequest->getSuperAttribute();
       $_products = $this->_prepareProduct($buyRequest, $product, $processMode);
             $_extended_products = array();
             foreach ($_products as $l_iIndex => $candidate) {
           if ($candidate->getParentProductId()) {

               //pr('selection id: '. $candidate->getSelectionId());
               //pr('option id:'. $candidate->getOptionId());
                             //l('parent id: '. $candidate->getParentProductId());
                             $l_oConfigurableProduct = null;
                             if (isset($l_aBundleOptions[$candidate->getOptionId()]) && $l_aBundleOptions[$candidate->getOptionId()] != $candidate->getSelectionId()) {
                                 //die();
                                 // this is the case - can be the case - when we have a configurable product set as an option for the bundle item
                   $l_oProduct = Mage::getModel('catalog/product')->load($candidate->getId());
                   foreach ($optionsArr[$candidate->getOptionId()] as $l_oProductPosibility) {
                       if (Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE == $l_oProductPosibility->getTypeId()) {
                           $l_oConfigurableProduct = $l_oProductPosibility;
                           break;
                       }
                   }
                                     if ($l_oConfigurableProduct) {
                       //$l_oProduct = $l_oConfigurableProduct;
                       //$l_oProduct->addCustomOption('attributes', serialize($l_aSuperAttributes[$l_oConfigurableProduct->getProductId()]));
                   }
                                                   }
               else {
                   $l_oProduct = Mage::getModel('catalog/product')->load($candidate->getId());
               }
                             $l_iProductId = $candidate->getId();
                             if ($l_oProduct->getId()) {
                                 //l('product id: '. $l_oProduct->getId());
                                     if (Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE == $l_oProduct->getTypeId()) {
                       $br = clone $buyRequest;
                       $br->setSuperAttribute($l_aSuperAttributes[$l_oProduct->getId()]);
                       $br->setProduct($l_oProduct->getId());
                       $_result = $l_oProduct->getTypeInstance(true)->prepareForCartAdvanced($br, $l_oProduct, $processMode);
                   }
                   else {
                       $_result = $l_oProduct->getTypeInstance(true)->prepareForCartAdvanced($buyRequest, $l_oProduct, $processMode);
                   }
                   if(!is_object($_result[0])) {
                       // pr(get_class($_result[0]));
                       // die('xxxxx');
                   }
                                     $_result[0]->setParentProductId($product->getId());
                                     /*
                   $parents = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($l_oProduct->getId());
                                     foreach($parents as $parent_id) {
                       $_prod = Mage::getModel('catalog/product')->load($parent_id);
                       foreach($_prod->getTypeInstance(true)->getConfigurableAttributesAsArray($_prod) as $conf_attr) {
                           foreach($conf_attr['values'] as $val) {
                               if($_result[0]->getData($conf_attr['attribute_code']) == $val['value_index']) {
                                   $additional_price += $val['pricing_value'];
                               }
                           }                                          }
                   }
                   */
                                     /*$_result[0]->setData('price',$_result[0]->getData('price') + $additional_price);
                   Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
                   $_result[0]->save();*/
                                     $_extended_products = array_merge($_products, $_result);
                                     $_products[$l_iIndex] = $_result[0];
                                     foreach ($_result[0]->getCustomOptions() as $l_oOption) {
                                             if ('info_buyRequest' == $l_oOption->getData('code')) {
                           continue;
                       }
                                             $product->addCustomOption(
                           'selection_'. $l_iIndex. '_'. $l_oOption->getData('code'),
                           $l_oOption->getData('value')
                       );
                                         }
                                     if ($l_oConfigurableProduct) {
                       $product->addCustomOption('selection_'. $candidate->getSelectionId(). '_attributes', serialize($l_aSuperAttributes[$l_oConfigurableProduct->getProductId()]));
                       $product->addCustomOption('selection_'. $candidate->getSelectionId(). '_parent_id', $l_oConfigurableProduct->getId());
                   }
                                     //if (Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE == $l_oProduct->getTypeId()) {                                          $_result[0]->addCustomOption('drecomm_unique_id', serialize($buyRequest->getData()));
                   //}
                                               }
                             unset($l_oProduct);                          }
       }
       $custom_options = array();
       foreach($buyRequest->getData('options') as $key => $option)
       {
           if(empty($option))
               continue;
           $query = "SELECT price FROM catalog_product_option_price WHERE option_id = '".$key."'";
           $data = Mage::getSingleton("core/resource")->getConnection("core_read")->fetchAll($query);
           if(!isset($data[0]))
               continue;
           $data = $data[0];
           $custom_options[$key] = $data['price'];
       }
       $product->addCustomOption('option_ids', serialize($custom_options));
       //$product->setData('price',$product->getData('price') + $additional_price);
                   /*$this->processFileQueue();
       print "<pre>";
       print_r($_products);
       print "</pre>";*/
       //l('-----------------------------------------');
       //die;
       //return $_extended_products;
       return $_products;
   }

    /**
     * Initialize product(s) for add to cart process
     *
     * @param   Varien_Object $buyRequest
     * @param Mage_Catalog_Model_Product $product
     * @return  unknown
     */
    public function prepareForCart(Varien_Object $buyRequest, $product = null)
    {
//        echo 'IN PREPARE'; die;

        if( !$buyRequest->getSuperAttribute() ){
            if (!$buyRequest->getData('options')) {
                return parent::prepareForCart($buyRequest, $product);
            }
        }

        $result = array();
        $selections = array();

        $product = $this->getProduct($product);

        $_appendAllSelections = false;
        if ($product->getSkipCheckRequiredOption()) {
            $_appendAllSelections = true;
        }

        if ($options = $buyRequest->getBundleOption()) {
            $qtys = $buyRequest->getBundleOptionQty();
            foreach ($options as $_optionId => $_selections) {
                if (empty($_selections)) {
                    unset($options[$_optionId]);
                }
            }
            $optionIds = array_keys($options);

            if (empty($optionIds)) {
                return Mage::helper('bundle')->__('Please select options for product.');
            }

            //$optionsCollection = $this->getOptionsByIds($optionIds, $product);
            $product->getTypeInstance(true)->setStoreFilter($product->getStoreId(), $product);
            $optionsCollection = $this->getOptionsCollection($product);
            if (!$this->getProduct($product)->getSkipCheckRequiredOption()) {
                foreach ($optionsCollection->getItems() as $option) {
                    if ($option->getRequired() && !isset($options[$option->getId()])) {
                       // return Mage::helper('bundle')->__('Required options not selected.');
                    }
                }
            }
            $selectionIds = array();

            foreach ($options as $optionId => $selectionId) {
                if (!is_array($selectionId)) {
                    if ($selectionId != '') {
                        $selectionIds[] = $selectionId;
                    }
                } else {
                    foreach ($selectionId as $id) {
                        if ($id != '') {
                            $selectionIds[] = $id;
                        }
                    }
                }
            }

            $selections = $this->getSelectionsByIds($selectionIds, $product);

            /**
             * checking if selections that where added are still on sale
             */
            foreach ($selections->getItems() as $key => $selection) {
                if (!$selection->isSalable()) {
                    $_option = $optionsCollection->getItemById($selection->getOptionId());
                    if (is_array($options[$_option->getId()]) && count($options[$_option->getId()]) > 1){
                        $moreSelections = true;
                    } else {
                        $moreSelections = false;
                    }
                    if ($_option->getRequired() && (!$_option->isMultiSelection() || ($_option->isMultiSelection() && !$moreSelections))) {
                        return Mage::helper('bundle')->__('Selected required options not available.');
                    }
                }
            }

            $optionsCollection->appendSelections($selections, false, $_appendAllSelections);

            $selections = $selections->getItems();

        } else {
            $product->getTypeInstance(true)->setStoreFilter($product->getStoreId(), $product);

            $optionCollection = $product->getTypeInstance(true)->getOptionsCollection($product);

            $optionIds = $product->getTypeInstance(true)->getOptionsIds($product);
            $selectionIds = array();

            $selectionCollection = $product->getTypeInstance(true)
                ->getSelectionsCollection(
                    $product->getTypeInstance(true)->getOptionsIds($product),
                    $product
                );

            $options = $optionCollection->appendSelections($selectionCollection, false, $_appendAllSelections);

            foreach ($options as $option) {
                if ($option->getRequired() && count($option->getSelections()) == 1) {
                    $selections = array_merge($selections, $option->getSelections());
                } else {
                    $selections = array();
                    break;
                }
            }
        }
//echo '<pre>'; print_r($selections); echo '</pre>'; die;
        if (count($selections) > 0) {
            $uniqueKey = array($product->getId());
            $selectionIds = array();

            /*
             * shaking selection array :) by option position
             */
            usort($selections, array($this, "shakeSelections"));

            foreach ($selections as $selection) {

                if ($selection->getSelectionCanChangeQty() && isset($qtys[$selection->getOptionId()])) {
                    $qty = $qtys[$selection->getOptionId()] > 0 ? $qtys[$selection->getOptionId()] : 1;
                } else {
                    $qty = $selection->getSelectionQty() ? $selection->getSelectionQty() : 1;
                }

                $product->addCustomOption('selection_qty_' . $selection->getSelectionId(), $qty, $selection);
                $selection->addCustomOption('selection_id', $selection->getSelectionId());

                if ($customOption = $product->getCustomOption('product_qty_' . $selection->getId())) {
                    $customOption->setValue($customOption->getValue() + $qty);
                } else {
                    $product->addCustomOption('product_qty_' . $selection->getId(), $qty, $selection);
                }

                /*
                 * creating extra attributes that will be converted
                 * to product options in order item
                 * for selection (not for all bundle)
                 */
                $simple= false;
                $superAtt = $buyRequest->getSuperAttribute();

                $slP = Mage::getModel('catalog/product')->load($selection->getProductId());
                if($slP->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE){
                    $selection->addCustomOption('attributes', serialize($superAtt[$selection->getProductId()]));
                } else {
                    $superAtt = false;
                    $simple = true;
                }

                $price = $product->getPriceModel()->getSelectionPrice($product, $selection, $qty);

                $attributes = array(
                    'price' => Mage::app()->getStore()->convertPrice($price),
                    'qty' => $qty,
                    'option_label' => $selection->getOption()->getTitle(),
                    'option_id' => $selection->getOption()->getId()
                );

                //if (!$product->getPriceType()) {
                if($superAtt){

                    foreach($superAtt as $skey=>$satt){
                        if($skey != $selection->getProductId()){
                            if($buyRequest->getOptions()){

                            }else {
                                continue;
                            }
                        }

                        $br = clone $buyRequest;
                        //$br->unsetData('bundle_option_qty');
                        //$br->unsetData('bundle_option');
                        $br->setSuperAttribute($satt);
                        $br->setQty($qty);
                        $br->setProduct($selection->getProductId());


                        $_result = $selection->getTypeInstance(true)->prepareForCart($br, Mage::getModel('catalog/product')->load($skey));
                        if (is_string($_result) && !is_array($_result)) {
                            return $_result;
                        }

                        if (!isset($_result[0])) {
                            return Mage::helper('checkout')->__('Can not add item to shopping cart');
                        }

                        $result []= $_result[0];

                    }
                }
                if ($simple) {
                    $br = clone $buyRequest;
                    $br->unsetData('super_attribute');
                    $br->setQty($qty);
                    $br->setProduct($slP->getData('entity_id'));
                    $prod = Mage::getModel('catalog/product')->load($slP->getData('entity_id'));
                    $_result = $selection->getTypeInstance(true)->prepareForCart($br,$prod );
                    $result [] = $_result[0];


                }
            }

            return $result;
        }

        return $this->getSpecifyOptionMessage();
    }

}
