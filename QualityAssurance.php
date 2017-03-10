<?php

namespace Piwik\Plugins\QualityAssurance;

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
		$jsFiles[] = "plugins/QualityAssurance/javascripts/main.js";
	}

	public function getStylesheetFiles(&$stylesheets)
	{
		$stylesheets[] = "plugins/QualityAssurance/stylesheets/style.css";
	}
}