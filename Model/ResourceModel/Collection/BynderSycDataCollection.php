<?php

namespace DamConsultants\Idex\Model\ResourceModel\Collection;

class BynderSycDataCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    
    /**
     * BynderSycDataCollection
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(
            \DamConsultants\Idex\Model\BynderSycData::class,
            \DamConsultants\Idex\Model\ResourceModel\BynderSycData::class
        );
    }
}
