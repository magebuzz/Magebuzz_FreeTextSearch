<?php
class Magebuzz_Freetextsearch_Block_Freetextsearch extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getFreetextsearch()     
     { 
        if (!$this->hasData('freetextsearch')) {
            $this->setData('freetextsearch', Mage::registry('freetextsearch'));
        }
        return $this->getData('freetextsearch');
        
    }
}