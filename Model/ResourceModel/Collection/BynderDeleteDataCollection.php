<?php

namespace DamConsultants\Idex\Model\ResourceModel\Collection;

class BynderDeleteDataCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    
    /**
     * BynderConfigSyncDataCollection
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(
            \DamConsultants\Idex\Model\BynderDeleteData::class,
            \DamConsultants\Idex\Model\ResourceModel\BynderDeleteData::class
        );
    }
}
