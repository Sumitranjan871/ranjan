<?php

class Leadtech_City_Model_Mysql4_City extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the city_id refers to the key field in your database table.
        $this->_init('city/city', 'city_id');
    }
}