<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Order API
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Order_Api extends Mage_Sales_Model_Api_Resource
{
    /**
     * Initialize attributes map
     */
    public function __construct()
    {
        $this->_attributesMap = array(
            'order' => array('order_id' => 'entity_id'),
            'order_address' => array('address_id' => 'entity_id'),
            'order_payment' => array('payment_id' => 'entity_id')
        );
    }

    /**
     * Initialize basic order model
     *
     * @param mixed $orderIncrementId
     * @return Mage_Sales_Model_Order
     */
    protected function _initOrder($orderIncrementId)
    {
        $order = Mage::getModel('sales/order');

        /* @var $order Mage_Sales_Model_Order */

        $order->loadByIncrementId($orderIncrementId);

        if (!$order->getId()) {
            $this->_fault('not_exists');
        }

        return $order;
    }

    /**
     * Retrieve list of orders. Filtration could be applied
     *
     * @param null|object|array $filters
     * @return array
     */
	 
	  public function affitems($filters = null)
    {
		
		try
		{
       $result = array();

		 $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		 $sql  = 'Select name,email, status, balance, total_commission_received, total_paid from affiliateplus_account where email = '. '"'.$filters.'"'. ' limit 1' ;
		 $affiliateCollection = $connection->fetchRow($sql);
		 $result["name"] =  $affiliateCollection["name"];
		 $result["email"] =  $affiliateCollection["email"];
		 $result["status"] =  $affiliateCollection["status"];
		 $result["balance"] =  $affiliateCollection["balance"];
		 $result["total_commission_received"] =  $affiliateCollection["total_commission_received"];
		 $result["total_paid"] =  $affiliateCollection["total_paid"];
		
		 $customer_email = $filters; 
		 $customer = Mage::getModel("customer/customer"); 
		 $customer->setWebsiteId(1);
		 $customer->loadByEmail($customer_email); 
		// $customer2 = Mage::getModel('customer/customer')->load($customer->getData('entity_id'));
		// $addressId =  $customer->getData("addrress_id");
		  foreach ($customer->getAddresses() as $address)
	{
	   $customerAddress = $address->toArray();
	}
		 $address = Mage::getModel('customer/address')
            ->load( $customerAddress['entity_id']);

        

        foreach (Mage::getModel('customer/address_api')->getAllowedAttributes($address) as $attributeCode => $attribute) {
             if($attributeCode == "street")
             {
			$result["street"] = $address->getStreet(1);
			$result["houseno"] = $address->getStreet(2);
			
			$neighbour = $address->getStreet(3); 
			if(empty($neighbour))
			{
			
          $result["neighbour"] = $address->getStreet(3); 
             }
             else
             {
           $result["neighbour"] = $address->getStreet(4); 
            }
		//$select = 'Select * from affiliateplus_account where email = '. '"'.$filters.'"' ;
		//$affiliateCollection = new Magestore_Affiliateplus_Model_Mysql4_Account_Collection();
//$collection = Mage::getResourceModel('affiliateplus_account/collection');
		//$affiliateCollection->load($select);
			 }
			 else
			 {
				 $result[$attributeCode] = $address->getData($attributeCode);
			 }
		
		}
		}
		catch (Exception $e)
		{
			Mage::log( $e->getMessage() , null, "sumiterrror.log");
		}
Mage::log($result, null, "result.log");
$accounts[] = $result;
		return $accounts;
    }

    public function items($filters = null)
    {
		
        $orders = array();

        //TODO: add full name logic
        $billingAliasName = 'billing_o_a';
        $shippingAliasName = 'shipping_o_a';

        /** @var $orderCollection Mage_Sales_Model_Mysql4_Order_Collection */
        $orderCollection = Mage::getModel("sales/order")->getCollection();
        $billingFirstnameField = "$billingAliasName.firstname";
        $billingLastnameField = "$billingAliasName.lastname";
        $shippingFirstnameField = "$shippingAliasName.firstname";
        $shippingLastnameField = "$shippingAliasName.lastname";
        $orderCollection->addAttributeToSelect('*')
            ->addAddressFields()
            ->addExpressionFieldToSelect('billing_firstname', "{{billing_firstname}}",
                array('billing_firstname' => $billingFirstnameField))
            ->addExpressionFieldToSelect('billing_lastname', "{{billing_lastname}}",
                array('billing_lastname' => $billingLastnameField))
            ->addExpressionFieldToSelect('shipping_firstname', "{{shipping_firstname}}",
                array('shipping_firstname' => $shippingFirstnameField))
            ->addExpressionFieldToSelect('shipping_lastname', "{{shipping_lastname}}",
                array('shipping_lastname' => $shippingLastnameField))
            ->addExpressionFieldToSelect('billing_name', "CONCAT({{billing_firstname}}, ' ', {{billing_lastname}})",
                array('billing_firstname' => $billingFirstnameField, 'billing_lastname' => $billingLastnameField))
            ->addExpressionFieldToSelect('shipping_name', 'CONCAT({{shipping_firstname}}, " ", {{shipping_lastname}})',
                array('shipping_firstname' => $shippingFirstnameField, 'shipping_lastname' => $shippingLastnameField)
        );

        /** @var $apiHelper Mage_Api_Helper_Data */
        $apiHelper = Mage::helper('api');
        $filters = $apiHelper->parseFilters($filters, $this->_attributesMap['order']);
        try {
            foreach ($filters as $field => $value) {
                $orderCollection->addFieldToFilter($field, $value);
            }
        } catch (Mage_Core_Exception $e) {
            $this->_fault('filters_invalid', $e->getMessage());
        }
        foreach ($orderCollection as $order) {
			$shpadd = array();
            $orders[] = $this->_getAttributes($order, 'order');
		//	$addattrbt = array("street","region","city","postcode","country_id");
			$currentattset  = $this->_getAttributes($order->getShippingAddress(), 'order_address');
			$currentattset["region"] = $order->getShippingAddress()->getData("region"); 
			$currentattset["city"] = $order->getShippingAddress()->getData("city");
			$currentattset["street"] = $order->getShippingAddress()->getStreet(1);
			$currentattset["houseno"] = $order->getShippingAddress()->getStreet(2);
			$neighbour = $order->getShippingAddress()->getStreet(3);
			if(empty($neighbour))
			{
				$currentattset["neighbour"] = $order->getShippingAddress()->getStreet(4) ;
			}
			else
			{
				$currentattset["neighbour"] = $order->getShippingAddress()->getStreet(3) ;
			}
			$currentattset["postcode"] = $order->getShippingAddress()->getData("postcode");
		    $shpadd['shipping_address'] = $currentattset;
		    $orders[]  = $shpadd ;
			$orditem['items'] = array();

        foreach ($order->getAllItems() as $item) {
            if ($item->getGiftMessageId() > 0) {
                $item->setGiftMessage(
                    Mage::getSingleton('giftmessage/message')->load($item->getGiftMessageId())->getMessage()
                );
            }
			$currentattset = $this->_getAttributes($item, 'order_item');
			
			$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $currentattset['sku']);
			$currentattset["shoe_size"] = $product->getData("shoe_size");
			$productModel = Mage::getModel('catalog/product');
			$attr = $productModel->getResource()->getAttribute("color");
			$color_label = $attr->getSource()->getOptionText($product->getData("color"));
			$currentattset["color"] = $color_label; 
			$productMode2= Mage::getModel('catalog/product');
			$attr2 = $productMode2->getResource()->getAttribute("tamanhoglobal");
		    $tamanhoglobal_label = $attr2->getSource()->getOptionText($product->getData("tamanhoglobal"));
			
			$currentattset["tamanhoglobal"] = $tamanhoglobal_label; 
			
			
			
			$orditem['items'][] = $currentattset;
            
        }
		$orders[]  = $orditem ;
		  
        }
        return $orders;
    }

    /**
     * Retrieve full order information
     *
     * @param string $orderIncrementId
     * @return array
     */
    public function info($orderIncrementId)
    {
        $order = $this->_initOrder($orderIncrementId);

        if ($order->getGiftMessageId() > 0) {
            $order->setGiftMessage(
                Mage::getSingleton('giftmessage/message')->load($order->getGiftMessageId())->getMessage()
            );
        }

        $result = $this->_getAttributes($order, 'order');

        $result['shipping_address'] = $this->_getAttributes($order->getShippingAddress(), 'order_address');
        $result['billing_address']  = $this->_getAttributes($order->getBillingAddress(), 'order_address');
        $result['items'] = array();

        foreach ($order->getAllItems() as $item) {
            if ($item->getGiftMessageId() > 0) {
                $item->setGiftMessage(
                    Mage::getSingleton('giftmessage/message')->load($item->getGiftMessageId())->getMessage()
                );
            }

            $result['items'][] = $this->_getAttributes($item, 'order_item');
        }

        $result['payment'] = $this->_getAttributes($order->getPayment(), 'order_payment');

        $result['status_history'] = array();

        foreach ($order->getAllStatusHistory() as $history) {
            $result['status_history'][] = $this->_getAttributes($history, 'order_status_history');
        }
       
       
    
          //------------------------adding custom fields number, city, etc starts ---ends

        return $result;
    }

    /**
     * Add comment to order
     *
     * @param string $orderIncrementId
     * @param string $status
     * @param string $comment
     * @param boolean $notify
     * @return boolean
     */
    public function addComment($orderIncrementId, $status, $comment = '', $notify = false)
    {
        $order = $this->_initOrder($orderIncrementId);

        $historyItem = $order->addStatusHistoryComment($comment, $status);
        $historyItem->setIsCustomerNotified($notify)->save();


        try {
            if ($notify && $comment) {
                $oldStore = Mage::getDesign()->getStore();
                $oldArea = Mage::getDesign()->getArea();
                Mage::getDesign()->setStore($order->getStoreId());
                Mage::getDesign()->setArea('frontend');
            }

            $order->save();
            $order->sendOrderUpdateEmail($notify, $comment);
            if ($notify && $comment) {
                Mage::getDesign()->setStore($oldStore);
                Mage::getDesign()->setArea($oldArea);
            }

        } catch (Mage_Core_Exception $e) {
            $this->_fault('status_not_changed', $e->getMessage());
        }

        return true;
    }

	
	 public function affStatus($orderIncrementId, $status, $comment = '', $notify = false)
    {
        $order = $this->_initOrder($orderIncrementId);

        $historyItem = $order->addStatusHistoryComment($comment, $status);
        $historyItem->setIsCustomerNotified($notify)->save();


        try {
            if ($notify && $comment) {
                $oldStore = Mage::getDesign()->getStore();
                $oldArea = Mage::getDesign()->getArea();
                Mage::getDesign()->setStore($order->getStoreId());
                Mage::getDesign()->setArea('frontend');
            }

            $order->save();
            $order->sendOrderUpdateEmail($notify, $comment);
            if ($notify && $comment) {
                Mage::getDesign()->setStore($oldStore);
                Mage::getDesign()->setArea($oldArea);
            }

        } catch (Mage_Core_Exception $e) {
            $this->_fault('status_not_changed', $e->getMessage());
        }

        return true;
    }

    /**
     * Hold order
     *
     * @param string $orderIncrementId
     * @return boolean
     */
    public function hold($orderIncrementId)
    {
        $order = $this->_initOrder($orderIncrementId);

        try {
            $order->hold();
            $order->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('status_not_changed', $e->getMessage());
        }

        return true;
    }

    /**
     * Unhold order
     *
     * @param string $orderIncrementId
     * @return boolean
     */
    public function unhold($orderIncrementId)
    {
        $order = $this->_initOrder($orderIncrementId);

        try {
            $order->unhold();
            $order->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('status_not_changed', $e->getMessage());
        }

        return true;
    }
	  public function affupd($orderIncrementId)
    {
        $order = $this->_initOrder($orderIncrementId);

        try {
            $order->unhold();
            $order->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('status_not_changed', $e->getMessage());
        }

        return true;
    }

    /**
     * Cancel order
     *
     * @param string $orderIncrementId
     * @return boolean
     */
    public function cancel($orderIncrementId)
    {
        $order = $this->_initOrder($orderIncrementId);

        if (Mage_Sales_Model_Order::STATE_CANCELED == $order->getState()) {
            $this->_fault('status_not_changed');
        }
        try {
            $order->cancel();
            $order->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('status_not_changed', $e->getMessage());
        }
        if (Mage_Sales_Model_Order::STATE_CANCELED != $order->getState()) {
            $this->_fault('status_not_changed');
        }
        return true;
    }

} // Class Mage_Sales_Model_Order_Api End