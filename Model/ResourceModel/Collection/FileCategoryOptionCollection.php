<?php

namespace DamConsultants\Idex\Model\ResourceModel\Collection;

class FileCategoryOptionCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Collection
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(
            \DamConsultants\Idex\Model\FileCategoryOption::class,
            \DamConsultants\Idex\Model\ResourceModel\FileCategoryOption::class
        );
    }
}
