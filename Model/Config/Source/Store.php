<?php

namespace DamConsultants\Idex\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Store\Model\StoreManagerInterface;

class Store implements ArrayInterface
{
    protected $storeManager;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Return array of store options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
		$options[] = [
                'value' => "all_store",
                'label' => "All Stores"
            ];
        $all_stores = $this->storeManager->getStores();    
        if(count($all_stores) > 0){
            foreach ($this->storeManager->getStores() as $store) {
                $options[] = [
                    'value' => $store->getId(),
                    'label' => $store->getName()
                ];
            }
        }
        return $options;
    }
}
