<?php
namespace Piwik\Plugins\QualityAssurance;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Translation\Translator;
use Piwik\View;
use Piwik\FrontController;

class Controller extends \Piwik\Plugin\Controller
{
	private $translator;

	public function __construct(Translator $translator)
	{
		parent::__construct();
		$this->translator = $translator;
	}

	public function index()
	{
		return $this->getStreamAnalyzer();
	}

	public function getStreamAnalyzer()
	{
		$view = new View("@QualityAssurance/index");

		$lastMinutes = 2;
		$audience_size = API::getInstance()->overviewGetRowOne( $lastMinutes, $metrics = 'traffic_ps', 5 );
		$view->bw_lastMinutes  	= $lastMinutes;
		$view->audience_size   		= $audience_size['audience_size']['value'];
		$view->startup_time   		= $audience_size['startup_time']['value'];
		$view->bitrate   		    = $audience_size['bitrate']['value'];
		$view->buffer_time   		= $audience_size['buffer_time']['value'];
		$view->refreshAfterXSecs = 10;
		$view->translations 	= array(
			'audience_size' => Piwik::translate('QualityAssurance_Audience')
		);

		$view->urlSparklineAudience = $this->getUrlSparkline('getGraphOverview', array('columns' => array('audience')));
		$view->urlSparklineStartup  = $this->getUrlSparkline('getGraphOverview', array('columns' => array('startup_time')));
		$view->urlSparklineRebuffer = $this->getUrlSparkline('getGraphOverview', array('columns' => array('rebuffer_time')));

		$view->lastMinutes  = $lastMinutes;
		$view->refreshAfterXSecs = 5;

		$overview = array(
			'audience',
		);
		$view->graphOverview = $this->getGraphOverview(array(), $overview);

		$view->byFor    = $this->renderReport('getFor');
		$view->byCon    = $this->renderReport('getCon');
		$view->byCat    = $this->renderReport('getCat');

		// Map -------------- //
		$view ->audienceOfGeo = FrontController::getInstance()->dispatch('UserCountryMap', 'visitorMap');
		// End Map ---------- //

		$this->setGeneralVariablesView($view);

		return $view->render();
	}

	public function getGraphOverview(array $columns = array(), array $defaultColumns = array())
	{
		if (empty($columns)) {
			$columns = Common::getRequestVar('columns', false);
			if (false !== $columns) {
				$columns = Piwik::getArrayFromApiParameter($columns);
			}
		}

		if ( !$columns ) {
			$selectableColumns = array(
				'audience',
			);
		} else {
			$selectableColumns = $columns;
		}

		$view = $this->getLastUnitGraphAcrossPlugins($this->pluginName, __FUNCTION__, $columns, $selectableColumns, '', 'QualityAssurance.getGraphEvolution');

		$view->config->enable_sort          = false;
		$view->config->max_graph_elements   = 30;
		$view->requestConfig->filter_sort_column = 'label';
		$view->requestConfig->filter_sort_order  = 'asc';
		$view->requestConfig->disable_generic_filters=true;
		$view->config->addTranslations(array(
			'audience'      => Piwik::translate('QualityAssurance_Audience'),
			'startup_time'  => Piwik::translate('QualityAssurance_StartupTime'),
			// 'bit_rate'      => Piwik::translate('QualityAssurance_Bitrate'),
			'rebuffer_time' => Piwik::translate('QualityAssurance_RebufferTime'),
		));

		// Can not check empty so have to hardcode. F**k me!
		$view->config->columns_to_display = $defaultColumns;

		return $this->renderView($view);
	}
}