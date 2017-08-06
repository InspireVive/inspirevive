<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

use App\Organizations\Models\Organization;
use App\Reports\Libs\Report;
use Infuse\Test;

class ReportTest extends PHPUnit_Framework_TestCase
{
    public static $org;

    public static function setUpBeforeClass()
    {
        self::$org = new Organization();
        self::$org->grantAllPermissions();
        self::$org->create([
            'name' => 'Test Organization',
            'email' => 'test@example.com',
            'username' => 'test'
        ]);
    }

    public static function tearDownAfterClass()
    {
        if (self::$org) {
            self::$org->grantAllPermissions()->delete();
        }
    }

    public function assertPreConditions()
    {
        $this->assertGreaterThan(0, self::$org->id());
    }

    public function testAvailableReports()
    {
        // NOTE this only tests if the reports are functional
        // the legitimacy of the output of the report should be tested
        // separately.

        foreach (Report::$availableReports as $r => $details) {
            $report = Report::getReport(Test::$app, self::$org, $r, time() - 3600, time() + 3600);

            $this->assertInstanceOf('App\Reports\Libs\Reports\AbstractReport', $report);

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
        $this->expectException(InvalidArgumentException::class);
        Report::getReport(Test::$app, self::$org, 'blah', time() - 3600, time() + 3600);
    }
}
