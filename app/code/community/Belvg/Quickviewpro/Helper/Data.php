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

class Belvg_Quickviewpro_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * The main extension settings
     *
     * @var array
     */
    private $_settings    = array();
    
    const DEFAULT_QUICKVIEW_MAX_HEIGHT  = 400;
    const FANCYBOX_EASING_IN_SWING      = 'easeOutBack';
    const FANCYBOX_EASING_OUT_SWING     = 'easeInBack';

    /**
     * Load the main extension settings
     */
    public function __construct()
    {
        $this->_settings = $this->getQuickviewSettings();
    }

    /**
     * Is the extension enabled?
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return Mage::getStoreConfig('quickviewpro/settings/enabled', Mage::app()->getStore());
    }

    /**
     * Current media type of displaying pictures
     *
     * @return string
     */
    public function getMediaType()
    {
        return Mage::getStoreConfig('quickviewpro/media/active', Mage::app()->getStore());
    }

    /**
     * Get the main extension settings
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->_settings;
    }

    /**
     * Get json representation of
     *
     * @return string
     */
    public function getJsonSettings()
    {
        return Mage::helper('core')->jsonEncode($this->_settings);
    }

    /**
     * Create the main extension setings
     *
     * @return array
     */
    private function getQuickviewSettings()
    {
        $store      = Mage::app()->getStore();
        $settings   = array(
            'media'             => $this->getMediaType(),
            'add_to_cart'       => (int)Mage::getStoreConfig('quickviewpro/settings/add_to_cart', $store),
            'navigation'        => (int)Mage::getStoreConfig('quickviewpro/settings/navigation', $store),
            'navigation_preview'=> (int)Mage::getStoreConfig('quickviewpro/settings/navigation_preview', $store),
            'review'            => (int)Mage::getStoreConfig('quickviewpro/settings/review', $store),
            'share'             => (int)Mage::getStoreConfig('quickviewpro/settings/share', $store),
            'product_page_link' => (int)Mage::getStoreConfig('quickviewpro/settings/product_page_link', $store),
            'overlay_show'      => (int)Mage::getStoreConfig('quickviewpro/settings/overlay_show', $store),
            'overlay_color'     =>      Mage::getStoreConfig('quickviewpro/settings/overlay_color', $store),
            'quickview_scroll'  => (int)Mage::getStoreConfig('quickviewpro/settings/quickview_scroll', $store),
            'max_height'        => (int)Mage::getStoreConfig('quickviewpro/settings/max_height', $store),
            'overlay_opacity'   => (float)str_replace(',', '.', Mage::getStoreConfig('quickviewpro/settings/overlay_opacity', $store))
        );

        $settings   = $this->checkDefaultQuickviewSettings($settings);
        return $settings;
    }

    /**
     * Check default main settings of extension
     *
     * @param array The main extension settings 
     * @return array
     */
    private function checkDefaultQuickviewSettings($settings)
    {
        if (!$settings['max_height']) {
            $settings['max_height'] = self::DEFAULT_QUICKVIEW_MAX_HEIGHT;
        }

        if (!$settings['overlay_show']) {
            unset($settings['overlay_opacity']);
            unset($settings['overlay_color']);
        }

        return $settings;
    }

    /**
     * Create 'Cloud Zoom' settings of displaying pictures
     *
     * @return array
     */
    public function getCloudZoomSettings()
    {
        $modulName     = Mage::app()->getRequest()->getModuleName();
        $store         = Mage::app()->getStore();
        if ($modulName == 'quickviewpro') {
            $pageType  = '_quickview';
        } else {
            $pageType  = '';
        }

        $settings = array(
            'zoomWidth'    =>   (int)Mage::getStoreConfig('quickviewpro/cloudzoom/zoom_width' . $pageType, $store),
            'zoomHeight'   =>   (int)Mage::getStoreConfig('quickviewpro/cloudzoom/zoom_height' . $pageType, $store),
            'position'     =>        Mage::getStoreConfig('quickviewpro/cloudzoom/position' . $pageType, $store),
            'adjustX'      =>   (int)Mage::getStoreConfig('quickviewpro/cloudzoom/adjust_x' . $pageType, $store),
            'adjustY'      =>   (int)Mage::getStoreConfig('quickviewpro/cloudzoom/adjust_y' . $pageType, $store),
            'tint'         =>        Mage::getStoreConfig('quickviewpro/cloudzoom/tint', $store),
            'softFocus'    =>   (int)Mage::getStoreConfig('quickviewpro/cloudzoom/soft_focus', $store),
            'smoothMove'   =>   (int)Mage::getStoreConfig('quickviewpro/cloudzoom/smooth_move', $store),
            'showTitle'    =>   (int)Mage::getStoreConfig('quickviewpro/cloudzoom/show_title', $store),
            'tintOpacity'  => (float)str_replace(',', '.', Mage::getStoreConfig('quickviewpro/cloudzoom/tint_opacity', $store)),
            'lensOpacity'  => (float)str_replace(',', '.', Mage::getStoreConfig('quickviewpro/cloudzoom/lens_opacity', $store)),
            'titleOpacity' => (float)str_replace(',', '.', Mage::getStoreConfig('quickviewpro/cloudzoom/title_opacity', $store))
        );
        if (!$settings['position']) {
            $settings['position']   = Mage::getStoreConfig('quickviewpro/cloudzoom' . $pageType . '/position_element', $store);
        }

        $settings = $this->checkDefaultCloudZoomSettings($settings);
        return $settings;
    }

    /**
     * Check default 'Cloud Zoom' settings of displaying pictures
     *
     * @param array 'Cloud Zoom' settings
     * @return array
     */
    private function checkDefaultCloudZoomSettings($settings)
    {
        if (!$settings['zoomWidth']) {
            unset($settings['zoomWidth']);
        }

        if (!$settings['zoomHeight']) {
            unset($settings['zoomHeight']);
        }

        if (!$settings['smoothMove']) {
            unset($settings['smoothMove']);
        }

        if ($settings['position'] == "0") {
            $settings['position'] = Mage::getStoreConfig('quickviewpro/cloudzoom/position_element', $store);
        }

        $store    = Mage::app()->getStore();
        $tintShow = (int)Mage::getStoreConfig('quickviewpro/cloudzoom/tint_show', $store);
        if (!$tintShow) {
            unset($settings['tint']);
            unset($settings['tintOpacity']);
        }

        return $settings;
    }

    /**
     * Create 'FancyBox' settings of displaying pictures
     *
     * @return array
     */
    public function getFancyBoxSettings()
    {
        $store      = Mage::app()->getStore();
        $settings   = array(
            'padding'        =>   (int)Mage::getStoreConfig('quickviewpro/fancybox/padding', $store),
            'margin'         =>   (int)Mage::getStoreConfig('quickviewpro/fancybox/margin', $store),
            'cyclic'         =>   (int)Mage::getStoreConfig('quickviewpro/fancybox/cyclic', $store),
            'overlayShow'    =>   (int)Mage::getStoreConfig('quickviewpro/fancybox/overlay_show', $store),
            'overlayColor'   =>        Mage::getStoreConfig('quickviewpro/fancybox/overlay_color', $store),
            'titleShow'      =>   (int)Mage::getStoreConfig('quickviewpro/fancybox/title_show', $store),
            'titlePosition'  =>        Mage::getStoreConfig('quickviewpro/fancybox/title_position', $store),
            'transitionIn'   =>        Mage::getStoreConfig('quickviewpro/fancybox/transition_in', $store),
            'easingIn'       =>        Mage::getStoreConfig('quickviewpro/fancybox/transition_in_elastic', $store),
            'transitionOut'  =>        Mage::getStoreConfig('quickviewpro/fancybox/transition_out', $store),
            'easingOut'      =>        Mage::getStoreConfig('quickviewpro/fancybox/transition_out_elastic', $store),
            'opacity'        =>   (int)Mage::getStoreConfig('quickviewpro/fancybox/opacity', $store),
            'speedIn'        =>   (int)Mage::getStoreConfig('quickviewpro/fancybox/speed_in', $store),
            'speedOut'       =>   (int)Mage::getStoreConfig('quickviewpro/fancybox/speed_out', $store),
            'overlayOpacity' => (float)str_replace(',', '.', Mage::getStoreConfig('quickviewpro/fancybox/overlay_opacity', $store))
        );
    
        $settings   = $this->checkDefaultFancyBoxSettings($settings);
        return $settings;
    }

    /**
     * Check default 'FancyBox' settings of displaying pictures
     *
     * @param array 'FancyBox' settings
     * @return array
     */
    private function checkDefaultFancyBoxSettings($settings)
    {
        if (!$settings['overlayShow']) {
            unset($settings['overlayOpacity']);
            unset($settings['overlayColor']);
        }

        if (!$settings['titleShow']) {
            $settings['titleShow'] = FALSE;
            unset($settings['titlePosition']);
        }

        if ($settings['titleShow'] && $settings['titlePosition']=="outside") {
            unset($settings['titleShow']);
            unset($settings['titlePosition']);
        }

        if ($settings['easingIn'] == "swing") {
            $settings['easingIn'] = self::FANCYBOX_EASING_IN_SWING;
        }

        if ($settings['easingOut'] == "swing") {
            $settings['easingOut'] = self::FANCYBOX_EASING_OUT_SWING;
        }

        return $settings;
    }

    /**
     * Get prefix for javascript storage cache
     */
    public function getJsCachePrefix()
    {
        return Mage::helper('core/url')->getCurrentBase64Url();
    }
}