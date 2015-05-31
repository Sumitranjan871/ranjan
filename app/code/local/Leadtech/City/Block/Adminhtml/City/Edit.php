<?php

class Leadtech_City_Block_Adminhtml_City_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'city';
        $this->_controller = 'adminhtml_city';
        
        $this->_updateButton('save', 'label', Mage::helper('city')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('city')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('city_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'city_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'city_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('city_data') && Mage::registry('city_data')->getId() ) {
            return Mage::helper('city')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('city_data')->getTitle()));
        } else {
            return Mage::helper('city')->__('Add Item');
        }
    }
}