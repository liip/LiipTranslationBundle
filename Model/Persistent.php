<?php

namespace Liip\TranslationBundle\Model;

class Persistent
{
    protected $isDeleted = false;
    protected $isModified = false;
    protected $isNew = false;

    public function isDeleted()
    {
        return $this->isDeleted;
    }

    public function isModified()
    {
        return $this->isModified;
    }

    public function isNew()
    {
        return $this->isNew;
    }

    protected function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;
    }

    protected function setIsModified($isModified)
    {
        $this->isModified = $isModified;
    }

    protected function setIsNew($isNew)
    {
        $this->isNew = $isNew;
    }

    public function isDirty()
    {
        return $this->isNew() || $this->isModified() || $this->isDeleted();
    }
}
