<?php

namespace Oro\Bundle\EntityBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\Rest\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\EntityBundle\Provider\EntityFieldProvider;
use Oro\Bundle\EntityBundle\Exception\InvalidEntityException;

/**
 * @RouteResource("entity")
 * @NamePrefix("oro_api_")
 */
class EntityFieldController extends FOSRestController implements ClassResourceInterface
{
    /**
     * Get entity fields.
     *
     * @param string $entityName Entity full class name; backslashes (\) should be replaced with underscore (_).
     *
     * @QueryParam(
     *      name="with-relations", requirements="(1)|(0)", nullable=true, strict=true, default="0",
     *      description="Indicates whether association fields should be returned as well.")
     * @QueryParam(
     *      name="with-virtual-fields", requirements="(1)|(0)", nullable=true, strict=true, default="0",
     *      description="Indicates whether virtual fields should be returned as well.")
     * @QueryParam(
     *      name="with-entity-details", requirements="(1)|(0)", nullable=true, strict=true, default="0",
     *      description="Indicates whether details of related entity should be returned as well.")
     * @QueryParam(
     *      name="with-unidirectional", requirements="(1)|(0)", nullable=true, strict=true, default="0",
     *      description="Indicates whether Unidirectional association fields should be returned.")
     * @Get(name="oro_api_get_entity_fields", requirements={"entityName"="((\w+)_)+(\w+)"})
     * @QueryParam(
     *      name="with-exclusions", requirements="(1)|(0)", nullable=true, strict=true, default="1",
     *      description="Indicates whether exclusion logic should be applied.")
     * @ApiDoc(
     *      description="Get entity fields",
     *      resource=true
     * )
     *
     * @return Response
     */
    public function getFieldsAction($entityName)
    {
        $entityName         = str_replace('_', '\\', $entityName);
        $withRelations      = ('1' == $this->getRequest()->query->get('with-relations'));
        $withEntityDetails  = ('1' == $this->getRequest()->query->get('with-entity-details'));
        $withUnidirectional = ('1' == $this->getRequest()->query->get('with-unidirectional'));
        $withVirtualFields  = ('1' == $this->getRequest()->query->get('with-virtual-fields'));
        $withExclusions     = ('1' == $this->getRequest()->query->get('with-exclusions'));

        /** @var EntityFieldProvider $provider */
        $provider = $this->get('oro_entity.entity_field_provider');

        $statusCode = Codes::HTTP_OK;
        try {
            $result = $provider->getFields(
                $entityName,
                $withRelations,
                $withVirtualFields,
                $withEntityDetails,
                $withUnidirectional,
                $withExclusions
            );
        } catch (InvalidEntityException $ex) {
            $statusCode = Codes::HTTP_NOT_FOUND;
            $result     = array('message' => $ex->getMessage());
        }

        return $this->handleView($this->view($result, $statusCode));
    }
}
