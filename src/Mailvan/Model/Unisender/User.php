<?php
/**
 * 
 * 
 * @package ${PACKAGE}
 * @subpackage ${SUBPACKAGE}
 * @author Andrey Yaroshenko <da3n00r@gmail.com>
 */

namespace Mailvan\Model\Unisender;

use Mailvan\Core\Model\User as BaseUser;

class User extends BaseUser
{
    protected $tags = array();

    /**
     * Get user tags
     *
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set user tags
     *
     * @param array|string $tags
     */
    public function setTags($tags)
    {
        if (is_string($tags)){
            $tags = array($tags);
        }

        $this->tags = $tags;
    }

    public function toArray()
    {
        $data = parent::toArray();
        $data['tags'] = $this->getTags();

        return $data;
    }


    protected static function getFieldsMap()
    {
        $map = parent::getFieldsMap();
        $map['tags'] = 'setTags';

        return $map;
    }
}
