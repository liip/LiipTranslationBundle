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

    protected function setIsDeleted($isDeleted = true)
    {
        $this->isDeleted = $isDeleted;
    }

    protected function setIsModified($isModified = true)
    {
        $this->isModified = $isModified;
    }

    protected function setIsNew($isNew = true)
    {
        $this->isNew = $isNew;
    }

    public function isDirty()
    {
        return $this->isNew() || $this->isModified() || $this->isDeleted();
    }

    public function getDirtyReason()
    {
        return
            $this->isDeleted() ? 'deleted' : (
                $this->isNew() ? 'created' : (
                    $this->isModified() ? 'modified' : null
                )
            )
        ;
    }
}
