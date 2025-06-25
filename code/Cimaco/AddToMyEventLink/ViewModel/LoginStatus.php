<?php declare(strict_types=1);

namespace Cimaco\AddToMyEventLink\ViewModel;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * ViewModel to check if the customer is logged in.
 */
class LoginStatus implements ArgumentInterface {

    /**
     * @var Session Magento customer session
     */
    protected Session $customerSession;

    public function __construct(
        Session $customerSession
    )
    {
        $this->customerSession = $customerSession;
    }

    /**
     * Check whether the customer is logged in.
     * 
     * @return bool
     */
    public function isTheCustomerLoggedIn() : bool {
        return $this->customerSession->isLoggedIn();
    }
}