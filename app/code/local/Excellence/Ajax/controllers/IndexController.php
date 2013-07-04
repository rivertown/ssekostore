<?php
require_once 'Mage/Checkout/controllers/CartController.php';
class Excellence_Ajax_IndexController extends Mage_Checkout_CartController
{
	public function addAction()
	{
		$cart   = $this->_getCart();
		$params = $this->getRequest()->getParams();
		if($params['isAjax'] == 1){
			$response = array();
			try {
				if (isset($params['qty'])) {
					$filter = new Zend_Filter_LocalizedToNormalized(
					array('locale' => Mage::app()->getLocale()->getLocaleCode())
					);
					$params['qty'] = $filter->filter($params['qty']);
				}

				$product = $this->_initProduct();
				$related = $this->getRequest()->getParam('related_product');

				/**
				 * Check product availability
				 */
				if (!$product) {
					$response['status'] = 'ERROR';
					$response['message'] = $this->__('Unable to find Product ID');
				}

				$cart->addProduct($product, $params);
				if (!empty($related)) {
					$cart->addProductsByIds(explode(',', $related));
				}

				$cart->save();

				$this->_getSession()->setCartWasUpdated(true);

				/**
				 * @todo remove wishlist observer processAddToCart
				 */
				Mage::dispatchEvent('checkout_cart_add_product_complete',
				array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
				);

				if (!$cart->getQuote()->getHasError()){
					$message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->htmlEscape($product->getName()));
					$response['status'] = 'SUCCESS';
					$response['message'] = $message;
					
					$this->loadLayout();
					$toplink = $this->getLayout()->getBlock('top.links')->toHtml();
					Mage::register('referrer_url', $this->_getRefererUrl());
					$response['toplink'] = $toplink;
					$response['product_name'] = $product->getName();
					$response['product_image'] = $product->getImageUrl();
					$response['price'] = number_format($product->getFinalPrice(),2);
					$response['qty'] = $filter->filter($params['qty']);

					$cartHelper = Mage::helper('checkout/cart');
					$items = $cartHelper->getCart()->getItems();
					foreach ($items as $item) {
					    if ($item->getProduct()->getId() == $product->getId()) {
					        $itemId = $item->getItemId();
					        break;
					    }
					}
					$response['remove_url'] = Mage::getUrl( 'checkout/cart/delete', array( 'id' => $itemId ) );   
					$response['count'] = Mage::helper('checkout/cart')->getSummaryCount();    
				}
			} catch (Mage_Core_Exception $e) {
				$msg = "";
				if ($this->_getSession()->getUseNotice(true)) {
					$msg = $e->getMessage();
				} else {
					$messages = array_unique(explode("\n", $e->getMessage()));
					foreach ($messages as $message) {
						$msg .= $message.'<br/>';
					}
				}

				$response['status'] = 'ERROR';
				$response['message'] = $msg;
			} catch (Exception $e) {
				$response['status'] = 'ERROR';
				$response['message'] = $this->__('Cannot add the item to shopping cart.');
				Mage::logException($e);
			}
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
			return;
		}else{
			return parent::addAction();
		}
	}
}