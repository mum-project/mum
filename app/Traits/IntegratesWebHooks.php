<?php

namespace App\Traits;

use App\Exceptions\HttpRequestFailedException;
use App\Exceptions\IntegrationsDisabledException;
use App\Exceptions\InvalidUrlException;
use App\WebHookIntegration;
use function config;
use const FILTER_VALIDATE_URL;
use function filter_var;
//use Http\Client\Exception\RequestException;
//use Http\Httplug\Facade\Httplug;
use Psr\Http\Message\ResponseInterface;
use function str_replace;
use function urlencode;

trait IntegratesWebHooks
{
    /**
     * Queries the URL specified by the given integration and returns the request output.
     *
     * @param WebHookIntegration $integration
     * @param array|null         $placeholders
     * @return ResponseInterface
     * @throws IntegrationsDisabledException
     * @throws HttpRequestFailedException
     */
    public function callWebHook(WebHookIntegration $integration, array $placeholders = null)
    {
        $this->checkWebHookIntegrationsEnabled();

        $this->checkIntegrationUrl($integration);

        $url = $this->buildEncodedUrl($integration, $placeholders);

        try {
            $response = $this->sendHttpRequest($url);

            if ($response->getStatusCode() >= 400) {
                throw new HttpRequestFailedException($response);
            }
        } catch (RequestException $exception) {
            throw new HttpRequestFailedException($exception->getRequest());
        }

        return $response;
    }

    /**
     * Throws an IntegrationsDisabledException if integrations are disabled generally
     * or if web hook integrations are disabled.
     *
     * @throws IntegrationsDisabledException
     */
    private function checkWebHookIntegrationsEnabled()
    {
        if (!config('integrations.enabled.generally')) {
            throw new IntegrationsDisabledException();
        }

        if (!config('integrations.enabled.web_hooks')) {
            throw new IntegrationsDisabledException("Web hook integrations are disabled.");
        }
    }

    /**
     * Checks for the integration to have a valid URL.
     *
     * @param WebHookIntegration $integration
     */
    private function checkIntegrationUrl(WebHookIntegration $integration)
    {
        if (!filter_var($integration->getUrl(), FILTER_VALIDATE_URL)) {
            throw new InvalidUrlException($integration->getUrl());
        }
    }

    /**
     * Builds an encoded URL from the integration model's URL and any given placeholders.
     *
     * @param WebHookIntegration $integration
     * @param array|null         $placeholders
     * @return string
     */
    private function buildEncodedUrl(WebHookIntegration $integration, array $placeholders = null)
    {
        $url = $integration->getUrl();

        if ($placeholders) {
            foreach ($placeholders as $placeholder => $value) {
                $url = str_replace('%{' . $placeholder . '}', urlencode($value), $url);
            }
        }

        return $url;
    }

    /**
     * Send a HTTP Request to the provided URL via HTTPlug.
     *
     * @param string $url
     * @param string $method
     * @return ResponseInterface
     */
    private function sendHttpRequest(string $url, string $method = 'GET')
    {
//        $factory = app()->make('httplug.message_factory.default');
//        $request = $factory->createRequest($method, $url);

//        return Httplug::sendRequest($request);
    }
}
