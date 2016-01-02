<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace app\reports\libs\Reports;

class HoursDetail extends AbstractReport
{
    public function name()
    {
        return 'Hours Log';
    }

    public function getSections()
    {
        $section = [
            'title' => 'Hours Log',
            'rows' => [], ];

        $section['header'] = ['Date', 'Volunteer', 'Metadata', 'Hours', 'Place', 'Tags'];

        $hours = $this->organization->hours($this->start, $this->end);
        $count = 0;

        foreach ($hours as $hour) {
            $h = $hour->toArray();

            // only show approved hours
            if (!$h['approved']) {
                continue;
            }

            $volunteer = $hour->volunteer();

            $metadata = $volunteer->metadata;
            if ($metadata) {
                $metadata = urldecode(http_build_query($metadata));
            }

            $row = [
                date('m-d-Y', $h['timestamp']),
                $volunteer->name(),
                $metadata,
                (int) $h['hours'],
                $hour->place()->name,
                implode(',', $hour->tags()),
            ];

            $section['rows'][] = $row;

            $count += $h['hours'];
        }

        $section['footer'] = ['', '', 'Total Hours', number_format($count), '', ''];

        $section['header'] = array_merge($section['header']);

        return [$section];
    }
}
