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

class ErrorResponseData extends ResponseData
{
    const DATA__ERRORS = 'errors';

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * ErrorResponseData constructor.
     *
     * @param array $errors
     * @param array|null $links
     * @param array|null $metadata
     */
    public function __construct(?array $errors = [], ?array $links = [], ?array $metadata = [])
    {
        $this->errors = $errors;
        $this->links = $links;
        $this->metadata = $metadata;
    }

    /**
     * @param string $errorName
     * @param $errorValue
     *
     * @return ErrorResponseData
     */
    public function addError(string $errorName, $errorValue): ErrorResponseData {
        $this->errors[$errorName] = $errorValue;
        return $this;
    }

    /**
     * @param array $errors
     *
     * @return ErrorResponseData
     */
    public function addErrors(array $errors): ErrorResponseData {
        foreach ($errors as $errorName => $errorValue) {
            $this->errors[$errorName] = $errorValue;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     *
     * @return ErrorResponseData
     */
    public function setErrors(array $errors): ErrorResponseData
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * @param int|null $status
     * @param array|null $headers
     * @param bool|null $json
     *
     * @return Response
     */
    public function buildResponse(?int $status = Response::HTTP_BAD_REQUEST, ?array $headers = [], ?bool $json = false): Response
    {
        return new JsonResponse(
            [
                ResponseData::DATA => [
                    ErrorResponseData::DATA__ERRORS => $this->getErrors(),
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