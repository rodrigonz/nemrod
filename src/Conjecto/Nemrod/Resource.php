<?php
/*
 * This file is part of the Nemrod package.
 *
 * (c) Conjecto <contact@conjecto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Conjecto\Nemrod;

use EasyRdf\Resource as BaseResource;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Class Resource.
 */
class Resource extends BaseResource
{
    /**
     * Is the resource ready for usage within Nemrod ?
     *
     * @var bool
     */
    protected $isReady = false;

    /**
     * Tells if the resource was modified after being loaded.
     *
     * @var bool
     */
    protected $isDirty = false;

    /**
     *
     */
    const PROPERTY_PATH_SEPARATOR = "/";

    /**
     * @var Manager
     */
    private $_rm;

    /**
     *
     */
    public function __construct($uri = null, $graph = null)
    {
        $uri = ($uri == null) ? 'e:-1' : $uri;

        return parent::__construct($uri, $graph);
    }

    /** Get all values for a property
     * This method will return an empty array if the property does not exist.
     *
     * @param string $property The name of the property (e.g. foaf:name)
     * @param string $type     The type of value to filter by (e.g. literal)
     * @param string $lang     The language to filter by (e.g. en)
     *
     * @return array An array of values associated with the property
     */
    public function all($property, $type = null, $lang = null)
    {
        list($first, $rest) = $this->split($property);

        $result = parent::all($property, $type, $lang);

        if (is_array($result)) {
            $llResult = array();
            foreach ($result as $res) {
                if ($res instanceof Resource && (!empty($this->_rm))) {
                    $llResult[] = $this->_rm->find(null, $res->getUri());
                }
            }

            return $llResult;
        } elseif ($this->_rm->isResource($result)) {
            try {
                if ($result->isBNode()) {
                    $re = $this->_rm->getUnitOfWork()->getPersister()->constructBNode($this->uri, $first);
                } else {
                    $re = $this->_rm->find(null, $result->getUri());
                }
                if (!empty($re)) {
                    if ($rest == '') {
                        return $re;
                    }

                    return $re->all($rest, $type, $lang);
                }

                return;
            } catch (Exception $e) {
                return;
            }
        } else {
            return $result;
        }
    }

    /**
     * @param array|string $property
     * @param null         $type
     * @param null         $lang
     *
     * @return mixed|void
     */
    public function get($property, $type = null, $lang = null)
    {
        list($first, $rest) = $this->split($property);

        //first trrying to get first step value
        $result = parent::get($first, $type, $lang);

        if (is_array($result)) {
            if (count($result)) {
                return $result[0];
            }

            return;
        } elseif ($result instanceof Resource  && (!empty($this->_rm))) { //we get a resource

            try {
                //"lazy load" part : we get the complete resource
                if ($result->isBNode()) {
                    $re = $this->_rm->getUnitOfWork()->getPersister()->constructBNode($this->uri, $first);
                } else {
                    $re = $this->_rm->find(null, $result->getUri());
                }

                if (!empty($re)) {
                    if ($rest == '') {
                        return $re;
                    }
                    //if rest of path is not empty, we get along it
                     return $re->get($rest, $type, $lang);
                }

                return;
            } catch (Exception $e) {
                return;
            }
        } else { //result is a litteral
            return $result;
        }
    }

    /**
     * @return int|void
     */
    public function set($property, $value)
    {
        $this->snapshot($property);

        //echo $this->getUri()."-".$property.">".$value;
        //resource: check if managed (for further save
        if ($value instanceof Resource && (!empty($this->_rm)) && $this->_rm->getUnitOfWork()->isManaged($this)) {
            $this->_rm->persist($value);
        }
        $out = parent::set($property, $value);

        return $out;
    }

    /**
     * @return int|void
     */
    public function add($property, $value)
    {
        $this->snapshot();
        //resource: check if managed (for further save)
        if ($property instanceof Resource && (!empty($this->_rm)) && $this->_rm->getUnitOfWork()->isManaged($this)) {
            $this->_rm->persist($property);
        }
        $out = parent::add($property, $value);

        return $out;
    }

    /**
     * @return int|void
     */
    public function delete($property, $value = null)
    {
        $this->snapshot();
        $out = parent::delete($property, $value);

        return $out;
    }

    /**
     * @return Manager
     */
    public function getRm()
    {
        return $this->_rm;
    }

    /**
     * @param Manager $rm
     */
    public function setRm($rm)
    {
        $this->_rm = $rm;
    }

    /**
     * @param $path
     *
     * @return array
     */
    private function split($path)
    {
        $first = $path;
        $rest = "";
        $firstSep = strpos($path, $this::PROPERTY_PATH_SEPARATOR);

        if ($firstSep) {
            $first = substr($path, 0, $firstSep);
            $rest = substr($path, $firstSep+1);
        }

        return array($first, $rest);
    }

    /**
     * @param $property
     * @param $value
     */
    private function snapshot()
    {
        if (!$this->isReady) {
            return;
        }

        if (!empty($this->_rm) && !$this->isDirty) {
            $this->_rm->getUnitOfWork()->snapshot($this);
            $this->isDirty = true;
            $this->_rm->getUnitOfWork()->setDirty($this->getUri());
        }
    }

    /**
     * Sets the resource as ready for usage within Nemrod.
     */
    public function setReady()
    {
        $this->isReady = true;
    }
}
