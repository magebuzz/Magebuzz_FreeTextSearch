<?php
class Magebuzz_Freetextsearch_Block_Result extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		$keyword = Mage::registry("keyword");
		$this->getLayout()->getBlock('head')->setTitle(Mage::helper('freetextsearch')->__('Search result for "%s"', $keyword));
		return parent::_prepareLayout();
    }
	public function getProductHtml() {
		return $this->getChildHtml('freetextsearch.product');
	}
	public function setListCollection() {      
		$this->getChild('freetextsearch.product')
			->setCollection($this->_getProductCollection());
	}
	protected function _getProductCollection(){
		$results = Mage::registry("result");
		$productIds = $results['product'];
		$collection = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToSelect('*')
			->addAttributeToFilter('entity_id', array('in' => $productIds));	
		$collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());
		
		return $collection;
		
	}
}