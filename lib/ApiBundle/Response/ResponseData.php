<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 26. 3. 2019
 * Time: 14:34
 */

namespace Asyf\ApiBundle\Response;

use Asyf\ApiBundle\Exception\Response\NonScalarValueException;
use Symfony\Component\HttpFoundation\Response;

abstract class ResponseData
{
    const DATA = 'data';
    const LINKS = 'links';
    const METADATA = 'metadata';

    /**
     * @var array
     */
    protected $links = [];

    /**
     * @var array
     */
    protected $metadata = [];

    /**
     * @param string $key
     * @param $value
     *
     * @return ResponseData
     * @throws NonScalarValueException
     */
    public function addLink(string $key, $value): ResponseData
    {
        if (is_scalar($value)) {
            $this->links[$key] = $value;
        } else {
            throw new NonScalarValueException(sprintf('Link with key "%s" is not a scalar value.', $key));
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    public function getLinkByKey(string $key)
    {
        if (isset($this->getLinks()[$key])) {
            return $this->getLinks()[$key];
        }
        return null;
    }

    /**
     * @param array $links
     *
     * @return ResponseData
     */
    public function setLinks(array $links): ResponseData
    {
        $this->links = $links;
        return $this;
    }

    /**
     * @param string $key
     * @param $value
     *
     * @return ResponseData
     * @throws NonScalarValueException
     */
    public function addMetadata(string $key, $value): ResponseData
    {
        if (is_scalar($value)) {
            $this->metadata[$key] = $value;
        } else {
            throw new NonScalarValueException(sprintf('Metadata with key "%s" is not a scalar value.', $key));
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    public function getMetadataByKey(string $key)
    {
        if (isset($this->getMetadata()[$key])) {
            return $this->getMetadata()[$key];
        }
        return null;
    }

    /**
     * @param array $metadata
     *
     * @return ResponseData
     */
    public function setMetadata(array $metadata): ResponseData
    {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * @param int|null $status
     * @param array|null $headers
     * @param bool|null $json
     *
     * @return Response
     */
    abstract public function buildResponse(?int $status = Response::HTTP_OK, ?array $headers = [], ?bool $json = false): Response;
}