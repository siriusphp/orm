<?php

namespace Sirius\Orm\Relation;

class OneToOne extends ManyToOne
{
    protected function getDefaultOptions()
    {
        $defaults = parent::getDefaultOptions();

        $defaults[RelationOption::CASCADE] = true;

        return $defaults;
    }
}
