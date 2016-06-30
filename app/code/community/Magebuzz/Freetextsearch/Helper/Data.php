<?php

class Magebuzz_Freetextsearch_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getResultUrl(){
		$enableQuickSearch = $this->enableQuickSearch();
		if($enableQuickSearch){
			return $this->getUrl('freetextsearch/search/ajaxsearch');
		}
		else{
			return $this->getUrl('freetextsearch/search/result');
		}
	}
	public function enableQuickSearch(){
		return Mage::getStoreConfig('freetextsearch/general/enable_quick_search');
	}
	public function isShowPageContent(){
		return Mage::getStoreConfig('freetextsearch/search_setting/cms_pages_content');
	}
	public function getNumberResults(){
		return (int)Mage::getStoreConfig('freetextsearch/quick_search_setting/number_results');
	}
	public function limitCharDescription(){
		return (int)Mage::getStoreConfig('freetextsearch/quick_search_setting/limit_character_product_desc');
	}
	public function getSearchResultNotice(){
		return Mage::getStoreConfig('freetextsearch/search_setting/search_result_notice');
	}
	public function prShortText($str,$numberChar){
		$short_text = substr($str, 0, $numberChar);
		if(substr($short_text, 0, strrpos($short_text, ' '))!=''){ 
			$short_text = substr($short_text, 0, strrpos($short_text, ' '));
			$short_text = $short_text.'...';
		}
		return $short_text;
	}
	public function getSearchResultProducts($keyword){
		$result = array();
		$limit = Mage::getStoreConfig('freetextsearch/search_setting/number_results');
		/* Default search by Product Name */
		$products = $this->searchProductByAttribute($keyword,"name");
		$product_count = count($products);
		if($product_count)
		{
			foreach($products as $productId)
			{
				$result[] = $productId;
			}
		}
		/* Search Product By Sku */
		/*-----------------------*/
		$searchBySku = Mage::getStoreConfig('freetextsearch/search_setting/search_by_sku');
		if($searchBySku){
			$product_list_sku = $this->searchProductByAttribute($keyword,"sku");
			if(count($product_list_sku))
			{
				foreach($product_list_sku as $productId)
				{
					if (!in_array($productId, $result)) {
						$result[] = $productId;
					}
				}
			}
		}

		/* Search Product By Manufacturer */
		/*-----------------------*/
		$searchByManufacturer = Mage::getStoreConfig('freetextsearch/search_setting/search_by_manufacturer');
		if($searchByManufacturer){
			$product_list_manufacturer = $this->searchProductByManufacturer($keyword);
			if(count($product_list_manufacturer))
			{
				foreach($product_list_manufacturer as $productId)
				{
					if (!in_array($productId, $result)) {
						$result[] = $productId;
					}
				}
			}
		}
		
		/* Search Product By Description */
		/*-----------------------*/
		$searchByDescription = Mage::getStoreConfig('freetextsearch/search_setting/search_by_desc');
		if($searchByDescription){
			$product_list_desc = $this->searchProductByAttribute($keyword,"description");
			if(count($product_list_desc))
			{
				foreach($product_list_desc as $productId)
				{
					if (!in_array($productId, $result)) {
						$result[] = $productId;
					}
				}
			}
		}
		return $result;
	}
	public function getSearchCMSPages($keyword){
		$result = array();
		$storeId    = Mage::app()->getStore()->getId();
		$cmspages = Mage::getModel('cms/page')->getCollection()
		->addFieldToFilter("is_active",1)
		->addFieldToFilter('title',array('like'=>'%'. $keyword.'%'))
		->setCurPage(1)
		->setOrder('title','ASC');
		$cmspages->load();
		if(count($cmspages))
		{
			foreach($cmspages as $_page)
			{
				$result[] = $_page->getId();
			}
		}
		return $result;
	}
	
	
	/*
	* Get Manufacturer Ids by keyword
	*/
	public function getManufacturerIds($keyword) {
		$attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product','manufacturer');
		$manufacturerIds = array();
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		/* Create search query from attribute option table by keyword */
		$searchQuery = "SELECT DISTINCT eao.option_id";
		$searchQuery .= " FROM ".Mage::getSingleton('core/resource')->getTableName('eav_attribute_option_value')." AS eaov";
		$searchQuery .= " JOIN ".Mage::getSingleton('core/resource')->getTableName('eav_attribute_option')." AS eao ON eaov.option_id = eao.option_id";
		$searchQuery .= " WHERE eaov.value LIKE '%".$keyword."' AND eao.attribute_id = '".$attributeId."'";
		
		/* Read results */
		$result = $read->fetchAll($searchQuery);

		foreach($result as $item) {
			array_push($manufacturerIds, $item['option_id']);
		}
		return $manufacturerIds;
	}
	
	public function searchProductByManufacturer($keyword)
	{
		$attributeId = Mage::getResourceModel('eav/entity_attribute')
					->getIdByCode('catalog_product','manufacturer');
		//get manufacturer id by keyword 
		$manufacturerIds = $this->getManufacturerIds($keyword);
		$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
		$attributeOptions = $attribute ->getSource()->getAllOptions();
		$result = array();
		$storeId    = Mage::app()->getStore()->getId();
		
		$products = Mage::getModel('catalog/product')->getCollection()
		->addAttributeToSelect('*')		
		->setStoreId($storeId)
		->addStoreFilter($storeId)
		->addFieldToFilter("status", '1')	
		->addFieldToFilter('manufacturer', array('in' => $manufacturerIds))	
		->setCurPage(1)
		->setOrder('name','ASC');
		
		Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($products);
		Mage::getSingleton('catalog/product_visibility')->addVisibleInSiteFilterToCollection($products);
		$products->load();
		
		if(count($products))
		{
			foreach($products as $product)
			{
				$result[] = $product->getId();
			}
		}
		return $result;
	}
	public function searchProductByAttribute($keyword,$attribute)
	{
		$result = array();
		$storeId    = Mage::app()->getStore()->getId();
		$products = Mage::getModel('catalog/product')->getCollection()
		->addAttributeToSelect('*')		
		->setStoreId($storeId)
		->addStoreFilter($storeId)
		->addFieldToFilter("status",1)	
		->addFieldToFilter($attribute,array('like'=>'%'. $keyword.'%'))	
		->setCurPage(1)
		->setOrder('name','ASC');
		
		Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($products);
		Mage::getSingleton('catalog/product_visibility')->addVisibleInSiteFilterToCollection($products);
		$products->load();
		
		if(count($products))
		{
			foreach($products as $product)
			{
				$result[] = $product->getId();
			}
		}
		return $result;
	}
}