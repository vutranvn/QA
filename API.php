<?php

namespace Piwik\Plugins\QualityAssurance;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Site;
use Piwik\Metrics\Formatter;
use Piwik\API\Request;
use Piwik\FrontController;

class API extends \Piwik\Plugin\API
{

	private $idSites;

	public function __construct()
	{
		$this->idSites = $this->getArrIdSitesAccessView();
	}

	public function overviewGetRowOne( $lastMinutes , $refreshAfterXSecs )
	{
		$audience_size  = array();
		$startup_time   = array();
		$bitrate        = array();
		$buffer_time    = array();

		$idSites = $this->idSites;
		$date   = Common::getRequestVar('date', date("Y-m-d"));
		$period = Common::getRequestVar('period', 'day');
		$format = 'JSON';
		$token_auth = Piwik::getCurrentUserTokenAuth();

		echo "<pre>";
			var_dump('idSites: ', $idSites);
		echo "</pre>";
		/**
		 * Get api in multi idSites
		 */
		foreach ($idSites as $idSite) {
			$eventsActions  = $this->callHttpApiRequest('Events.getAction', $format, $token_auth, $idSite, $date, $period);
			$uniqueVisitors = $this->callHttpApiRequest('VisitsSummary.getUniqueVisitors', $format, $token_auth, $idSite, $date, $period);

			if ( !$eventsActions ) {
				$eventsActions = json_decode($eventsActions, true);
				foreach ($eventsActions as $action) {
					if ( $action['label'] == 'start-delay' ) {
						$startup_time[] = $action['avg_event_value'];
					}

					if ( $action['label'] == 'rebuffer-time' ) {
						$buffer_time[] = $action['avg_event_value'];
					}

					if ( $action['label'] == 'bitrate' ) {
						$bitrate[] = $action['avg_event_value'];
					}
				}
			}
			if ( !$uniqueVisitors ) {
				$uniqueVisitors = json_decode($uniqueVisitors, true);
				if ( isset($uniqueVisitors['value']) ) {
					$audience_size[] = $uniqueVisitors['value'];
				}
			}
		}

		$formatter 		= new Formatter();
		return array(
			'audience_size'     => array(
				'value'     => $audience_size?$formatter->getPrettyNumber( array_sum($audience_size)/count($audience_size) ):'Empty',
				'metrics'   => 'audience_size',
			),
			'startup_time'     => array(
				'value'     => $startup_time?$formatter->getPrettyNumber( array_sum($startup_time)/count($startup_time) ):'Empty',
				'metrics'   => 'startup_time',
			),
			'bitrate'     => array(
				'value'     => $bitrate?$formatter->getPrettyNumber( array_sum($bitrate)/count($bitrate) ):'Empty',
				'metrics'   => 'bitrate',
			),
			'buffer_time'     => array(
				'value'     => $buffer_time?$formatter->getPrettyNumber( array_sum($buffer_time)/count($buffer_time) ):'Empty',
				'metrics'   => 'buffer_time',
			),
			'refreshAfterXSecs' => 60,
			'lastMinutes'       => $lastMinutes
		);
	}

	public function getGraphEvolution($idSite, $date, $period, $columns = false)
	{
		$date   = !$date?Common::getRequestVar('date', date("Y-m-d")):date("Y-m-d");
		$period = !$period?Common::getRequestVar('period', 'day'):'day';

//		$columns = array(
//			'audience',
//			'startup_time',
//			'bit_rate',
//			'rebuffer_time',
//		);
		if ( empty($columns) ) {
			$columns = array(
				'audience',
			);
		}

		$end    = date("Y-m-d");
		$from   = date('Y-m-d',(strtotime ( '-30 days', strtotime($end)) ));

		$graphData = array();
		for( $from; $from <= $end; $from=date('Y-m-d', (strtotime ( '+1 day', strtotime($from)))) ) {
			foreach ( $columns as $column ) {
				$graphData[ $from ][ $column ] = rand( 1000, 10000);
			}
		}
		/**
		 * Make data like
		 *
		 * array (
		 *      "2016-07-17" => array ( "request_count_200" => X, "request_count_500" => Y ),
		 *      "2016-07-18" => array ( "request_count_200" => X, "request_count_500" => Y ),
		 *      "2016-07-19" => array ( "request_count_200" => X, "request_count_500" => Y )
		 * )
		 */

		ksort($graphData);

		return DataTable::makeFromIndexedArray($graphData);
	}

