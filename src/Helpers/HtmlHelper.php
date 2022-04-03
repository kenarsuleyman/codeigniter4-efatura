<?php

namespace kenarsuleyman\codeigniter4Efatura\Helpers;

class HtmlHelper
{

    private $template;
    private $html;

    public function __construct()
    {
        $this->template = file_get_contents('../Templates/Invoice.html');
    }

    private function formatData($data)
    {

    }

    public function generate($data)
    {

    }

    public function export()
    {
        return $this->template;
    }

}