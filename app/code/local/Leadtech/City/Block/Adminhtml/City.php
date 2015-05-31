<?php
class Leadtech_City_Block_Adminhtml_City extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_city';
    $this->_blockGroup = 'city';
    $this->_headerText = Mage::helper('city')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('city')->__('Add Item');
    parent::__construct();
  }
}