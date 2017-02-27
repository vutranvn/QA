<?php

namespace Piwik\Plugins\QualityAssurance;

use Piwik\Piwik;

class QualityAssurance extends \Piwik\Plugin
{
	public function registerEvents()
	{
		return array(
			'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
			'AssetManager.getJavaScriptFiles' => 'getJsFiles',
		);
	}

	public function getJsFiles(&$jsFiles)
	{
		$jsFiles[] = "libs/bower_components/visibilityjs/lib/visibility.core.js";
		$jsFiles[] = "plugins/QualityAssurance/javascripts/vendor/raphael.min.js";
		$jsFiles[] = "plugins/QualityAssurance/javascripts/vendor/jquery.qtip.min.js";
		$jsFiles[] = "plugins/QualityAssurance/javascripts/vendor/kartograph.min.js";
		$jsFiles[] = "libs/bower_components/chroma-js/chroma.min.js";
		$jsFiles[] = "plugins/QualityAssurance/javascripts/visitor-map.js";
		$jsFiles[] = "plugins/QualityAssurance/javascripts/realtime-map.js";

		$jsFiles[] = "plugins/QualityAssurance/javascripts/main.js";
	}

	public function getStylesheetFiles(&$stylesheets)
	{
		$stylesheets[] = "plugins/QualityAssurance/stylesheets/visitor-map.less";
		$stylesheets[] = "plugins/QualityAssurance/stylesheets/realtime-map.less";

		$stylesheets[] = "plugins/QualityAssurance/stylesheets/style.css";
	}
}