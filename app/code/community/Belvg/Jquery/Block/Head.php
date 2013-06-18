<?php
class Belvg_Jquery_Block_Head extends Mage_Adminhtml_Block_Template
{
    protected $_libz   = array();
    protected $_jsurlz = array();
    
    public function addLib($libname)
    {
        $this->_libz[] = $libname;
    }
    
    public function addJs($jsurl)
    {
        $this->_jsurlz[] = $jsurl;
    }
    
    public function getLibz()
    {
        return $this->_libz;
    }
    
    public function getJsUrlz()
    {
        return $this->_jsurlz;
    }
    
}