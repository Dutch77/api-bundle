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

class CollectionResponseData extends ResponseData
{
    const DATA__RESULTS = 'results';

    /**
     * @var array
     */
    protected $results;

    public function __construct(?array $results = [], ?array $links = [], ?array $metadata = [])
    {
        $this->results = $results;
        $this->links = $links;
        $this->metadata = $metadata;
    }

    /**
     * @return array
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * @param array $results
     *
     * @return CollectionResponseData
     */
    public function setResults(array $results): CollectionResponseData
    {
        $this->results = $results;
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
                    CollectionResponseData::DATA__RESULTS => $this->getResults()
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