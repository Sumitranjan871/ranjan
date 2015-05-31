<?php
class Leadtech_City_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction(){
		$stateId = $this->getRequest()->getParam('state_id');
		$region = Mage::getModel('directory/region')->load($stateId);
		$state_code = $region->getCode();
		Mage::log($state_code, null, "suraj.log")	;
		$selectedCity = "abc" ;
        $result=array();
        $result['mycities']=$this->getCitiesAsDropdown($selectedCity,$state_code);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/city?id=15 
    	 *  or
    	 * http://site.com/city/id/15 	
    	 */
    	/* 
		$city_id = $this->getRequest()->getParam('id');

  		if($city_id != null && $city_id != '')	{
			$city = Mage::getModel('city/city')->load($city_id)->getData();
		} else {
			$city = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($city == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$cityTable = $resource->getTableName('city');
			
			$select = $read->select()
			   ->from($cityTable,array('city_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$city = $read->fetchRow($select);
		}
		Mage::register('city', $city);
		*/

		
		//$this->loadLayout();     
		//$this->renderLayout();
    }
	
	  public function getCities($stateId)
    {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $tableName = $resource->getTableName('ciudades');
		
        $query = "SELECT * FROM ".$tableName." WHERE state_id = "."'".$stateId."'";
        $results = $readConnection->fetchAll($query);
        $cities = array();

        if(count($results) > 0)
        {
            foreach($results as $city)
            {
                $cityId = $city['id'];
                $cityName = $city['city_name'];
                $cities[$cityId] = $cityName;
            }
        }
        return $cities;
    }

    public function getCitiesAsDropdown($selectedCity = '',$stateId)
    {
		
        $cities = $this->getCities($stateId);
        $options = '';
        if(count($cities) > 0)
        {
            foreach($cities as $city)
            {
                $isSelected = $selectedCity == $city ? ' selected="selected"' : null;
                $options .= '<option value="' . $city . '"' . $isSelected . '>' . $city . '</option>';
            }
        }
        return $options;
    }
}