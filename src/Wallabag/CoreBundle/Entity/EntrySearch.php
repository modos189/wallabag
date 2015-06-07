<?php

namespace Wallabag\CoreBundle\Entity;

use Symfony\Component\HttpFoundation\Request;

class EntrySearch
{
    protected $title;

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }
}