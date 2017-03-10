<?php

namespace Piwik\Plugins\QualityAssurance;

use Piwik\Menu\MenuTop;
use Piwik\Piwik;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureTopMenu(MenuTop $menu)
    {
        if (!Piwik::hasUserSuperUserAccess()) return;
        $tooltip = Piwik::translate('QualityAssurance_QualityAssurance');
        $menu->addItem(Piwik::translate('QualityAssurance_QualityAssurance'), '', $this->urlForAction('index'), 2, $tooltip);
    }
}
