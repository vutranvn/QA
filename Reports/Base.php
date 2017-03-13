<?php
namespace Piwik\Plugins\QualityAssurance\Reports;

use Piwik\Plugin\Report;

abstract class Base extends Report
{
    protected function init()
    {
        $this->categoryId = 'QualityAssurance_QualityAssurance';
    }
}