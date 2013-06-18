<?php
class Belvg_Jquery_Model_Observer
{
    public function addLibz()
    {
        $jquery_head = Mage::app()->getLayout()->getBlock('jquery_head');
        if ($jquery_head instanceof Belvg_Jquery_Block_Head && Mage::getStoreConfig('jquery/settings/enabled')) {
            $helper = Mage::helper('jquery/data');
            $head   = Mage::app()->getLayout()->getBlock('head');
            $data   = $head->getData();
            $tmp    = $data['items'];
            $data['items'] = '';
            $head->setData($data);

            $libz   = $jquery_head->getLibz();
            $urlz   = $jquery_head->getJsUrlz();
            //print_r($urlz); die;
            if (!empty($libz)) {
                foreach ($libz as $lib) {
                    $head->addJs($helper->getUrl($lib));            
                }
            }

            if (!empty($urlz)) {
                foreach ($urlz as $url) {
                    $head->addJs($url);            
                }
            }

            $data = $head->getData();
            if (!$data['items']) {
                $data['items'] = array();
            }

            $data['items'] = array_merge((array)$data['items'], (array)$tmp);
            $head->setData($data);
        }
    }
    
}