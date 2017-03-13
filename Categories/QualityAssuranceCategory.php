<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\QualityAssurance\Categories;

use Piwik\Category\Category;

class QualityAssuranceCategory extends Category
{
    protected $id = 'QualityAssurance_QualityAssurance';
    protected $order = 2;
    protected $icon = 'icon-chart-bar';
}