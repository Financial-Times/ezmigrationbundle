<?php

namespace Kaliop\eZMigrationBundle\API\Collection;

use eZ\Publish\API\Repository\Values\ObjectState\ObjectState;

/**
 * @todo add phpdoc to suggest typehinting
 */
class ObjectStateCollection extends AbstractCollection
{
    protected $allowedClass = ObjectState::class;
}
