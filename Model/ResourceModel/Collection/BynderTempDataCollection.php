<?php

namespace DamConsultants\Idex\Model\ResourceModel\Collection;

class BynderTempDataCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    
    /**
     * BynderConfigSyncDataCollection
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(
            \DamConsultants\Idex\Model\BynderTempData::class,
            \DamConsultants\Idex\Model\ResourceModel\BynderTempData::class
        );
    }
}
