<?php

class Devils_Filters_Model_Layer_Filter_Price extends Mage_Catalog_Model_Layer_Filter_Price
{

    /**
     * Get all filter items
     *
     * @return array
     */
    public function getItems0()
    {
        if (is_null($this->_items)) {
            $this->_initItems();
        }
        return $this->_items;
    }

    /**
     * Apply price range filter
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param $filterBlock
     *
     * @return Mage_Catalog_Model_Layer_Filter_Price
     */
    public function apply0(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        /**
         * Filter must be string: $fromPrice-$toPrice
         */
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter) {
            return $this;
        }

        //validate filter
        $filterParams = explode(',', $filter);
        $filter = $this->_validateFilter($filterParams[0]);
        if (!$filter) {
            return $this;
        }

        list($from, $to) = $filter;

        $this->setInterval(array($from, $to));

        $priorFilters = array();
        for ($i = 1; $i < count($filterParams); ++$i) {
            $priorFilter = $this->_validateFilter($filterParams[$i]);
            if ($priorFilter) {
                $priorFilters[] = $priorFilter;
            } else {
                //not valid data
                $priorFilters = array();
                break;
            }
        }
        if ($priorFilters) {
            $this->setPriorIntervals($priorFilters);
        }

        $this->_applyPriceRange();
        $this->getLayer()->getState()->addFilter($this->_createItem(
            $this->_renderRangeLabel(empty($from) ? 0 : $from, $to),
            $filter
        ));

        return $this;
    }

    /**
     * Get data for build price filter items
     *
     * @return array
     */
    protected function _getItemsData0()
    {
        if (Mage::app()->getStore()->getConfig(self::XML_PATH_RANGE_CALCULATION) == self::RANGE_CALCULATION_IMPROVED) {
            return $this->_getCalculatedItemsData();
        } elseif ($this->getInterval()) {
            return array();
        }

        $range      = $this->getPriceRange();
        $dbRanges   = $this->getRangeItemCounts($range);
        $data       = array();

        if (!empty($dbRanges)) {
            $lastIndex = array_keys($dbRanges);
            $lastIndex = $lastIndex[count($lastIndex) - 1];

            foreach ($dbRanges as $index => $count) {
                $fromPrice = ($index == 1) ? '' : (($index - 1) * $range);
                $toPrice = ($index == $lastIndex) ? '' : ($index * $range);

                $data[] = array(
                    'label' => $this->_renderRangeLabel($fromPrice, $toPrice),
                    'value' => $fromPrice . '-' . $toPrice,
                    'count' => $count,
                );
            }
        }

        return $data;
    }


    /**
     * Get maximum price from layer products set
     *
     * @return float
     */
    public function getSliderMinPrice()
    {
        $minPrice = $this->getData('devils_min_price_int');
        if (is_null($minPrice)) {
            $collection = clone $this->getLayer()->getProductCollection();
//            $collection->getSelect()->reset(Zend_Db_Select::WHERE);
            $minPrice = $collection->getMinPrice();
            $minPrice = floor($minPrice);
            $this->setData('devils_min_price_int', $minPrice);
        }

        return $minPrice;
    }

    /**
     * Get maximum price from layer products set
     *
     * @return float
     */
    public function getSliderMaxPrice()
    {
        $maxPrice = $this->getData('devils_max_price_int');
        if (is_null($maxPrice)) {
            $collection = clone $this->getLayer()->getProductCollection();
//            $collection->getSelect()->reset(Zend_Db_Select::WHERE);
            $maxPrice = $collection->getMaxPrice();
            $maxPrice = floor($maxPrice);
            $this->setData('devils_max_price_int', $maxPrice);
        }

        return $maxPrice;
    }
}