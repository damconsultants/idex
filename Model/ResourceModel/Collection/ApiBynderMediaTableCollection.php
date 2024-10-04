<?php

namespace DamConsultants\Idex\Model\ResourceModel\Collection;

class ApiBynderMediaTableCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    
    /**
     * BynderConfigSyncDataCollection
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(
            \DamConsultants\Idex\Model\ApiBynderMediaTable::class,
            \DamConsultants\Idex\Model\ResourceModel\ApiBynderMediaTable::class
        );
    }
}
