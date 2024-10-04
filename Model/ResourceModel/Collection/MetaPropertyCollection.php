<?php

namespace DamConsultants\Idex\Model\ResourceModel\Collection;

class MetaPropertyCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * MetaPropertyCollection
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(
            \DamConsultants\Idex\Model\MetaProperty::class,
            \DamConsultants\Idex\Model\ResourceModel\MetaProperty::class
        );
    }
}
