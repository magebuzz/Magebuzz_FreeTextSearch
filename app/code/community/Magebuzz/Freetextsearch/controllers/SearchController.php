<?php
class Magebuzz_Freetextsearch_SearchController extends Mage_Core_Controller_Front_Action
{
    public function indexAction() {
		$this->loadLayout();  
		$this->renderLayout();
    }
	public function resultAction() {
		$_coreHelper = Mage::helper('core');
		$items = array();
		/* Get Keyword */
		$keyword = urldecode($this->getRequest()->getParam('keyword'));
		/* Result - Product */
		$products = Mage::helper("freetextsearch")->getSearchResultProducts($keyword);
		$result[] = $products;
		/* Result - Product & CMS */
		$allowSearchCMS = Mage::getStoreConfig('freetextsearch/search_setting/cms_pages_allow');
		if($allowSearchCMS) {
			$cmspage = Mage::helper("freetextsearch")->getSearchCMSPages($keyword);
			$result = array("cms"=>$cmspage,"product"=>$products);
		}
		else {
			$result = array("product"=>$products);
		}
		Mage::register("result",$result);
		Mage::register("keyword",$keyword);
 	  $this->loadLayout();  
		$this->renderLayout();
    }
	public function quicksearchAction() {
		$keyword = urldecode($this->getRequest()->getParam('keyword'));
		$_coreHelper = Mage::helper('core');
		/* Result - Product */
		$productIds = Mage::helper("freetextsearch")->getSearchResultProducts($keyword);
		$cmspageIds = Mage::helper("freetextsearch")->getSearchCMSPages($keyword);
		$limitResults = Mage::helper("freetextsearch")->getNumberResults();
		$limitCharDesc = Mage::helper("freetextsearch")->limitCharDescription();
		$results = array_merge((array)$productIds, (array)$cmspageIds);
		$thumb_width = Mage::getStoreConfig('freetextsearch/quick_search_setting/thumbnail_product_image_width');
		/* Result - Product & CMS */
		$allowSearchCMS = Mage::getStoreConfig('freetextsearch/search_setting/cms_pages_allow');
		$html = "";
		if(count($results) > 0) {
			if(count($productIds) > 0) {
				$html .= "<h3 class='result-title'>Products</h3>";
				$html .= "<ul class='list-products'>";
				$resultCount = 0;
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
					$html .= "<h3 class='product-name'><a href='".$product_url."' title='".$product_name."' target='_blank'>".$product_name."</a></h3>";
					$html .= "<p class='desc'>".Mage::helper('freetextsearch')->prShortText($product_desc,$limitCharDesc)."</p>";
					$html .= "<div class='product-price'><h5>".$price."</h5></div>";
					$html .= "</div>";
					$html .= "</li>";
					$resultCount++;
					if($resultCount >= $limitResults) {
						break;
					}
				}
				$html .= "</ul>";
			}
			if(count($productIds) < $limitResults || count($productIds) <= 0) {
				if($allowSearchCMS) {
					if(count($cmspageIds) > 0) {
						$html .= "<h3 class='result-title'>CMS Pages</h3>";
						$html .= "<ul class='list-cms'>";
						foreach($cmspageIds as $_pageId){
							$html .= "<li class='item cms'>";
							$pageInfo = Mage::getModel('cms/page')->load($_pageId,'page_id')->getData();
							$pageId = $pageInfo['page_id'];
							$pageTitle = $pageInfo['title'];
							$pageUrl = Mage::getBaseUrl().$pageInfo['identifier'];
							$pageContent = $pageInfo['content'];
							$html .= "<h5 class='p-title'><a href='".$pageUrl."' title='".$pageTitle."' alt='".$pageTitle."' target='_blank'>".$pageTitle."</a></h5>";
							$html .= "</li>";
							$resultCount++;
							if($resultCount >= $limitResults) {
								break;
							}
						}
						$html .= "</ul>";
					}
				}
			}
		}
		$responseHtml = array('html' => $html);
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($responseHtml));	
    }
}