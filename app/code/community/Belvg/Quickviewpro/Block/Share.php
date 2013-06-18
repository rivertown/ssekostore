<?php
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
/**********************************************
 *        MAGENTO EDITION USAGE NOTICE        *
 **********************************************/
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
/**********************************************
 *        DISCLAIMER                          *
 **********************************************/
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 **********************************************
 * @category   Belvg
 * @package    Belvg_Quickviewpro
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

class Belvg_Quickviewpro_Block_Share extends Mage_Core_Block_Template
{
    protected $_productId  = '';
    protected $_url        = '';
    protected $_title      = '';
    protected $_img        = '';
    protected $_price      = '';
    protected $_desc       = '';

    const DESCRIPTION_MAX_LEN   = 100;

    public function __construct()
    {
        //parent::__construct();
        $this->loadProductData();
    }

    /**
     * Load data of current product
     */
    protected function loadProductData()
    {
        /* @var $product Mage_Catalog_Model_Product */
        $product = Mage::registry('current_product');
        if ($product instanceof Mage_Catalog_Model_Product) {
            /* @var $helper Mage_Catalog_Helper_Output */
            $helper           = Mage::helper('catalog/output');
            $this->_productId = $product->getId();
            $this->_url       = $product->getProductUrl();
            $this->_title     = $helper->productAttribute($product, $product->getName(), 'name');
            $this->_img       = Mage::helper('catalog/image')->init($product, 'small_image');
            $this->_price     = $product->getPrice();
            $this->_desc      = htmlspecialchars(strip_tags($helper->productAttribute($product, $product->getShortDescription(), 'short_description')), ENT_COMPAT);
            $this->_desc      = $this->cutStr($this->_desc, self::DESCRIPTION_MAX_LEN);
        }    
    }

    /**
     * Get current product Id
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->_productId;
    }

    /**
     * Get current url
     *
     * @return string
     */
    public function getPageUrl()
    {
        return trim($this->_url);
    }

    /**
     * Get title of current product
     *
     * @return string
     */
    public function getPageTitle()
    {
        return trim($this->_title);
    }

    /**
     * Get small image url of current product
     *
     * @return string
     */
    public function getPageImg()
    {
        return $this->_img;
    }

    /**
     * Get price of current product
     *
     * @return string
     */
    public function getPagePrice()
    {
        return $this->_price;
    }

    /**
     * Get short description of current product
     *
     * @return string
     */
    public function getPageDesc()
    {
        return $this->_desc;
    }

    /**
     * Cut string
     *
     * @param string
     * @param int Max string length
     * @return string
     */
    public function cutStr($str, $maxLen)
    {
        $len    = (strlen($str) > $maxLen) ? strripos(substr($str, 0, $maxLen), ' ') : $maxLen;
        $cutStr = substr($str, 0, $len);

        return (strlen($str) > $maxLen) ? $cutStr . '...' : $cutStr;
    }

    /**
     * Check if product can be emailed to friend
     *
     * @return bool
     */
    public function canEmailToFriend()
    {
        $sendToFriendModel = Mage::registry('send_to_friend_model');
        return $sendToFriendModel && $sendToFriendModel->canEmailToFriend();
    }

}