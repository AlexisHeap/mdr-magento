<?php

namespace Cimaco\AddToMyEventLink\ViewModel;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSesion;

class ProductInfo implements ArgumentInterface
{
    /**
     * @var Registry Magento's global reigstry to fetch current product
     */
    protected $registry;

    /**
     * @var ImageHelper Helper to generate product image URLs
     */
    protected $imageHelper;

    /**
     * @var Product|Null the current product from registry
     */
    protected $product;

    /**
     * @var Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    public function __construct(
        Registry $registry,
        ImageHelper $imageHelper,
        PriceHelper $priceHelper,
        Curl $curl,
        CategoryRepositoryInterface $categoryRepository,
        CustomerSesion $customerSession
    ) {
        $this->registry = $registry;
        $this->imageHelper = $imageHelper;
        $this->product = $this->registry->registry('current_product');
        $this->priceHelper = $priceHelper;
        $this->curl = $curl;
        $this->categoryRepository = $categoryRepository;
        $this->customerSession = $customerSession; 
    }

    /**
     * Returns product image URL
     * 
     * @return string|null
     */
    public function getImageUrl()
    {
        if (!$this->product) {
            return null;
        }
        return $this->imageHelper->init($this->product, 'product_small_image')->getUrl();
    }

    /**
     * Returns formatted product price.
     * 
     * @return string
     */
    public function getPrice()
    {
        if (!$this->product) {
            return '';
        }
        $amount = $this->product->getFinalPrice();
        return $this->priceHelper->currency($amount, true, false);
    }

    /**
     * Retrieves events from an external API using the customer's email.
     */
    public function getEvents() {
        $email = $this->getLoginUsername();
        if (!$email) {
            return null;
        }

        $queryParams = http_build_query(['email' => $email]);
        $url = "https://dev-mdr.heapstash.cloud/mdr/store/events/occ?$queryParams";
        $this->curl->get($url);
        $response = $this->curl->getBody();

        return json_decode($response, true);
    }

    /**
     * Returns the product name.
     * 
     * @return string
     */
    public function getProductName() {
        if (!$this->product) {
            return '';
        }
        return $this->product ? $this->product->getName() : '';
    }

    /**
     * Returns the first product category name.
     * 
     * @return string|null
     */
    public function getCategory()
    {
        if (!$this->product) {
        return [];
        }

        $categoryIds = $this->product->getCategoryIds(); 

        $categories = null;
        foreach ($categoryIds as $categoryId) {
            try {
                $category = $this->categoryRepository->get($categoryId);
                $categories[] = $category->getName(); 
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            }
        }

        return $categories[0];
    }

    /**
     * Returns the email of the logged-in customer or null if guest
     * 
     * @return string|null
     */
    public function getLoginUsername(): ?string {
        if ($this->customerSession->isLoggedIn()) {
            return $this->customerSession->getCustomer()->getEmail();
        }
        return null;
    }
}
