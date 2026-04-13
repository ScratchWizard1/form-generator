<?php

declare(strict_types=1);

namespace FormGenerator\Components;

use FormGenerator\Components\BasicComponent;

class EndFieldset extends BasicComponent
{
    /**
     * Generate the HTML for ending the fieldset.
     *
     * @return string
     */
    protected function getInput(): string
    {
        return "</fieldset>";
    }
}
