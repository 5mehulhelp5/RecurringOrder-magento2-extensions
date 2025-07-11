<?php
/**
 * Pravams Chat Module
 * 
 * @category Pravams
 * @package Pravams_Chat
 * @copyright Copyright (c) 2023 Pravams. (http://www.pravams.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Pravams\Chat\Controller\Index;

use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultFactory;

class Refresh extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    
    protected $_chatlistData;
    
    protected $_chatlistCollection;
    
    protected $_messagelistData;
    
    protected $_messagelistCollection;

    /**
     * @var \Magento\Customer\Model\Session $_session
     */
    protected $_session;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Pravams\Chat\Model\ChatFactory $chatlistData,
        \Pravams\Chat\Model\ResourceModel\Chat\Collection $chatlistCollection,
        \Pravams\Chat\Model\ChatMessageFactory $messagelistData,
        \Pravams\Chat\Model\ResourceModel\ChatMessage\CollectionFactory $messagelistCollection,
        \Magento\Customer\Model\Session $session,
        array $data = []
    )  
    {  
        $this->_pageFactory = $pageFactory;
        $this->_chatlistData = $chatlistData;
        $this->_chatlistCollection= $chatlistCollection;
        $this->_messagelistData = $messagelistData;
        $this->_messagelistCollection= $messagelistCollection;
        $this->_session=$session;
        parent::__construct($context);
    }
    public function execute()
    {
            $session=$this->_session;
            $customerId=$session->getCustomerId();
            $chatlistCollection=$this->_chatlistCollection;
            
            $customerName=$session->getCustomerData()->getFirstName()." ".$session->getCustomerData()->getLastName();
            
            $data = $this->getRequest()->getPostValue();
            
            $chatlistCollection->addFieldToFilter('customer_id',$customerId);
            if (count($chatlistCollection) > 0) {
                $chatId = $chatlistCollection->getFirstItem()->getChatId();
                $chatlistData=$this->_chatlistData->create();
                $chatlistData->load($chatId);
            } else {
                $chatlistData=$this->_chatlistData->create();
            }
            
            $chatId=$chatlistData->getChatId();
            
            
            $messagelistCollection = $this->_messagelistCollection->create();
            $messagelistCollection->addFieldToFilter('customer_id',$customerId);
            $chat=array();
            foreach($messagelistCollection as $_messagelist){
                if ($_messagelist->getMessageType()=="customer") {
                  $chatName=$_messagelist->getCustomerName();
                } else {
                  $chatName=$_messagelist->getAdminName();
                }
                $chatTime = strtotime($_messagelist->getCreatedAt());
                $chatTimeFmt = date('d-M h:i',$chatTime);
                $chat[]=array(
                    "message" => $_messagelist->getMessage(),
                    "customer_name"=>$chatName,
                    'viewed'=>$_messagelist->getAdminViewed(),
                    'type'=>$_messagelist->getMessageType(),
                    "created_at"=>$chatTimeFmt,
                ); 
                $_messagelist->setCustomerViewed(1);
                $_messagelist->save();
            }
             
            $output=json_encode($chat);
            
            $rawResult = $this->resultFactory->create(ResultFactory::TYPE_RAW);
            return $rawResult->setContents($output);
    }
}
?>
