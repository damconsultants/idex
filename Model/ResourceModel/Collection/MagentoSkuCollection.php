<?php

namespace DamConsultants\Idex\Model\ResourceModel\Collection;

class MagentoSkuCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    
    /**
     * MagentoSkuCollection
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(
            \DamConsultants\Idex\Model\MagentoSku::class,
            \DamConsultants\Idex\Model\ResourceModel\MagentoSku::class
        );
    }
}
