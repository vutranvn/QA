<?php
namespace Piwik\Plugins\QualityAssurance\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;

class getFor extends Base
{
	protected function init()
	{
		parent::init();
		$this->name          = Piwik::translate('QualityAssurance_AudienceByFormat');
//		$this->subcategoryId = 'QualityAssurance_Overview';
		$this->order = 2;
		$this->documentation = ''; // TODO
	}

	public function configureView(ViewDataTable $view)
	{
		$view->config->show_search = false;
		$view->config->show_exclude_low_population = false;
		$view->config->addTranslation('label', Piwik::translate("QualityAssurance_Time"));

		$view->requestConfig->filter_sort_column = 'label';
		$view->requestConfig->filter_sort_order = 'asc';
		$view->requestConfig->filter_limit = 10;

		$view->config->show_exclude_low_population = false;
		$view->config->show_table_all_columns = false;
		$view->config->disable_row_evolution  = true;
		$view->config->max_graph_elements = 10;
		$view->config->metrics_documentation = array('value' => 'Documentation for metrics');
	}

}