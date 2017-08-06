<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace App\Volunteers\Console;

use App\Volunteers\Models\VolunteerHour;

class CLI
{
    public function resendVerificationRequests($req, $res)
    {
        if (!$req->isCli()) {
            return $res->setCode(404);
        }

        $hours = VolunteerHour::where('organization', $req->params('org'))
            ->where('approved', false)
            ->all();

        echo "Resending verification requests for organization\n";

        $n = 0;
        foreach ($hours as $hour) {
            if ($hour->requestThirdPartyVerification()) {
                ++$n;
            }
        }

        echo "Sent $n verification request(s)\n";
    }

    // called with `php public/index.php /volunteers/markInactive org_id filename.txt`
    // filename.txt should have an email on each line to mark inactive
    public function markInactive($req, $res)
    {
        $org = $req->cliArgs(2);
        $file = $req->cliArgs(3);
        $emails = explode("\n", file_get_contents($file));

        if (!$org || !$file) {
            exit('Invalid arguments. Usage: php public/index.php /volunteers/markInactive org_id filename.txt');
        }

        $n = 0;
        foreach ($emails as $email) {
            $email = trim($email);
            if (!$email) {
                continue;
            }

            $db = $this->getApp()['database']->getDefault();
            $query = $db->update('Volunteers')
                ->values(['active' => 0])
                ->where('organization', $org)
                ->where('uid=SELECT uid FROM Users WHERE email="'.$email.'")');
            if ($query->execute()) {
                ++$n;
            }
        }

        echo "Marked $n volunteer(s) as inactive\n";
    }
}
