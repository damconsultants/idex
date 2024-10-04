<?php

namespace DamConsultants\Idex\Model\ResourceModel\Collection;

class BynderConfigSyncDataCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    
    /**
     * BynderConfigSyncDataCollection
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(
            \DamConsultants\Idex\Model\BynderConfigSyncData::class,
            \DamConsultants\Idex\Model\ResourceModel\BynderConfigSyncData::class
        );
    }
}
