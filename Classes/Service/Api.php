<?php

namespace Kitzberger\Usebouncer\Service;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;
use TYPO3\CMS\Core\Error\Http\UnauthorizedException;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;

class Api implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ?string $reason = null;

    public function __construct(private RequestFactoryInterface $requestFactory)
    {
    }

    /**
     * Check a given mail address via usebouncer.com
     *
     * @param  string $mail
     * @return bool true if valid
     */
    public function checkMail(string $mail): bool
    {
        $this->logger->info('Checking mail address: ' . $mail);

        # See https://docs.usebouncer.com/ for details.
        $url = 'https://api.usebouncer.com/v1.1/email/verify?email=' . rawurlencode($mail);

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

        $this->reason = null;

        // Return a PSR-7 compliant response object
        $response = $this->requestFactory->request($url, 'GET', $additionalOptions);

        // Get the content as a string on a successful request
        switch ($response->getStatusCode()) {
            case 200:
                $response = $this->parseBody($response);
                $this->logger->debug(print_r($response, true));
                $this->reason = $response['status'];
                return $response['status'] === 'deliverable';
            case 401:
                $this->logger->log(LogLevel::ERROR, 'Resource requires login in Usebouncer');
                throw new UnauthorizedException(
                    '401 from Usebouncer! Check ENV Variables for Usebouncer Credentials.',
                    1694012897
                );
            case 402:
                $this->logger->log(LogLevel::ERROR, 'Resource requires payment in Usebouncer');
                throw new UnauthorizedException(
                    '402 from Usebouncer! Check your Usebouncer subscription.',
                    1694012899
                );
            case 403:
                $response = $this->parseBody($response);
                // Todo: maybe actually look at response?
                $this->logger->log(LogLevel::WARNING, 'Resource requires login in Usebouncer');
                throw new UnauthorizedException(
                    '403 from Usebouncer!',
                    1694012898
                );
            case 404:
                $response = $this->parseBody($response);
                // Todo: maybe actually look at response?
                $this->logger->log(LogLevel::WARNING, 'Resource not found in Usebouncer');
                throw new ResourceDoesNotExistException('404 from Usebouncer!');
            default:
                $message = sprintf('Unhandled response code %d from Usebouncer!', $response->getStatusCode());
                $this->logger->log(LogLevel::ERROR, $message);
                throw new \Exception($message);
        }
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    protected function parseBody($response)
    {
        if ($response->getHeaderLine('Content-Type') === 'application/json') {
            $content = json_decode((string) $response->getBody()->getContents(), true);

            if (json_last_error()) {
                $this->logger->log(LogLevel::ERROR, json_last_error_msg());
            }

            return $content;
        }

        $this->logger->log(LogLevel::ERROR, 'Unknown Content-Type: ' . $response->getHeaderLine('Content-Type'));
        throw new \Exception('Unknown Content-Type: ' . $response->getHeaderLine('Content-Type'));
    }
}
