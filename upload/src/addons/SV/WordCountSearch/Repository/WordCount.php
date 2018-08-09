<?php

namespace SV\WordCountSearch\Repository;

use XF\Mvc\Entity\Repository;

/**
 * Class WordCount
 *
 * @package SV\WordCountSearch\Repository
 */
class WordCount extends Repository
{
    /**
     * @param string $str
     * @return int
     */
    protected function str_word_count_utf8($str)
    {
        // ref: http://php.net/manual/de/function.str-word-count.php#107363
        return count(preg_split('~[^\p{L}\p{N}\']+~u',$str));
    }

    /**
     * @return bool
     */
    public function hasRangeQuery()
    {
        //$this->app()->search()->getQuery();
        if (self::$hasElasticSearch  === null)
        {
            self::$hasElasticSearch = false;
            self::$hasMySQLSearch = true;
        }

        return self::$hasElasticSearch || self::$hasMySQLSearch;
    }

    /** @var bool|null  */
    protected static $hasElasticSearch = null;
    protected static $hasMySQLSearch = true;

    /**
     * @return mixed
     */
    protected function getWordCountThreshold()
    {
        return \XF::app()->options()->wordcountThreshold;
    }

    /**
     * @param int $postId
     * @param int $wordCount
     * @return bool
     */
    public function shouldRecordPostWordCount(/** @noinspection PhpUnusedParameterInspection */ $postId, $wordCount)
    {
        if ($wordCount >= $this->getWordCountThreshold())
        {
            return true;
        }

        return false;
    }

    /**
     * @param string $message
     * @return int
     */
    public function getTextWordCount($message)
    {
        $strippedText = $this->app()->stringFormatter()->stripBbCode($message, ['stripQuote' => true]);
        // remove non-visible placeholders
        $strippedText = str_replace('[*]', ' ', $strippedText);
        return $this->str_word_count_utf8($strippedText);
    }

    /**
     * @param int $WordCount
     * @return string
     */
    public function roundWordCount($WordCount)
    {
        $inexactWordCount = intval($WordCount);
        if (!$inexactWordCount)
        {
            return 0;
        }
        if ($inexactWordCount >= 1000000000)
        {
            $inexactWordCount = round($inexactWordCount / 1000000000, 1) . 'b';
        }
        else if ($inexactWordCount >= 1000000)
        {
            $inexactWordCount = round($inexactWordCount / 1000000, 1) . 'm';
        }
        else if ($inexactWordCount >= 100000)
        {
            $inexactWordCount = round($inexactWordCount / 100000, 1) * 100 . 'k';
        }
        else if ($inexactWordCount >= 10000)
        {
            $inexactWordCount = round($inexactWordCount / 10000, 1) * 10 . 'k';
        }
        else if ($inexactWordCount >= 1000)
        {
            $inexactWordCount = round($inexactWordCount / 1000, 1) . 'k';
        }
        else if ($inexactWordCount >= 100)
        {
            $inexactWordCount = round($inexactWordCount / 100, 1) * 100;
        }
        else if ($inexactWordCount >= 10)
        {
            $inexactWordCount = round($inexactWordCount / 10, 1) * 10;
        }
        else if ($inexactWordCount < 0)
        {
            $inexactWordCount = 0;
        }
        else
        {
            $inexactWordCount = 10;
        }

        return strval($inexactWordCount);
    }

    /**
     * @param \XF\Entity\Forum|null $forum
     *
     * @return bool
     */
    public function getIsThreadmarksSupportEnabled(\XF\Entity\Forum $forum = null)
    {
        $addOns = \XF::app()->container('addon.cache');
        if (empty($addOns['SV/Threadmarks']))
        {
            return false;
        }

        /** @var \SV\Threadmarks\XF\Entity\Forum $forum */
        if ($forum)
        {
            if (!$forum->canViewThreadmarks())
            {
                return false;
            }
        }

        return true;
    }
}