	public function getFor($idSite, $period, $date, $segment = false)
	{

		$metrics = array(
			'avi',
			'mp4',
			'flv',
		);
		$result = $this->getDataExamples( $metrics );

		return $result;
	}

	public function getCon($idSite, $period, $date, $segment = false)
	{

		$metrics = array(
			'Cable',
			'Fiber',
			'Moblie',
			'DSL',
			'Others',
		);
		$result = $this->getDataExamples( $metrics, 0, 40 );

		return $result;
	}

	public function getCat($idSite, $period, $date, $segment = false)
	{

		$metrics = array(
			'Sport',
			'Comedy‎',
			'Game show',
			'Talk show',
			'Music',
		);
		$result = $this->getDataExamples( $metrics, 0, 40 );

		return $result;
	}

	public function getCountry($idSite, $period, $date, $segment = false)
	{
		$dataTable = $this->getDataTable(Archiver::COUNTRY_RECORD_NAME, $idSite, $period, $date, $segment);

		// apply filter on the whole datatable in order the inline search to work (searches are done on "beautiful" label)
		$dataTable->filter('AddSegmentValue');
		$dataTable->filter('ColumnCallbackAddMetadata', array('label', 'code'));
		$dataTable->filter('ColumnCallbackAddMetadata', array('label', 'logo', __NAMESPACE__ . '\getFlagFromCode'));
		$dataTable->filter('ColumnCallbackReplace', array('label', __NAMESPACE__ . '\countryTranslate'));

		$dataTable->queueFilter('ColumnCallbackAddMetadata', array(array(), 'logoWidth', function () { return 16; }));
		$dataTable->queueFilter('ColumnCallbackAddMetadata', array(array(), 'logoHeight', function () { return 11; }));

		return $dataTable;
	}

	public function getDataExamples( $metrics, $min = 1000, $max = 10000 )
	{
		$end    = date("Y-m-d");
		$from   = date('Y-m-d',(strtotime ( '-15 days', strtotime($end)) ));

		$graphData = array();
		for( $from; $from <= $end; $from=date('Y-m-d', (strtotime ( '+1 day', strtotime($from)))) ) {
			foreach ( $metrics as $metric ) {
				$graphData[ $from ][ $metric ] = rand($min, $max);
			}
		}
		/**
		 * Make data like
		 *
		 * array (
		 *      "2016-07-17" => array ( "request_count_200" => X, "request_count_500" => Y ),
		 *      "2016-07-18" => array ( "request_count_200" => X, "request_count_500" => Y ),
		 *      "2016-07-19" => array ( "request_count_200" => X, "request_count_500" => Y )
		 * )
		 */

		ksort($graphData);

		return DataTable::makeFromIndexedArray($graphData);
	}

	private function callHttpApiRequest($method, $format, $token_auth, $idSite = false, $date = false, $period = false)
	{
		$url = 'module=API&method='.$method;
		if ( $idSite ) {
			$url .= '&idSite='.$idSite;
		}
		if ( $date ) {
			$url .= '&date='.$date;
		}
		if ( $period ) {
			$url .= '&period='.$period;
		}
		$url .= '&format='.$format.'&token_auth='.$token_auth;
		$request = new Request($url);

		return $request->process();
	}

	private function checkUserIsAnonymous(){
		//
	}

	private function getArrIdSitesAccessView()
	{
		$token_auth = Piwik::getCurrentUserTokenAuth();
		$isAdmin    = Piwik::hasUserSuperUserAccess();
		if ( $isAdmin == true ) {
			$idSites = $this->callHttpApiRequest('SitesManager.getAllSitesId', 'JSON', $token_auth);
			$idSites = json_decode($idSites, true);
		} else {
			$result = $this->callHttpApiRequest('SitesManager.getSitesWithViewAccess', 'JSON', $token_auth);
			$result = json_decode($result, true);
			foreach ($result as $r) {
				$idSites[] = $r['idsite'];
			}
		}

		return $idSites;
	}

}