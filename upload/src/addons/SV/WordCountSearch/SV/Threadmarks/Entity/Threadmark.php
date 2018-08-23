<?php

namespace SV\WordCountSearch\SV\Threadmarks\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * Extends \SV\Threadmarks\Entity\Threadmark
 */
class Threadmark extends XFCP_Threadmark
{
    protected function _postSave()
    {
        if ($this->isInsert())
        {
            $content = $this->Content;
            if ($content &&
                $content->isValidRelation('Words') &&
                !$content->getRelation('Words') &&
                is_callable([$content, 'rebuildPostWordCount']))
            {
                /** @noinspection PhpUndefinedMethodInspection */
                $content->rebuildPostWordCount(null, false, false);
            }
        }

        parent::_postSave();
    }

    /**
     * @return string
     */
    public function getWordCount()
    {
        if (($content = $this->Content) && !empty($content->WordCount))
        {
            return $content->WordCount;
        }

        return '';
    }

    /**
     * @param Structure $structure
     * @return Structure
     */
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->getters['WordCount'] = true;

        return $structure;
    }
}