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
 * lib.php - Contains Plagiarism plugin specific functions called by Modules.
 * @package   plagiarism_plagkh
 * @copyright 2023 plagkh
 * @author    Mariya Khalyavina
 */

defined('MOODLE_INTERNAL') || die();

// Constants.
define('PLAGIARISM_PLAGKH_DEFAULT_MODULE_CMID', 0);
$clsupportedsubmissiontypes = array('file', 'text_content', 'forum_post', 'quiz_answer');
define('PLAGIARISM_PLAGKH_SUPPORTED_SUBMISSION_TYPES', $clsupportedsubmissiontypes);


// Max file size 25mb.
define('PLAGIARISM_PLAGKH_MAX_FILE_UPLOAD_SIZE', 26214400);
define('PLAGIARISM_PLAGKH_CRON_QUERY_LIMIT', 100);
define('PLAGIARISM_PLAGKH_CRON_MAX_DATA_LOOP', 256);
define('PLAGIARISM_PLAGKH_MAX_FILENAME_LENGTH', 180);
define('PLAGIARISM_PLAGKH_LOGS_PREFIX', 'log_');



// plagkh support file types.
$plagkhacceptedfiles = array(
    // Textual.
    '.html', '.txt', '.csv', '.htm', '.docx', '.doc'
);
define('PLAGIARISM_PLAGKH_ACCEPTED_FILES', $plagkhacceptedfiles);
define('DEFAULT_DATABASE_PLAGKHDB_ID', 'DEFAULT_DATABASE_PLAGKHDB_ID');

// plagkh retry array in seconds.
define('PLAGIARISM_PLAGKH_RETRY', array(0, 2.5, 3, 5, 10));
define('PLAGIARISM_PLAGKH_MAX_RETRY', 5);
define('PLAGIARISM_PLAGKH_DEFUALT_EULA_VERSION', '2023032700');
define('PLAGIARISM_PLAGKH_EULA_FIELD_NAME', 'plagiarism_plagkh_latesteulaversion ');

