<?php
namespace DamConsultants\Idex\Block\Adminhtml\Catalog\Product\Form;

class BynderDoc extends \Magento\Backend\Block\Template
{
    /**
     * Block template.
     *
     * @var string
     */
    protected $_template = 'group/bynderdoc.phtml';
    /**
     * EntityId.
     *
     * @return $this
     */
    public function getEntityId()
    {
        return $this->getRequest()->getParam('id');
    }
    /**
     * Image.
     *
     * @return $this
     */
    public function getDrag()
    {
        return $this->getViewFileUrl('DamConsultants_Idex::images/drag.png');
    }
    /**
     * Image.
     *
     * @return $this
     */
    public function getDelete()
    {
        return $this->getViewFileUrl('DamConsultants_Idex::images/delete_.avif');
    }
}
