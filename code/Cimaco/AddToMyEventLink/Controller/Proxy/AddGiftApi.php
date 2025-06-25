<?php 

namespace Cimaco\AddToMyEventLink\Controller\Proxy;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\HTTP\Client\Curl;

/**
 * Controller to proxy a POST request to an external API to add a gift to an event.
 * CSRF validation is bypassed to allow external POST requests.
 */

class AddGiftApi extends Action implements CsrfAwareActionInterface {
    /**
     * @var Magento\Framework\Controller\Result\JsonFacotry
     */
    protected $jsonFactory;

    /**
     * @var Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Curl $curl
    )
    {
        $this->jsonFactory = $jsonFactory;
        $this->curl = $curl;
        parent::__construct($context);
    }

    /**
     * Disable CSRF exception by returning null.
     */
    public function createCsrfValidationException(\Magento\Framework\App\RequestInterface $request): ?\Magento\Framework\App\Request\InvalidRequestException
    {
        return null; // Disable CSRF exception
    }

    /**
     * Always allow the request by returning true.
     */
    public function validateForCsrf(\Magento\Framework\App\RequestInterface $request): ?bool
    {
        return true; // Accept all requests
    }

    /**
     * Executes the controller logic to forward gift data to an external API.
     * 
     * @return Json
     */
    public function execute()
    {
       $result = $this->jsonFactory->create();

       $eventId = $this->getRequest()->getParam('eventId');

       if(!$eventId) {
        return $result->setData([
            'success' => false,
            'message' => 'EventId is required'
        ]);
       }

        $body = file_get_contents('php://input');
        $payload = json_decode($body, true);
        $addGiftEndpoint = "https://dev-mdr.heapstash.cloud/mdr/store/events/{$eventId}/occ-gifts";

        try {
            $this->curl->addHeader("Content-type", "application/json");
            $this->curl->post($addGiftEndpoint, json_encode($payload));

            return $result->setData([
                'success' => true,
                'data' => $payload
            ]);
        }catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}