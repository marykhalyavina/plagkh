<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * plagkh Plagiarism Plugin - Handle plagiairsm check similarity score update
 * @package   plagiarism_plagkh
 * @copyright 2023 plagkh
 * @author    Mariya Khalyavina
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_plagkh\task;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/plagiarism/plagkh/classes/plagiarism_plagkh_logs.class.php');

/**
 * plagkh Plagiarism Plugin - Handle plagiairsm check similarity score update
 */
class plagiarism_plagkh_updatereports extends \core\task\scheduled_task {
    /**
     * get scheduler name, this will be shown to admins on schedulers dashboard
     */
    public function get_name() {
        return get_string('clupdatereportscores', 'plagiarism_plagkh');
    }

    /**
     * execute the task
     */
    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/plagiarism/plagkh/classes/plagiarism_plagkh_comms.class.php');
        $this->update_reports();
    }

    /**
     * sync files with plagkh API
     */
    private function update_reports() {
        global $DB;

        $canloadmoredata = true;
        $maxdataloadloops = PLAGIARISM_PLAGKH_CRON_MAX_DATA_LOOP;

        while ($canloadmoredata && (--$maxdataloadloops) > 0) {
            $submissionsinstances = array();

            $expectedfinishtime = strtotime('- 1 minutes');

            $submissions = $DB->get_records_select(
                "plagiarism_plagkh_files",
                "statuscode = ? AND lastmodified < ? AND (similarityscore IS NULL)",
                array('pending', $expectedfinishtime),
                '',
                '*',
                0,
                PLAGIARISM_PLAGKH_CRON_QUERY_LIMIT
            );

            $canloadmoredata = count($submissions) == PLAGIARISM_PLAGKH_CRON_QUERY_LIMIT;

            // Add submission ids to the request.
            foreach ($submissions as $clsubmission) {
                // Only add the submission to the request if the module still exists.
                if ($cm = get_coursemodule_from_id('', $clsubmission->cm)) {
                    $submissioninstance = new \stdClass();
                    $submissioninstance->courseModuleId = $clsubmission->cm;
                    $submissioninstance->moodleUserId = $clsubmission->userid;
                    $submissioninstance->identitfier = $clsubmission->identifier;
                    array_push($submissionsinstances, $submissioninstance);
                } else {
                    $clsubmission->statuscode = 'error';
                    $clsubmission->errormsg = 'course module (cm) wasnt found for this record';
                    if (!$DB->update_record('plagiarism_plagkh_files', $clsubmission)) {
                        \plagiarism_plagkh_logs::add(
                            "Update record failed (CM: " . $cm->id . ", User: " . $clsubmission->userid . ") - ",
                            "UPDATE_RECORD_FAILED"
                        );
                    }
                }
            }

            if (count($submissionsinstances) > 0) {
                try {

                    if (!\plagiarism_plagkh_comms::test_plagkh_connection('scheduler_task')) {
                        return;
                    }

                    $plagkhcomms = new \plagiarism_plagkh_comms();
                    $scaninstances = $plagkhcomms->get_plagiarism_scans_instances($submissionsinstances);
                    if (count($scaninstances) > 0) {
                        foreach ($scaninstances as $clscaninstance) {

                            $currentsubmission = $DB->get_record(
                                'plagiarism_plagkh_files',
                                array(
                                    'cm' => $clscaninstance->courseModuleId,
                                    'userid' => $clscaninstance->moodleUserId,
                                    'identifier' => $clscaninstance->identitfier
                                )
                            );

                            if (isset($currentsubmission)) {
                                $currentsubmission->externalid = $clscaninstance->scanId;
                                if ($clscaninstance->status == 1) {
                                    $currentsubmission->statuscode = 'success';
                                    $currentsubmission->similarityscore = round($clscaninstance->plagiarismScore, 1);
                                    $currentsubmission->ischeatingdetected = $clscaninstance->isCheatingDetected;
                                    if (!$DB->update_record('plagiarism_plagkh_files', $currentsubmission)) {
                                        \plagiarism_plagkh_logs::add(
                                            "Update record failed (CM: " . $cm->id . ", User: "
                                                . $currentsubmission->userid . ") - ",
                                            "UPDATE_RECORD_FAILED"
                                        );
                                    }
                                } else if ($clscaninstance->status == 2) {
                                    $currentsubmission->statuscode = 'error';
                                    $currentsubmission->errormsg = $clscaninstance->errorMessage;
                                    if (!$DB->update_record('plagiarism_plagkh_files', $currentsubmission)) {
                                        \plagiarism_plagkh_logs::add(
                                            "Update record failed (CM: " . $cm->id . ", User: "
                                                . $currentsubmission->userid . ") - ",
                                            "UPDATE_RECORD_FAILED"
                                        );
                                    }
                                }
                            } else {
                                \plagiarism_plagkh_logs::add(
                                    "Submission not found for plagkh API scan instances with the identifier: "
                                        . $clscaninstance->identitfier,
                                    "SUBMISSION_NOT_FOUND"
                                );
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \plagiarism_plagkh_logs::add(
                        "Update reports failed - " . $e->getMessage(),
                        "API_ERROR"
                    );
                }
            }
        }

        return true;
    }
}