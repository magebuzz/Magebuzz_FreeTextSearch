<?php

class Magebuzz_Freetextsearch_Model_Mysql4_Freetextsearch_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('freetextsearch/freetextsearch');
    }
}