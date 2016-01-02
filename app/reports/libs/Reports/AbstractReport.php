<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace app\reports\libs\Reports;

use infuse\Response;
use infuse\Utility as U;
use infuse\View;
use App;
use app\organizations\models\Organization;

abstract class AbstractReport
{
    public static $viewsDir;

    protected $app;
    protected $organization;
    protected $start;
    protected $end;
    protected $df;
    protected $htmlOutput;

    public function __construct(App $app, Organization $organization, $start, $end)
    {
        $this->app = $app;
        $this->organization = $organization;
        $this->start = $start;
        $this->end = $end;
        $this->df = 'M j, Y';

        self::$viewsDir = dirname(dirname(__DIR__)).'/views';
    }

    public function baseFilename()
    {
        $name = $this->organization->name.' '.$this->name().' '.date($this->df, $this->start).'|'.date($this->df, $this->end);

        return str_replace([' ', '/'], ['-', '-'], $name);
    }

    public function name()
    {
        return 'Report';
    }

    /**
     * Generates the output of a report for a given type.
     *
     * @param string   $type   html|pdf|csv
     * @param bool     $stream when true, streams the resulting file to the client (pdf, csv only)
     * @param Response $res    when streaming, response object to use
     *
     * @return string|array|false
     */
    public function output($type, $stream = false, Response $res = null)
    {
        // $this->organization->useTimezone();

        $type = strtolower($type);

        if ($type == 'html') {
            $this->htmlOutput = true;

            // NOTE host name has the development port number stripped,
            // otherwise the css is not loaded

            $data = [
                'css' => 'file://'.INFUSE_PUBLIC_DIR.'/css/report.css',
                'header' => $this->getHeader(),
                'sections' => $this->getSections(),
            ];

            $this->htmlOutput = false;

            $view = new View('report', $data);

            return $view->render();
        } elseif ($type == 'pdf') {
            $html = $this->output('html');

            // Run wkhtmltopdf
            $descriptorspec = [
                0 => ['pipe', 'r'], // stdin
                1 => ['pipe', 'w'], // stdout
                2 => ['pipe', 'w'], // stderr
            ];

            $process = proc_open(WKHTMLTOPDF_CMD, $descriptorspec, $pipes);

            // Send the HTML on stdin
            fwrite($pipes[0], $html);
            fclose($pipes[0]);

            // Read the outputs
            $pdf = stream_get_contents($pipes[1]);
            $errors = stream_get_contents($pipes[2]);

            // Close the process
            fclose($pipes[1]);
            $return_value = proc_close($process);

            // Handle errors
            if ($errors) {
                error_log($errors);
            }

            // Output the results
            if ($stream) {
                $res->setContentType('application/pdf')
                    ->setHeader('Cache-Control', 'public, must-revalidate, max-age=0')
                    ->setHeader('Pragma', 'public')
                    ->setHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT')
                    ->setHeader('Last-Modified', gmdate('D, d M Y H:i:s').' GMT')
                    ->setHeader('Content-Length', strlen($pdf))
                    ->setHeader('Content-Disposition', 'attachment; filename="'.$this->baseFilename().'.pdf";')
                    ->setBody($pdf);
            } else {
                return $pdf;
            }
        } elseif ($type == 'csv') {
            $output = [];

            $header = $this->getHeader();
            foreach ($header as $key => $value) {
                $output[] = [$key, $value];
            }

            $output[] = [];

            $sections = $this->getSections();
            foreach ($sections as $section) {
                if (isset($section['title'])) {
                    $output[] = [$section['title']];
                }

                if (isset($section['keyvalue'])) {
                    foreach ($section['keyvalue'] as $key => $value) {
                        $output[] = [$key, $value];
                    }

                    $output[] = [];
                }

                $entireTable = array_merge(
                    [(array) U::array_value($section, 'header')],
                    (array) U::array_value($section, 'rows'),
                    [(array) U::array_value($section, 'footer')]);

                foreach ($entireTable as $row) {
                    $output[] = $row;
                }

                $output[] = [];
            }

            $csv = fopen('php://output', 'w');

            ob_start();
            foreach ($output as $row) {
                fputcsv($csv, $row);
            }
            fclose($csv);
            $output = ob_get_clean();

            if ($stream) {
                $res->setContentType('text/csv')
                    ->setHeader('Cache-Control', 'public, must-revalidate, max-age=0')
                    ->setHeader('Pragma', 'public')
                    ->setHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT')
                    ->setHeader('Last-Modified', gmdate('D, d M Y H:i:s').' GMT')
                    ->setHeader('Content-Length', strlen($output))
                    ->setHeader('Content-Disposition', 'attachment; filename="'.$this->baseFilename().'".csv')
                    ->setBody($output);
            } else {
                return $output;
            }
        }

        return false;
    }

    protected function getHeader()
    {
        return [
            'Title' => $this->name(),
            'Organization' => $this->organization->name,
            'Start Date' => date($this->df, $this->start),
            'End Date' => date($this->df, $this->end),
        ];
    }

    abstract protected function getSections();
}
