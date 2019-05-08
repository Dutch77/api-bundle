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

class MessageResponseData extends ResponseData
{
    const DATA__MESSAGE = 'message';
    /**
     * @var string
     */
    protected $message;

    /**
     * ResourceResponseData constructor.
     *
     * @param string $message
     * @param array|null $links
     * @param array|null $metadata
     */
    public function __construct($message = "", ?array $links = [], ?array $metadata = [])
    {
        $this->message = $message;
        $this->links = $links;
        $this->metadata = $metadata;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return MessageResponseData
     */
    public function setMessage(string $message): MessageResponseData
    {
        $this->message = $message;
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
                    MessageResponseData::DATA__MESSAGE => $this->getMessage()
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