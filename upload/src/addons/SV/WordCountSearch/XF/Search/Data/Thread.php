<?php

namespace SV\WordCountSearch\XF\Search\Data;


use SV\WordCountSearch\Entity\PostWords;
use XF\Search\IndexRecord;
use XF\Search\MetadataStructure;

/**
 * Class Thread
 *
 * @package SV\WordCountSearch\XF\Search\Data
 */
class Thread extends XFCP_Thread
{
    /**
     * @param \XF\Entity\Thread $entity
     *
     * @return array
     * @throws \XF\PrintableException
     */
    protected function getMetaData(\XF\Entity\Thread $entity)
    {
        /** @var \SV\WordCountSearch\XF\Entity\Thread $entity */
        $metadata = parent::getMetaData($entity);

        // The firstPost isn't indexed as a post but as the thread.

        /** @var \SV\WordCountSearch\XF\Entity\Post $post */
        $post = $entity->FirstPost;
        if (!$post)
        {
            return $metadata;
        }

        $wordCountRepo = $this->getWordCountRepo();

        $wordCount = $post->RawWordCount;
        if ($wordCount)
        {
            $wordCount = intval($wordCount);
            if (empty($post->Words))
            {
                $post->rebuildPostWordCount($wordCount);
            }

            //$metadata['word_count'] = $wordCount;
        }

        $threadmarkInstalled = $wordCountRepo->getIsThreadmarksSupportEnabled();
        $wordCount = $entity->RawWordCount;
        if ($threadmarkInstalled)
        {
            if ($entity->threadmark_count && !$wordCount ||
                !$entity->threadmark_count && $wordCount)
            {
                /** @var \SV\WordCountSearch\XF\Repository\Thread $threadRepo */
                $threadRepo = \XF::app()->repository('XF:Thread');
                $threadRepo->rebuildThreadWordCount($entity);
                $wordCount = $entity->RawWordCount;
            }
        }
        if ($wordCount)
        {
            $metadata['word_count'] = $wordCount;
        }

        return $metadata;
    }

    /**
     * @param MetadataStructure $structure
     */
    public function setupMetadataStructure(MetadataStructure $structure)
    {
        parent::setupMetadataStructure($structure);
        $structure->addField('word_count', MetadataStructure::INT);
    }

    /**
     * @param $order
     *
     * @return null
     */
    public function getTypeOrder($order)
    {
        return parent::getTypeOrder($order);
    }

    /**
     * @return \SV\WordCountSearch\Repository\WordCount|\XF\Mvc\Entity\Repository
     */
    protected function getWordCountRepo()
    {
        return \XF::app()->repository('SV\WordCountSearch:WordCount');
    }
}
