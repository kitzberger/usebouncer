<?php

namespace Kitzberger\Usebouncer\Service;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LogLevel;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Api implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var RequestFactoryInterface */
    private $requestFactory;

    public function __construct(RequestFactoryInterface $requestFactory)
    {
        $this->requestFactory = $requestFactory;
    }

    public function checkMail(string $mail): bool
    {
        $this->logger->info('Checking mail address: ' . $mail);

        # See https://docs.usebouncer.com/ for details.
        $url = 'https://api.usebouncer.com/v1.1/email/verify?email=' . $mail;

        # Auth
        $username = ''; // always empty
        $password = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['usebouncer']['apiKey'] ?? '';

        $additionalOptions = [
            // Additional headers for this specific request
            'headers' => [
                'User-Agent' => 'TYPO3 11 backend',
                'Cache-Control' => 'no-cache',
                'Accept' => 'application/json',
            ],
            // Additional options, see http://docs.guzzlephp.org/en/latest/request-options.html
            'allow_redirects' => false,
            'http_errors' => false,
            #'cookies' => true,

            'auth' => [
                $username,
                $password,
            ],
        ];

        // Return a PSR-7 compliant response object
        $response = $this->requestFactory->request($url, 'GET', $additionalOptions);

        // Get the content as a string on a successful request
        switch ($response->getStatusCode()) {
            case 200:
                $response = $this->parseBody($response);
                $this->logger->debug(print_r($response, true));
                return $response['status'] !== 'deliverable';
            case 401:
                $this->logger->log(LogLevel::ERROR, 'Resource requires login in Usebouncer');
                throw new UnauthorizedException(
                    '401 from Usebouncer! Check ENV Variables for Usebouncer Credentials',
                    1689607662291
                );
            case 403:
                $response = $this->parseBody($response);
                // Todo: maybe actually look at response?
                $this->logger->log(LogLevel::WARNING, 'Resource requires login in Usebouncer');
                throw new ResourceRequiresLoginException('403 from Usebouncer!');
            case 404:
                $response = $this->parseBody($response);
                // Todo: maybe actually look at response?
                $this->logger->log(LogLevel::WARNING, 'Resource not found in Usebouncer');
                throw new ResourceDoesNotExistException('404 from Usebouncer!');
            default:
                throw new \Exception('Unknown response code from Usebouncer!');
        }
    }

    protected function parseBody($response)
    {
        if ($response->getHeaderLine('Content-Type') === 'application/json') {
            $content = json_decode($response->getBody()->getContents(), true);

            if (json_last_error()) {
                $this->logger->log(LogLevel::ERROR, json_last_error_msg());
            }

            return $content;
        }

        $this->logger->log(LogLevel::ERROR, 'Unknown Content-Type: ' . $response->getHeaderLine('Content-Type'));
        throw new \Exception('Unknown Content-Type: ' . $response->getHeaderLine('Content-Type'));
    }
}
