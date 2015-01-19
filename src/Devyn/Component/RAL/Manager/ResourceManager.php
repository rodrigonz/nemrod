<?php

namespace Devyn\Component\RAL\Manager;

/**
 * Class ResourceManager
 * @package Devyn\Component\RAL\Manager
 */
class ResourceManager
{

    /** @var  RepositoryFactory */
    private $repositoryFactory;

    /** @var \EasyRdf_Sparql_Client */
    private $sparqlClient;

    /**
     *
     */
    public function __construct($repositoryFactory)
    {
        $this->repositoryFactory = $repositoryFactory;
    }

    /**
     * @param $className
     * @return mixed
     */
    public function getRepository($className)
    {
        return $this->repositoryFactory->getRepository($className, $this);
    }

    /**
     * @param RepositoryFactory $repo
     */
    public function setRepositoryFactory(RepositoryFactory $repo)
    {
        $this->repositoryFactory = $repo;
    }

    /**
     * @return \EasyRdf_Sparql_Client
     */
    public function getSparqlClient()
    {
        return $this->sparqlClient;
    }

    /**
     * @param \EasyRdf_Sparql_Client $sparqlClient
     */
    public function setSparqlClient($sparqlClient)
    {
        $this->sparqlClient = $sparqlClient;
    }

}