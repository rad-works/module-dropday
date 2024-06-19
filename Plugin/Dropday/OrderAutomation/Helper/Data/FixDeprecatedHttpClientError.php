<?php
declare(strict_types=1);

namespace DmiRud\Dropday\Plugin\Dropday\OrderAutomation\Helper\Data;

use Dropday\OrderAutomation\Helper\Data;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;

/**
 * Fix deprecated Http client error.
 */
class FixDeprecatedHttpClientError
{
    public function __construct(private readonly CurlFactory $curlFactory)
    {
    }

    /**
     * Provide and set new Http client
     *
     * @param Data $subject
     * @param \Closure $proceed
     * @return Curl
     */
    public function aroundGetClient(Data $subject, \Closure $proceed): Curl
    {
        $client = $this->curlFactory->create();
        $client->addHeader('Content-Type', 'application/json');
        $client->addHeader('Accept', 'application/json');
        $client->addHeader('account-id', $subject->getAccountId());
        $client->addHeader('api-key',  $subject->getApiKey());

        return $client;
    }
}