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

class Belvg_Quickviewpro_Block_Adminhtml_Global extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Associative array of system setting elements
     *
     * @var array
     */
    protected $_elements = array();

    /**
     * Custom template
     *
     * @var string
     */
    protected $_template = 'belvg/quickviewpro/global.phtml';

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $fieldset)
    {
        foreach ($fieldset->getSortedElements() as $element) {
            $htmlId = $element->getHtmlId();
            $this->_elements[$htmlId] = $element;
        }

        $originalData = $fieldset->getOriginalData();
        $this->addData(array(
            'fieldset_label'    => $fieldset->getLegend(),
            'fieldset_help_url' => isset($originalData['help_url']) ? $originalData['help_url'] : '',
        ));
        return $this->toHtml();
    }

    /**
     * Get array of element objects
     *
     * @return array
     */
    public function getElements()
    {
        return $this->_elements;
    }

    /**
     * Get element by id
     *
     * @param string $elementId
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getElement($elementId)
    {
        if (isset($this->_elements[$elementId])) {
            return $this->_elements[$elementId];
        }

        return FALSE;
    }

    /**
     * Return radio buttons group html with hidden field for correct config values
     *
     * @param Varien_Data_Form_Element_Abstract
     * @return string
     */
    public function getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $configPath  = 'quickviewpro/' . $this->getElementOriginalData($element, 'frontend_element_name');
        $configType  = $this->getElementOriginalData($element, 'frontend_type');
        $name        = $this->getRadioName($element);
        $class       = $this->getRadioClass($element);
        $value       = $element->getHtmlId();
        $store       = Mage::app()->getRequest()->getParam('store');
        $checked     = ( $element->getHtmlId() == Mage::getStoreConfig($configPath, $store) );

        return '<input id="' . $value . '_checked" name="' . $element->getName() . '" type="hidden" value="' . (($checked)?'1':'0') . '" class="quickviewpro_media_checked" />
                <input id="' . $value . '" name="' . $name . '" type="' . $configType . '" value="' . $value . '" class="' . $class . '" ' . (($checked)?'checked':'') . ' />';
    }
    
    /**
     * Get total radio button elements name
     *
     * @param Varien_Data_Form_Element_Abstract
     * @return string
     */
    public function getRadioName(Varien_Data_Form_Element_Abstract $element)
    {
        if ($this->getElementOriginalData($element, 'frontend_type') == 'radio') {
            $path = explode('/', $this->getElementOriginalData($element, 'frontend_element_name'));
            return 'groups[' . $path[0] . '][fields][' . $path[1] . '][value]';
        } else {
            return FALSE;
        }
    }

    /**
     * Get total radio button elements class
     *
     * @param Varien_Data_Form_Element_Abstract
     * @return string
     */
    public function getRadioClass(Varien_Data_Form_Element_Abstract $element)
    {
        if ($this->getElementOriginalData($element, 'frontend_type') == 'radio') {
            $path = explode('/', $this->getElementOriginalData($element, 'frontend_element_name'));
            return 'quickviewpro_' . $path[0] . '_' . $path[1];
        } else {
            return FALSE;
        }
    }

    /**
     * Get element label
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function getElementLabel(Varien_Data_Form_Element_Abstract $element)
    {
        return $element->getLabel();
    }

    /**
     * Get element comment
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function getElementComment(Varien_Data_Form_Element_Abstract $element)
    {
        return $element->getComment();
    }

    /**
     * Get element data by key
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @param string
     * @return string
     */
    public function getElementOriginalData(Varien_Data_Form_Element_Abstract $element, $key)
    {
        $data = $element->getOriginalData();
        return isset($data[$key]) ? $data[$key] : FALSE;
    }

    /**
     * Get custom html of element label
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function getElementLabelTextHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return sprintf('<span id="%s">%s</span>',
            $element->getHtmlId() . '_label_text',
            $this->escapeHtml($this->getElementLabel($element))
        );
    }

}
