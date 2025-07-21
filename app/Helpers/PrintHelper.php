<?php

use Barryvdh\DomPDF\PDF;

class PrintHelper extends PDF
{

    protected $html;
    public function __construct($html)
    {
        $this->html = $html;
    }
}
