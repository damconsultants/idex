<?php

namespace DamConsultants\Idex\Model\ResourceModel\Collection;

class BynderMediaTableCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    
    /**
     * BynderConfigSyncDataCollection
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(
            \DamConsultants\Idex\Model\BynderMediaTable::class,
            \DamConsultants\Idex\Model\ResourceModel\BynderMediaTable::class
        );
    }
}
