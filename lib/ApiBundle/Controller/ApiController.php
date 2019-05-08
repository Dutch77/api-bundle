<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 26. 3. 2019
 * Time: 14:25
 */

namespace Asyf\ApiBundle\Controller;

use Asyf\ApiBundle\Configuration\PersistentCollectionFieldConfiguration;
use Asyf\ApiBundle\Form\Type\ApiType;
use Asyf\ApiBundle\Response\CollectionResponseData;
use Asyf\ApiBundle\Response\ErrorResponseData;
use Asyf\ApiBundle\Response\MessageResponseData;
use Asyf\ApiBundle\Response\ResourceResponseData;
use Asyf\ApiBundle\Service\ConfigurationBuilder;
use Asyf\ApiBundle\Service\Normalizer\NormalizerInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Asyf\ApiBundle\Exception\Configuration\MissingConfigurationOptionException;
use Asyf\ApiBundle\Exception\Configuration\OrderBy\NotMappedFieldException;
use Asyf\ApiBundle\Exception\Configuration\OrderBy\UnsupportedOrderDirectionException;
use Asyf\ApiBundle\Exception\Configuration\MissingEntityConfigurationException;
use Asyf\ApiBundle\Exception\Normalizer\UnexpectedValueTypeException;
use Asyf\ApiBundle\Exception\Configuration\Conditions\AmbiguousConditionException;
use Asyf\ApiBundle\Exception\Configuration\Conditions\MissingConditionKeyException;
use Asyf\ApiBundle\Exception\Configuration\Conditions\UnsupportedConditionOperatorException;
use Asyf\ApiBundle\Exception\Configuration\Conditions\UnsupportedConditionTypeException;
use Asyf\ApiBundle\Exception\Response\NonScalarValueException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\Security\Acl\Util\ClassUtils;

class ApiController extends Controller
{
    /**
     * @var RequestStack
     */
    protected $requestStack;
    /**
     * @var ConfigurationBuilder
     */
    protected $configurationBuilder;
    /**
     * @var NormalizerInterface
     */
    protected $normalizer;
    /**
     * @var Registry|RegistryInterface
     */
    protected $doctrine;

    public function __construct(RequestStack $requestStack, ConfigurationBuilder $configurationBuilder, NormalizerInterface $normalizer, RegistryInterface $doctrine)
    {
        $this->requestStack = $requestStack;
        $this->configurationBuilder = $configurationBuilder;
        $this->normalizer = $normalizer;
        $this->doctrine = $doctrine;
    }

    /**
     * @param object $resource
     * @param array $rawConfiguration
     *
     * @return Response
     * @throws MissingConfigurationOptionException
     * @throws MissingEntityConfigurationException
     * @throws NotMappedFieldException
     * @throws UnexpectedValueTypeException
     * @throws UnsupportedOrderDirectionException
     */
    protected function autoResource($resource, ?array $rawConfiguration = []): Response
    {
        $configuration = $this->configurationBuilder->buildConfiguration($resource, [
                $rawConfiguration,
                $this->requestStack->getCurrentRequest()->query->all()
            ]
        );
        $normalizedResult = $this->normalizer->normalize($resource, $configuration);
        $responseData = new ResourceResponseData($normalizedResult, [
            'self' => $this->requestStack->getCurrentRequest()->getUri()
        ]);
        return $responseData->buildResponse();
    }

