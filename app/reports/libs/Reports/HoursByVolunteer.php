<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace app\reports\libs\Reports;

use app\volunteers\models\Volunteer;

class HoursByVolunteer extends AbstractReport
{
    public function name()
    {
        return 'Total Hours By Volunteer';
    }

    public function getSections()
    {
        $section = [
            'title' => 'Volunteers',
            'rows' => [], ];

        $tagHeaders = $this->organization->hourTags();

        $section['header'] = ['Volunteer', 'Metadata', 'Hours'];
        // $section['header'] = array_merge( $section['header'], $tagHeaders );

        $totalHours = 0;
        $totalHoursByTag = [];
        foreach ($tagHeaders as $tag) {
            $totalHoursByTag[$tag] = 0;
        }

        $volunteers = Volunteer::findAll([
            'where' => [
                'active' => true,
                'organization' => $this->organization->id(), ], ]);

        foreach ($volunteers as $volunteer) {
            $metadata = $volunteer->metadata;
            if ($metadata) {
                $metadata = urldecode(http_build_query($metadata));
            }

            $volunteerHours = $this->organization->totalHoursVolunteered($this->start, $this->end, $volunteer);
            $volunteerHoursBytag = $this->organization->totalHoursVolunteeredByTag($this->start, $this->end, $volunteer);

            $row = [
                $volunteer->name(),
                $metadata,
                $volunteerHours,
            ];

            // process tags
            foreach ($tagHeaders as $tag) {
                $tagHours = 0;
                if (isset($volunteerHoursBytag[$tag])) {
                    $tagHours = $volunteerHoursBytag[$tag];
                }

                // $row[] = $tagHours;
                $totalHoursByTag[$tag] += $tagHours;
            }

            $section['rows'][] = $row;

            $totalHours += $volunteerHours;
        }

        $section['footer'] = ['', 'Total Hours', number_format($totalHours)];
        // $section['footer'] = array_merge( $section['footer'], array_values( $totalHoursByTag ) );

        return [$section];
    }
}
