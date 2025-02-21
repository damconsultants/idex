<?php

namespace DamConsultants\Idex\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class SkuList extends Action
{
    protected $resultPageFactory;

    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('DamConsultants_Idex::menu_item9');
        $resultPage->getConfig()->getTitle()->prepend(__('Bynder SKU List'));

        return $resultPage;
    }
}
