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
 * plagkh rate limit exception
 * @package   plagiarism_plagkh
 * @copyright 2023 plagkh
 * @author    Mariya Khalyavina
 */

/** plagkh rate limit exception */
class plagiarism_plagkh_rate_limit_exception extends Exception {
    /**
     * constructor
     */
    public function __construct() {
        parent::__construct("Слишком много запросов. Пожалуйста, подождите, прежде чем повторить попытку.", 429);
    }
}