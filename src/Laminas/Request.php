<?php

namespace Laminas\Psr7Bridge\Laminas;

use Laminas\Http\Header\Cookie;
use Laminas\Http\PhpEnvironment\Request as BaseRequest;
use Laminas\Stdlib\Parameters;

class Request extends BaseRequest
{
    /**
     * @var array
     */
    private $attributes;

    /**
     * Overload constructor.
     *
     * This method overloads the original constructor to accept the various
     * request metadata instead of pulling from superglobals.
     *
     * @param string $method
     * @param string|\Psr\Http\Message\UriInterface $uri
     * @param array $headers
     * @param array $cookies
     * @param array $queryStringArguments
     * @param array $postParameters
     * @param array $uploadedFiles
     * @param array $serverParams
     * @param array $attributes
     */
    public function __construct(
        $method,
        $uri,
        array $headers,
        array $cookies,
        array $queryStringArguments,
        array $postParameters,
        array $uploadedFiles,
        array $serverParams,
        array $attributes
    ) {
        $this->setAllowCustomMethods(true);

        $this->setMethod($method);
        // Remove the "http(s)://hostname" part from the URI
        $this->setRequestUri(preg_replace('#^[^/:]+://[^/]+#', '', (string) $uri));
        $this->setUri((string) $uri);

        $headerCollection = $this->getHeaders();
        foreach ($headers as $name => $values) {
            foreach ($values as $value) {
                $headerCollection->addHeaderLine($name, $value);
            }
        }

        if (! empty($cookies)) {
            $headerCollection->addHeader(new Cookie($cookies));
        }

        $this->setQuery(new Parameters($queryStringArguments));
        $this->setPost(new Parameters($postParameters));
        $this->setFiles(new Parameters($uploadedFiles));

        // Do not use `setServerParams()`, as that extracts headers, URI, etc.
        $this->serverParams = new Parameters($serverParams);

        $this->attributes = $attributes;
    }


    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function getAttribute($attribute, $default = null)
    {
        if (!\array_key_exists($attribute, $this->attributes)) {
            return $default;
        }

        return $this->attributes[$attribute];
    }
}
