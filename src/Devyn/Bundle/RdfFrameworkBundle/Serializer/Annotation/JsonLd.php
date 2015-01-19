<?php

namespace Devyn\Bundle\RdfFrameworkBundle\Serializer\Annotation;

/**
 * Serializer JsonLd annotation
 *
 * @Annotation
 * @Target("CLASS")
 */
class JsonLd
{
    /**
     * @var string
     */
    public $frame = null;

    /**
     * @var boolean
     */
    public $compact = true;
}
