<?php

namespace Piwik\Plugins\QualityAssurance\Widgets;

use Piwik\API\Request;
use Piwik\Plugin\Controller;
use Piwik\Plugins\QualityAssurance\Segment;

class Helper extends Controller
{
    public function getUrlSparkline($action, $customParameters = array())
    {
        return parent::getUrlSparkline($action, $customParameters);
    }

    private static function getRawSegment()
    {
        return Request::getRawSegmentFromRequest();
    }

    public static function isUsingDefaultSegment()
    {
        $segment = self::getRawSegment();

        return empty($segment);
    }

    public static function getMediaSegment()
    {
        $segment = self::getRawSegment();

        if (!empty($segment) && strpos($segment, Segment::NAME_SPENT_TIME) !== false) {
            // do not modify segment in case it already contains this segment
            return $segment;
        }

        $mediaSegment = Segment::NAME_SPENT_TIME . '>0';

        if (!empty($segment)) {
            $mediaSegment .= ';' . urldecode($segment);
        }

        return urlencode($mediaSegment);
    }

}
