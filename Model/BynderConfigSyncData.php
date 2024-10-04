<?php

namespace DamConsultants\Idex\Model;

class BynderConfigSyncData extends \Magento\Framework\Model\AbstractModel
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
     * Idex Syc Data
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(\DamConsultants\Idex\Model\ResourceModel\BynderConfigSyncData::class);
    }
}
