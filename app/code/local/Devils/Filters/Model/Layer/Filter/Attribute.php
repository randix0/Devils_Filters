<?php

class Devils_Filters_Model_Layer_Filter_Attribute extends Mage_Catalog_Model_Layer_Filter_Attribute
{
    /**
     * Apply attribute option filter to product collection
     *
     * @param   Zend_Controller_Request_Abstract $request
     * @param   Varien_Object $filterBlock
     * @return  Mage_Catalog_Model_Layer_Filter_Attribute
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $appliedFilters = Mage::registry('devils_filters_applied');
        if (in_array($this->_requestVar, $appliedFilters)) {
            return $this;
        }

        $filterRequest = $request->getParam($this->_requestVar);
        $filters = array();

        if (!empty($filterRequest) && !is_null($filterRequest)) {
            if (!is_array($filterRequest) && !is_null($filterRequest)) {
                $filterRequest = array($filterRequest);
            }

            foreach ($filterRequest as $filter) {
                $text = $this->_getOptionText($filter);
                if ($filter && strlen($text)) {
                    $this->getLayer()->getState()->addFilter($this->_createItem($text, $filter));
                    $filters[] = (int)$filter;
                }
            }

//            $this->_items = array();
            if (!empty($filters)) {
                $this->_getResource()->applyFilterToCollection($this, $filters);
            }
        }

        $appliedFilters[] = $this->_requestVar;
        Mage::unregister('devils_filters_applied');
        Mage::register('devils_filters_applied', $appliedFilters);

        return $this;
    }

    /**
     * Get data array for building attribute filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $attribute = $this->getAttributeModel();
        $this->_requestVar = $attribute->getAttributeCode();
        if ($attribute->getAttributeCode() == 'manufacturer') {
            1+1;
        }

        $key = $this->getLayer()->getStateKey().'_'.$this->_requestVar;
        $data = $this->getLayer()->getAggregator()->getCacheData($key);

        if ($data === null) {
            $options = $attribute->getFrontend()->getSelectOptions();
            $optionsCount = $this->_getResource()->getCount($this);
//            Mage::log($this->_requestVar . ': ' . json_encode($optionsCount), null, 'devils.log', true);
            $data = array();
            foreach ($options as $option) {
                if (is_array($option['value'])) {
                    continue;
                }
                if (Mage::helper('core/string')->strlen($option['value'])) {
                    // Check filter type
                    if ($this->_getIsFilterableAttribute($attribute) == self::OPTIONS_ONLY_WITH_RESULTS) {
                        if (!empty($optionsCount[$option['value']])) {
                            $data[] = array(
                                'label' => $option['label'],
                                'value' => $option['value'],
                                'count' => $optionsCount[$option['value']],
                            );
                        }
                    }
                    else {
                        $data[] = array(
                            'label' => $option['label'],
                            'value' => $option['value'],
                            'count' => isset($optionsCount[$option['value']]) ? $optionsCount[$option['value']] : 0,
                        );
                    }
                }
            }

            $tags = array(
                Mage_Eav_Model_Entity_Attribute::CACHE_TAG.':'.$attribute->getId()
            );

            $tags = $this->getLayer()->getStateTags($tags);
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }
        return $data;
    }
}
