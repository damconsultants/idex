<?php

namespace DamConsultants\Idex\Model\ResourceModel\Collection;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Collection
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(
            \DamConsultants\Idex\Model\Bynder::class,
            \DamConsultants\Idex\Model\ResourceModel\Bynder::class
        );
    }
}
