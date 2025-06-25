<?php declare(strict_types=1);

namespace Cimaco\AddToMyEventLink\Plugin;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\HTTP\ClientInterface;
use Psr\Log\LoggerInterface;

class RetrieveLoginPlugin {
    protected $login;
    protected $httpClient;

    public function __construct(
        /**
         * @var LoggerInterface Logger instance to log login attemps
         */
        LoggerInterface $login,

        /**
         * @var ClientInterface HTTP client to send external requests
         */
        ClientInterface $httpClient
    )
    {
        $this->login = $login;
        $this->httpClient = $httpClient;
    }

    /**
     * Intercepts the login procces of the customer data to retrieve the username and password.
     * 
     * @param AccountManagementInterface $subject The subject being intercepted
     * @param callable $proceed The original method callable
     * @param string $username The login username (typically email)
     * @param string $password The login password
     * 
     * @return object
     */
    public function aroundAuthenticate(
        AccountManagementInterface $subject,
        callable $proceed,
        $username,
        $password
    )
    {
        $this->login->info("Attempting login via around plugin for user: $username");

        try {
            $customer = $proceed($username, $password);

            if($customer instanceof CustomerInterface) {
                $this->login->info("Login success. Customer ID: " . $customer->getId());

                $this->sendLoginDataToAuthRegister($username, $password);
            }

            return $customer;
        } catch (\Exception $e) {
            $this->login->error("Login failder for: $username" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * This function calls the auth/register endpoint sending the login data to register the customer in the MDR suite.
     * 
     * @param string $username The customer's email
     * @param string $password The customer's password
     */
    protected function sendLoginDataToAuthRegister(string $username, string $password): void
    {
        try {
            $this->httpClient->addHeader("Content-type", "application/json");
            $this->httpClient->post(
                'https://dev-mdr.heapstash.cloud/auth/register',
                json_encode([
                    'email' => $username,
                    'username' => $username,
                    'password1' => $password,
                    'password2' => $password
                ])
            );            
            $this->login->info("auth/register - response: " . $this->httpClient->getBody());
        } catch (\Exception $e) {
            $this->login->error("Failed to send data to external API: " . $e->getMessage());
        }
    }
}
