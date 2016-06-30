<?php

class Magebuzz_Freetextsearch_Model_Mysql4_Freetextsearch extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the freetextsearch_id refers to the key field in your database table.
        $this->_init('freetextsearch/freetextsearch', 'freetextsearch_id');
    }
}