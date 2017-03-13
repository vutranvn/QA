<?php

namespace Piwik\Plugins\QualityAssurance;

use Piwik\Menu\MenuReporting;
use Piwik\Piwik;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureReportingMenu(MenuReporting $menu)
    {
        if (!Piwik::isUserHasSomeViewAccess()) return;

        $menu->registerMenuIcon(Piwik::translate('QualityAssurance_QualityAssurance'), 'icon-chart-bar');
        $menu->addItem(Piwik::translate('QualityAssurance_QualityAssurance'), '', array(), 5);

        $this->addSubMenu($menu, Piwik::translate('QualityAssurance_Overview'), 'overview', 1);
    }

    private function addSubMenu(MenuReporting $menu, $subMenu, $action, $order)
    {
        $menu->addItem(Piwik::translate('QualityAssurance_QualityAssurance'), $subMenu, $this->urlForAction($action), $order);
    }
}
