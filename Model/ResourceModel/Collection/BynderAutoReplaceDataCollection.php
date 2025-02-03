<?php

namespace DamConsultants\Idex\Model\ResourceModel\Collection;

class BynderAutoReplaceDataCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    
    /**
     * BynderConfigSyncDataCollection
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(
            \DamConsultants\Idex\Model\BynderAutoReplaceData::class,
            \DamConsultants\Idex\Model\ResourceModel\BynderAutoReplaceData::class
        );
    }
}
