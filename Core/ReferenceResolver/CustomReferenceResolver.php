<?php

namespace Kaliop\eZMigrationBundle\Core\ReferenceResolver;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Field;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use \eZ\Publish\API\Repository\Repository;

/**
 * Handle 'any' references by letting the developer store them and retrieve them afterwards
 */
class CustomReferenceResolver extends AbstractResolver
{
    /**
     * Defines the prefix for all reference identifier strings in definitions
     */
    protected $referencePrefixes = array('reference:', 'main_location_content_field:');

    /**
     * Array of all references set by the currently running migrations.
     *
     * @var array
     */
    private $references = array();

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    /**
     * Get a stored reference
     *
     * @param string $identifier format: reference:<some_custom_identifier> or main_location_content_field:<field>:<value>
     * @return mixed
     * @throws \Exception When trying to retrieve an unset reference
     */
    public function getReferenceValue($identifier)
    {
        $ref = $this->getReferenceIdentifierByPrefix($identifier);

        switch($ref['prefix']) {
            case 'reference:':
                if (!array_key_exists($ref['identifier'], $this->references)) {
                    throw new \Exception("No reference set with identifier '{$ref['identifier']}'");
                }

                return $this->references[$ref['identifier']];
            case 'main_location_content_field:':
                return $this->getContentMainLocationByField($ref['identifier']);
        }


    }

    /**
     * Add a reference to be retrieved later.
     *
     * @param string $identifier The identifier of the reference
     * @param mixed $value The value of the reference
     * @throws \Exception When there is a reference with the specified $identifier already.
     */
    public function addReference($identifier, $value)
    {
        if (array_key_exists($identifier, $this->references)) {
            throw new \Exception("A reference with identifier '$identifier' already exists");
        }

        $this->references[$identifier] = $value;
    }

    /**
     * @param string $criteria <field>:<value> where <field> is a content type field
     * @return string Location id

     */
    private function getContentMainLocationByField($criteria)
    {
        list($field, $value) = explode(':', $criteria);

        $content = $this->repository->getSearchService()->findSingle(new Field($field, Operator::EQ, $value));

        return $content->contentInfo->mainLocationId;
    }
}
