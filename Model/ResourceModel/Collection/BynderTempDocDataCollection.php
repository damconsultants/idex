<?php

namespace DamConsultants\Idex\Model\ResourceModel\Collection;

class BynderTempDocDataCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    
    /**
     * BynderConfigSyncDataCollection
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(
            \DamConsultants\Idex\Model\BynderTempDocData::class,
            \DamConsultants\Idex\Model\ResourceModel\BynderTempDocData::class
        );
    }
}
