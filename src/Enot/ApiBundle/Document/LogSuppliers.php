<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * Class LogSuppliers
 * @package Enot\ApiBundle\Document
 * @MongoDB\Document(collection="log_suppliers")
 * @MongoDB\HasLifecycleCallbacks()
 */
class LogSuppliers extends Log
{

}