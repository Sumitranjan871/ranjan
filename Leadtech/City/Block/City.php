<?php
class Leadtech_City_Block_City extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getCity()     
     { 
        if (!$this->hasData('city')) {
            $this->setData('city', Mage::registry('city'));
        }
        return $this->getData('city');
        
    }
}