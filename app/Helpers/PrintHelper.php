<?php

use Barryvdh\DomPDF\PDF;

/**
 * PDF generation helper for printing reports and documents. Used for generating printable
 * versions of JICR reports, meeting minutes, and other formal documents.
 *
 * Data Flow:
 *   Data prepared → PrintHelper formats → Generate PDF → Download or display
 *
 * @business-domain Utility
 */
class PrintHelper extends PDF
{

    protected $html;
    /**
     * Create a new PrintHelper instance.
     *
     * @param  mixed  $html  
     */
    public function __construct($html)
    {
        $this->html = $html;
    }
}
