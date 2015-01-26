<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 26/01/2015
 * Time: 11:02
 */

namespace Devyn\Component\Form\Extension\Core\Type;


use Devyn\Component\QueryBuilder\QueryBuilder;
use Devyn\Component\RAL\Manager\Manager;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RalQueryBuilderLoader
{
    /**
     * Construct an ORM Query Builder Loader
     *
     * @param QueryBuilder|\Closure $queryBuilder
     * @param Manager               $manager
     * @param string                $class
     *
     * @throws UnexpectedTypeException
     */
    public function __construct($queryBuilder, $manager = null, $class = null)
    {
        // If a query builder was passed, it must be a closure or QueryBuilder
        // instance
        if (!($queryBuilder instanceof QueryBuilder || $queryBuilder instanceof \Closure)) {
            throw new UnexpectedTypeException($queryBuilder, 'Devyn\Component\QueryBuilder\QueryBuilder or \Closure');
        }

        if ($queryBuilder instanceof \Closure) {
            if (!$manager instanceof Manager) {
                throw new UnexpectedTypeException($manager, 'Devyn\Component\RAL\Manager\Manager');
            }

            $queryBuilder = $queryBuilder($manager->getRepository($class));

            if (!$queryBuilder instanceof QueryBuilder) {
                throw new UnexpectedTypeException($queryBuilder, 'Devyn\Component\QueryBuilder\QueryBuilder');
            }
        }

        $this->queryBuilder = $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getResources()
    {
        return $this->queryBuilder->getQuery()->execute();
    }
}