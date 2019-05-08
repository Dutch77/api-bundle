<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 26. 3. 2019
 * Time: 14:34
 */

namespace Asyf\ApiBundle\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ResourceResponseData extends ResponseData
{
    const DATA__RESULT = 'result';
    /**
     * @var mixed
     */
    protected $result;

    /**
     * ResourceResponseData constructor.
     *
     * @param null $result
     * @param array|null $links
     * @param array|null $metadata
     */
    public function __construct($result = null, ?array $links = [], ?array $metadata = [])
    {
        $this->result = $result;
        $this->links = $links;
        $this->metadata = $metadata;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     *
     * @return ResourceResponseData
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @param int|null $status
     * @param array|null $headers
     * @param bool|null $json
     *
     * @return Response
     */
    public function buildResponse(?int $status = Response::HTTP_OK, ?array $headers = [], ?bool $json = false): Response
    {
        return new JsonResponse(
            [
                ResponseData::DATA => [
                    ResourceResponseData::DATA__RESULT => $this->getResult()
                ],
                ResponseData::METADATA => $this->getMetadata(),
                ResponseData::LINKS => $this->getLinks()
            ],
            $status,
            $headers,
            $json
        );
    }
}