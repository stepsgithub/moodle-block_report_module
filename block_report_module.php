<?php

class block_report_module extends block_base {

    var $tempcontent;

    function init() {
        global $PAGE;
        $this->title = get_string('reportModuleTitle', 'block_report_module');
        $this->tempcontent = '';
    }

    function preferred_width() {
        return 210;
    }

    function create_item($visiblename,$link,$icon='',$class='') {
        $this->tempcontent .= '<div><a class="'.$class.'" href="'.$link.'"><img src="'.$icon.'" alt="icon" /> '.
                               $visiblename.'</a></div>'."\n";
    }

    function get_content() {
        global $CFG;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->create_item(get_string('reportModuleItem1', 'block_report_module'), $CFG->wwwroot.'/blocks/report_module/coursestandard.php',
                           $CFG->wwwroot.'/blocks/report_module/pic/arow.gif');
        $this->create_item(get_string('reportModuleItem2', 'block_report_module'), $CFG->wwwroot.'/blocks/report_module/reportcourse.php',
                           $CFG->wwwroot.'/blocks/report_module/pic/book.gif');
        $this->create_item(get_string('reportModuleItem3', 'block_report_module'), $CFG->wwwroot.'/blocks/report_module/selectstudent.php',
                           $CFG->wwwroot.'/blocks/report_module/pic/girl.gif');
        $this->create_item(get_string('reportModuleItem4', 'block_report_module'), $CFG->wwwroot.'/blocks/report_module/coursepassrate.php',
                           $CFG->wwwroot.'/blocks/report_module/pic/ok.gif');
        $this->create_item(get_string('reportModuleItem5', 'block_report_module'), $CFG->wwwroot.'/blocks/report_module/studenttrack.php',
                           $CFG->wwwroot.'/blocks/report_module/pic/roll.jpg');

        $this->tempcontent .= '<p></p>';

        $this->content         =  new object();
        $this->content->text   = $this->tempcontent;

        $this->content->footer = ' ';

        return $this->content;
    }

