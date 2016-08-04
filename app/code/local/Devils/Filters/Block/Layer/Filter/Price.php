<?php

class Devils_Filters_Block_Layer_Filter_Price extends Mage_Catalog_Block_Layer_Filter_Price
{
    private $_params;
    private $_minPrice;
    private $_maxPrice;

    public function __construct()
    {
        parent::__construct();
        $this->_params = Mage::app()->getRequest()->getParams();
    }

    public function getFilter()
    {
        return $this->_filter;
    }

    public function getRequestedRange($param)
    {
        if (!empty($this->_params[$param])) {
            $paramValue = $this->_params[$param];
            if (is_string($paramValue)) {
                list($min, $max) = explode('-', $paramValue);
                return array(
                    (int)$min,
                    (int)$max
                );
            }
        }
        return array(
            $this->getMinPrice(),
            $this->getMaxPrice()
        );
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


    public function getMinPrice()
    {
        if (is_null($this->_minPrice)){
            $this->_minPrice = $this->_filter->getSliderMinPrice();
        }
        return $this->_minPrice;
    }

    public function getMaxPrice()
    {
        if (is_null($this->_maxPrice)){
            $this->_maxPrice = $this->_filter->getSliderMaxPrice();
        }
        return $this->_maxPrice;
    }
}
