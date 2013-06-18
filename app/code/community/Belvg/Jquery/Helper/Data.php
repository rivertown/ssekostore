<?php
class Belvg_Jquery_Helper_Data extends Mage_Core_Helper_Data 
{

    public function getUrl($lib)
    {
        switch ($lib) {
            case 'jquery': 
                return 'belvg/jquery/jquery-1.8.3.min.js'; 
            case 'noconflict':
                return 'belvg/jquery/jquery.noconflict.js'; 
            default:
                break;
        }
    }

}