    function cron() {
        global $DB;

        $status = true;

        require_once('lib/function.php');

        if ($courses = $DB->get_records_select('reportmodule', array())) {
            $output = '';
            $separate = ',';

            foreach ($courses as $course) {
                $courseUserArray = array();
                $userPassArray = array();

                $userIDDataArray = array();
                $actionIDNameArray = array();

                $courseID = $course->courseid;
                $students = get_course_students($courseID);
                $timeDifferent = getTimeDifferent('all');
                if($students) {
                    foreach( $students as $theUserID => $theStudentObj ) {
                        if( !$DB->get_record('user', array('id'=>$theUserID)) ) {
                            break;
                        }
                        $userPass = true;
                        $courseUserLogData = produceUserCourseLog($theUserID, $courseID, $timeDifferent);
                        $courseUserArray[$theUserID] = produceCourseUserArray($theUserID, $courseID, $courseUserLogData, $userPass);
                        $userPassArray[$theUserID] = $userPass;
                    }
                }

                /*
                $category = $DB->get_record('course_categories', array('id'=>$course->category));
                $categoryName = format_string($category->name);
                */

                $course = $DB->get_record('course', array('id'=>$courseID));
                $courseName = format_string($course->fullname);

                foreach($courseUserArray as $theUserID => $courseUserData) {
                    $user = $DB->get_record('user', array('id'=>$theUserID));
                    $userIDDataArray[$theUserID] = array();
                    $userIDDataArray[$theUserID]['name'] = $user->firstname.$user->lastname;
                    $userIDDataArray[$theUserID]['description'] = $user->description;
                    $userIDDataArray[$theUserID]['email'] = $user->email;

                    foreach ($courseUserData as $courseUserActionID => $courseUserActionContent) {
                        $actionIDNameArray[$courseUserActionID] =
                            ((getCourseActionModName($courseUserActionID))?
                                getCourseActionModName($courseUserActionID):get_string('courseTime3', 'reportmodule'));
                    }
                }

                foreach ($courseUserArray as $userID => $actionInfo) {
                    $output .= $course->id . $separate;
                    $output .= $course->fullname . $separate;

                    //$output .= $userIDDataArray[$userID]['name'] . $separate;
                    //$output .= $userIDDataArray[$userID]['description'] . $separate;
                    $output .= $userIDDataArray[$userID]['email'] . $separate;

                    $passType = 0;
                    $coursePass = false;
                    $courseTime = 0;
                    $courseDate = '';
                    $actionPass = false;
                    $actionScore = 0;
                    $actionDate = 0;
                    $actionQuiz = 0;
                    foreach($actionInfo as $actionID => $actionContent) {
                        if ($actionID == 'course') {
                            if($actionContent['timeEnable']) {
                                $coursePass = $actionContent['timePass'];
                                $courseTime = $actionContent['time'];
                                $courseDate = $actionContent['date']?date('Y/m/d',$actionContent['date']):'';

                                $passType += 1;
                            }
                        }

                        if($actionContent['timeEnable']) {
                            //$output .= $actionContent['time'] . $separate;
                        }
                        if($actionContent['scoreEnable']) {
                            //$output .= $actionContent['score'] . $separate;

                            // First Quiz
                            if ($actionContent['itemmodule'] == 'quiz' && !$actionQuiz) {
                                $actionPass = $actionContent['scorePass'];
                                $actionScore = $actionContent['score'];
                                $actionDate = $actionContent['scoreDate']?date('Y/m/d',$actionContent['scoreDate']):'';
                                $actionQuiz ++;

                                $passType += 2;
                            }
                        }

                        if ( ($actionContent['timePass'] AND $actionContent['scorePass']) ) {
                            //$output .= '通過' . $separate;
                        } else {
                            //$output .= '不通過' . $separate;
                        }
                    }
                    //$output .= $userPassArray[$userID]?'通過':'不通過';
                    $output .= $passType . $separate . ($coursePass?'1':'0') . $separate . ($actionPass?'1':'0') . $separate . $courseTime . $separate . $actionScore . $separate . $courseDate . $separate . $actionDate;
                    $output .= "\r\n";
                }
            }
        }

        $temp = tmpfile();
        fwrite($temp, $output);
        fseek($temp, 0);

        $meta_data = stream_get_meta_data($temp);
        $filename = $meta_data["uri"];

        // FTP access parameters
        $host = 'FTP_HOST';
        $usr = 'FTP_USER';
        $pwd = 'FTP_PASSWORD';

        // file to move:
        $local_file = $filename;
        $ftp_path = '/report_' . date('j') . '.txt';

        // connect to FTP server (port 21)
        $conn_id = ftp_connect($host, 21) or die ("Cannot connect to host");

        // send access parameters
        ftp_login($conn_id, $usr, $pwd) or die("Cannot login");

        // turn on passive mode transfers (some servers need this)
        ftp_pasv ($conn_id, true);

        // perform file upload
        $upload = ftp_put($conn_id, $ftp_path, $local_file, FTP_ASCII);

        // check upload status:
        print (!$upload) ? 'Cannot upload' : 'Upload complete';
        print "\n";

        /*
        ** Chmod the file (just as example)
        */

        // If you are using PHP4 then you need to use this code:
        // (because the "ftp_chmod" command is just available in PHP5+)
        if (!function_exists('ftp_chmod')) {
            function ftp_chmod($ftp_stream, $mode, $filename){
                return ftp_site($ftp_stream, sprintf('CHMOD %o %s', $mode, $filename));
            }
        }

        // try to chmod the new file to 666 (writeable)
        if (ftp_chmod($conn_id, 0666, $ftp_path) !== false) {
            print $ftp_path . " chmoded successfully to 666\n";
        } else {
            print "could not chmod $file\n";
        }

        // close the FTP stream
        ftp_close($conn_id);

        fclose($temp); // this removes the file

        return $status;
    }
}
