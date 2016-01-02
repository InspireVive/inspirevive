<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

use app\organizations\models\Organization;
use app\volunteers\models\VolunteerOrganization;
use app\reports\libs\Report;

class ReportTest extends \PHPUnit_Framework_TestCase
{
    public static $org;
    public static $volunteerOrg;

    public static function setUpBeforeClass()
    {
        self::$org = new Organization();
        self::$org->grantAllPermissions();
        self::$org->create([
            'name' => 'Test Organization',
            'email' => 'test@example.com',
            'username' => 'test'
        ]);

        TestBootstrap::app('user')->enableSU();
        self::$volunteerOrg = new VolunteerOrganization();
        self::$volunteerOrg->create([
            'organization' => self::$org->id(),
            'volunteer_coordinator_email' => 'test@example.com',
            'city' => 'Tulsa', ]);
        TestBootstrap::app('user')->disableSU();
    }

    public static function tearDownAfterClass()
    {
        if (self::$org) {
            self::$org->grantAllPermissions();
            self::$org->delete();
        }
    }

    public function assertPreConditions()
    {
        $this->assertGreaterThan(0, self::$org->id());
        $this->assertGreaterThan(0, self::$volunteerOrg->id());
    }

    public function testAvailableReports()
    {
        // NOTE this only tests if the reports are functional
        // the legitimacy of the output of the report should be tested
        // separately.

        foreach (Report::$availableReports as $r => $details) {
            $report = Report::getReport(TestBootstrap::app(), self::$org, $r, time() - 3600, time() + 3600);

            $this->assertInstanceOf('app\reports\libs\Reports\AbstractReport', $report);

            // check base filename
            $this->assertGreaterThan(5, strlen($report->baseFilename()));

            // check name
            $this->assertGreaterThan(5, strlen($report->name()));

            // check html output
            $html = $report->output('html');

            $this->assertStringStartsWith('<!DOCTYPE html>', $html);
            $this->assertNotRegExp('/E_NOTICE|E_WARNING/', $html);

            // check pdf output
            ob_start();
            $pdf = $report->output('pdf');
            ob_end_clean();

            $this->assertTrue(is_string($pdf));
            $this->assertGreaterThan(0, strlen($pdf));

            // check csv output
            $csv = $report->output('csv');
            $this->assertGreaterThan(0, strlen($csv));
            $this->assertTrue(strpos($csv, ',') > 0);

            // bogus type
            $this->assertFalse($report->output('bogus'));
        }
    }

    public function testGetBogusReport()
    {
        $this->assertFalse(Report::getReport(TestBootstrap::app(), self::$org, 'blah', time() - 3600, time() + 3600));
    }
}
