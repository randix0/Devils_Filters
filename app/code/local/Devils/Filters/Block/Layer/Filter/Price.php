<?php

class Devils_Filters_Block_Layer_Filter_Price extends Mage_Catalog_Block_Layer_Filter_Price
{
    private $_params;

    public function __construct()
    {
        parent::__construct();
        $this->_params = Mage::app()->getRequest()->getParams();
    }

    public function getIsSelected($param, $value)
    {
        if (!empty($this->_params[$param])) {
            if (is_array($this->_params[$param])) {
                if (in_array($value, $this->_params[$param])) {
                    return true;
                }
            } else {
                if ($this->_params[$param] == $value) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Retrieve filter items
     *
     * @return array
     */
    public function getMaxPriceInt()
    {
        return $this->_filter->getMaxPriceInt();
    }
}
