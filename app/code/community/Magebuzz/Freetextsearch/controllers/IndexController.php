<?php
class Magebuzz_Freetextsearch_IndexController extends Mage_Core_Controller_Front_Action
{
  public function indexAction() {
		$keyword = urldecode($this->getRequest()->getParam('keyword'));
		$q = urldecode($this->getRequest()->getParam('q'));
		if($q) {
			$keyword = $q;
		}
		Mage::register('keyword',$keyword);
		$this->loadLayout();  
		$this->renderLayout();
    }
	public function quicksearchAction() {
		$keyword = urldecode($this->getRequest()->getParam('keyword'));
		$_coreHelper = Mage::helper('core');
		
		/* Result - Product */
		$productIds = Mage::helper("freetextsearch")->getSearchResultProducts($keyword);
		$results = array();
		$items = array();
		$thumb_width = Mage::getStoreConfig('freetextsearch/quick_search_setting/thumbnail_product_image_width');
		/* Result - Product & CMS */
		$allowSearchCMS = Mage::getStoreConfig('freetextsearch/search_setting/cms_pages_allow');
		$html = "";
		$html .= "<ul class='list-items'>";
		if(count($productIds) > 0) {
			foreach($productIds as $productId) {
				$html .= "<li class='item product'>";
				$product = Mage::getModel("catalog/product")->load($productId);
				$img = Mage::helper('catalog/image')->init($product, 'image')->resize($thumb_width);
				$img = $img->__toString();
				$price = $_coreHelper->currency($product->getPrice(),true,false);
				$product_url = $product->getProductUrl();
				$product_name = $product->getName();
				$product_desc = $product->getShortDescription();
				$html .= "<a class='product-img' href='".$product_url."' title='".$product_name."' alt='".$product_name."' target='_blank'><img src='". $img ."' title='".$product_name."'></a>";
				$html .= "<div class='product-info'>";
				$html .= "<h3 class='product-name'><a class='product-img' href='".$product_url."' title='".$product_name."' target='_blank'>".$product_name."</a></h3>";
				$html .= "<p class='desc'>".Mage::helper('freetextsearch')->prShortText($product_desc,70)."</p>";
				$html .= "</div>";
				$html .= "</li>";
			}
		}
		if($allowSearchCMS) {
			$cmspageIds = Mage::helper("freetextsearch")->getSearchCMSPages($keyword);
			if(count($cmspageIds) > 0){
				foreach($cmspageIds as $_pageId){
					$html .= "<li class='item cms'>";
					$pageInfo = Mage::getModel('cms/page')->load($_pageId,'page_id')->getData();
					$pageId = $pageInfo['page_id'];
					$pageTitle = $pageInfo['title'];
					$pageUrl = Mage::getBaseUrl().'/'.$pageInfo['identifier'];
					$pageContent = $pageInfo['content'];
					$html .= "<h3 class='p-title'><a href='".$pageUrl."' title='".$pageTitle."' alt='".$pageTitle."' target='_blank'>".$pageTitle."</a></h3>";
					$html .= "</li>";
				}
			}
		}
		$html .= "</ul>";
		$result = array('html' => $html);
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));	
    }	
}