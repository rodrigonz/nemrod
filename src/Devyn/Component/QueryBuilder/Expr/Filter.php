<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 06/01/2015
 * Time: 14:22
 */

namespace Devyn\Component\QueryBuilder\Expr;

use Doctrine\ORM\Query\Expr\Base;

class Filter extends Base
{
    /**
     * @var string
     */
    protected $preSeparator = 'FILTER (';

    /**
     * @var string
     */
    protected $separator = '';

    /**
     * @var string
     */
    protected $postSeparator = ')';

    /**
     * @return array
     */
    public function getParts()
    {
        return $this->parts;
    }

    /**
     * @var array
     */
    protected $allowedClasses = array(
        'Devyn\\Component\\QueryBuilder\\Expr\\GroupExpr',
    );

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->count() == 1) {
            return $this->preSeparator . $this->parts[0] . $this->postSeparator;
        }

        return $this->preSeparator . implode($this->separator, $this->parts) . $this->postSeparator;
    }
}