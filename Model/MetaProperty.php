<?php

namespace DamConsultants\Idex\Model;

class MetaProperty extends \Magento\Framework\Model\AbstractModel
{
    protected const CACHE_TAG = 'DamConsultants_Idex';

    /**
     * @var $_cacheTag
     */
    protected $_cacheTag = 'DamConsultants_Idex';

    /**
     * @var $_eventPrefix
     */
    protected $_eventPrefix = 'DamConsultants_Idex';

    /**
     * Meta Property
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(\DamConsultants\Idex\Model\ResourceModel\MetaProperty::class);
    }
}
