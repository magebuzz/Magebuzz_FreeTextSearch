<?php

class Magebuzz_Freetextsearch_Model_Status extends Varien_Object
{
    const STATUS_ENABLED	= 1;
    const STATUS_DISABLED	= 2;

    static public function getOptionArray()
    {
        return array(
            self::STATUS_ENABLED    => Mage::helper('freetextsearch')->__('Enabled'),
            self::STATUS_DISABLED   => Mage::helper('freetextsearch')->__('Disabled')
        );
    }
}