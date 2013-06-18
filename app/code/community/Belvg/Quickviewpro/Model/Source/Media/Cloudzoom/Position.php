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

class Belvg_Quickviewpro_Model_Source_Media_Cloudzoom_Position
{
    /**
     * Specifies the position of the zoom window relative to the small image
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'right',   'label' => Mage::helper('adminhtml')->__('right')),
            array('value' => 'left',    'label' => Mage::helper('adminhtml')->__('left')),
            array('value' => 'top',     'label' => Mage::helper('adminhtml')->__('top')),
            array('value' => 'bottom',  'label' => Mage::helper('adminhtml')->__('bottom')),
            array('value' => 'inside',  'label' => Mage::helper('adminhtml')->__('inside')),
            array('value' => '0',       'label' => Mage::helper('adminhtml')->__('id of an html element')),
        );
    }

}