    /**
     * @param string $entityClassName
     * @param array|null $rawConfiguration
     *
     * @return Response
     * @throws AmbiguousConditionException
     * @throws MissingConditionKeyException
     * @throws MissingConfigurationOptionException
     * @throws MissingEntityConfigurationException
     * @throws NotMappedFieldException
     * @throws UnexpectedValueTypeException
     * @throws UnsupportedConditionOperatorException
     * @throws UnsupportedConditionTypeException
     * @throws UnsupportedOrderDirectionException
     * @throws NonScalarValueException
     */
    protected function autoCollection(string $entityClassName, ?array $rawConfiguration = []): Response
    {
        /**
         * @var $em EntityManager
         */
        $em = $this->doctrine->getManagerForClass($entityClassName);
        $classMetaData = $em->getClassMetadata($entityClassName);
        $collection = new PersistentCollection($em, $classMetaData, new ArrayCollection());

        /**
         * @var $configuration PersistentCollectionFieldConfiguration
         */
        $configuration = $this->configurationBuilder->buildConfiguration(
            $collection,
            [
                $rawConfiguration,
                $this->requestStack->getCurrentRequest()->query->all()
            ]
        );

        /**
         * @var $repository EntityRepository
         */
        $repository = $em->getRepository($entityClassName);
        $collection = $repository->matching($configuration->getCriteria());

        $normalizedResults = $this->normalizer->normalize($collection, $configuration);
        $responseData = new CollectionResponseData(
            $normalizedResults,
            [
                'self' => $this->requestStack->getCurrentRequest()->getUri()
            ],
            [
                'maxResults' => $collection->count()
            ]
        );

        if ($limit = $configuration->getLimit()) {
            $responseData
                ->addMetaData(PersistentCollectionFieldConfiguration::LIMIT, $limit)
                ->addMetaData(PersistentCollectionFieldConfiguration::OFFSET, $configuration->getOffset());
        }

        return $responseData->buildResponse();
    }

    /**
     * @param object $resource
     * @param FormTypeInterface|FormType|string $formType
     * @param array|null $rawConfiguration
     * @param callable|null $callback
     *
     * @return Response
     * @throws MissingConfigurationOptionException
     * @throws MissingEntityConfigurationException
     * @throws NonScalarValueException
     * @throws NotMappedFieldException
     * @throws UnexpectedValueTypeException
     * @throws UnsupportedOrderDirectionException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function resourceSave($resource, $formType, ?array $rawConfiguration = [], ?callable $callback = null)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (is_subclass_of($formType, ApiType::class)) {
            $tempForm = $this->createForm($formType, $resource);
            $dataKeys = [];
            $data = $request->get($tempForm->getName());
            if (is_array($data)) {
                $dataKeys = array_keys($data);
            }
            $form = $this->createForm($formType, $resource, [
                'use_fields' => $dataKeys
            ]);
        } else {
            $form = $this->createForm($formType, $resource);
        }

        $entityClassName = ClassUtils::getRealClass($resource);
        /**
         * @var $em EntityManager
         */
        $em = $this->doctrine->getManagerForClass($entityClassName);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($resource);
            $em->flush($resource);

            if ($callback !== null && is_callable($callback)) {
                $callback($resource);
            }
            return $this->autoResource($resource, $rawConfiguration);
        } else {
            $errorResponseData = new ErrorResponseData();
            $errorResponseData->addLink('self', $request->getUri());

            if ($form->isSubmitted()) {
                $errors = $form->getErrors(true);
                foreach ($errors as $error) {
                    $errorResponseData->addError(
                        $error->getOrigin()->getName(),
                        $error->getMessage());
                }
            } else {
                $errorResponseData->addError(
                    $form->getName(),
                    'Form is not valid - no data passed'
                );
            }
            return $errorResponseData->buildResponse();
        }
    }

    /**
     * @param object $resource
     * @param callable|null $callback
     *
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function autoResourceDelete($resource, ?callable $callback = null)
    {
        if ($resource) {

            $entityClassName = ClassUtils::getRealClass($resource);
            /**
             * @var $em EntityManager
             */
            $em = $this->doctrine->getManagerForClass($entityClassName);

            $em->remove($resource);
            $em->flush($resource);

            $messageResponseData = new MessageResponseData(
                'Successfully deleted',
                [
                    'self' => $this->requestStack->getCurrentRequest()->getUri()
                ]
            );
            if ($callback !== null && is_callable($callback)) {
                $callback($resource);
            }
            return $messageResponseData->buildResponse();
        } else {
            $errorResponseData = new ErrorResponseData(
                [
                    'Not found' => 'Resource was not found'
                ],
                [
                    'self' => $this->requestStack->getCurrentRequest()->getUri()
                ]
            );
            return $errorResponseData->buildResponse(Response::HTTP_NOT_FOUND);
        }

    }
}
