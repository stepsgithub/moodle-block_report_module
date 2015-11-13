<?php

require_once('Computationtime.php');

//require_once('model.php');

//course categories
function categoriesdata() {
    $categories = array();
    $all_category = get_categories();
    foreach ($all_category as $category) {
        $categories["$category->id"] = format_string($category->name);
    }
    asort($categories);
    return $categories;
}    

    
//course
function coursesdata($categoryID='') {
    global $DB;
 
    $courses = array();

    if ($all_course = $DB->get_records("course", array("category"=>$categoryID), "fullname", "id,fullname,category")) {
        foreach ($all_course as $one_course) {
            $courses["$one_course->id"] = format_string($one_course->fullname);
        }
    }
    asort($courses);
    return $courses;
}

//user
function usersdata($courseID='') {
    global $CFG, $DB;

    $users = array();
    $course = $DB->get_record('course', array('id'=>$courseID));
    if ($course->id != SITEID) {
        if ($selectedgroup) {   // If using a group, only get users in that group.
            $courseusers = get_group_users($selectedgroup, 'u.lastname ASC', '', 'u.id, u.firstname, u.lastname, u.idnumber');
        } else {
            //$courseusers = get_course_users($course->id, '', '', 'u.id, u.firstname, u.lastname, u.idnumber');
            $context = get_context_instance( CONTEXT_COURSE, $courseID);
            $query = 'select u.id as id, firstname, lastname, idnumber, imagealt, email from ' . $CFG->prefix . 'role_assignments as a, ' . $CFG->prefix . 'user as u where contextid=' . $context->id . ' and roleid=5 and a.userid=u.id;';

            $courseusers = $DB->get_recordset_sql( $query );
        }
    } else {
        $courseusers = get_site_users("u.lastaccess DESC", "u.id, u.firstname, u.lastname, u.idnumber");
    }
    
    //if (count($courseusers) < COURSE_MAX_USERS_PER_DROPDOWN && !$showusers) {
        $showusers = 1;
    //}

    if ($showusers) {
        if ($courseusers) {
            foreach ($courseusers as $courseuser) {
                $users[$courseuser->id] = fullname($courseuser, has_capability('moodle/site:viewfullnames', $context));
            }
        }
        //if ($guest = get_guest()) {
        //    $users[$guest->id] = fullname($guest);
        //}
    }
    return $users;
}

function get_course_students($courseID='') {
    global $CFG, $DB;

    $users = array();
    $course = $DB->get_record('course', array('id'=>$courseID));
    if ($course->id != SITEID) {
        /*if ($selectedgroup) {   // If using a group, only get users in that group.
            $courseusers = get_group_users($selectedgroup, 'u.lastname ASC', '', 'u.id, u.firstname, u.lastname, u.idnumber');
        } else*/ {
            //$courseusers = get_course_users($course->id, '', '', 'u.id, u.firstname, u.lastname, u.idnumber');
            $context = get_context_instance( CONTEXT_COURSE, $courseID);
            $query = 'select u.id as id, firstname, lastname, idnumber, imagealt, email from ' . $CFG->prefix . 'role_assignments as a, ' . $CFG->prefix . 'user as u where contextid=' . $context->id . ' and roleid=5 and a.userid=u.id;';

            $courseusers = $DB->get_recordset_sql( $query );
        }
    } else {
        $courseusers = get_site_users("u.lastaccess DESC", "u.id, u.firstname, u.lastname, u.idnumber");
    }

    //if (count($courseusers) < COURSE_MAX_USERS_PER_DROPDOWN && !$showusers) {
        $showusers = 1;
    //}

    if ($showusers) {
        if ($courseusers) {
            foreach ($courseusers as $courseuser) {
                $users[$courseuser->id] = fullname($courseuser, has_capability('moodle/site:viewfullnames', $context));
            }
        }
        //if ($guest = get_guest()) {
        //    $users[$guest->id] = fullname($guest);
        //}
    }
    return $users;
}

//date
function dateformsdata() {
    $dateforms = array( 'day' => '日',
                        'week' => '星期',
                        'month' => '月份',
                        'year' => '年度');
    return $dateforms;
}

//format
function reportformatsdata() {
    $reportformats = array('showashtml' => get_string('displayonpage'),
                        'downloadasods' => get_string('downloadods'));
    return $reportformats;
}

//courseActionItem
function getCourseActionItem($courseID) {
    global $DB;

    $courseActionItem = $DB->get_records_select('grade_items', 'courseid="'.$courseID.'" AND itemtype="mod"');
    return $courseActionItem;
}

//courseActionItem Check
function courseActionItemCheck($courseActionItemOneobj) {
    switch ($courseActionItemOneobj->itemmodule) {
        case 'lesson':  //單元課程
            return $courseActionItemOneobj;
            break;
        case 'assignment': //作業
            return $courseActionItemOneobj;
            break;
        case 'quiz':  //考試
            return $courseActionItemOneobj;
            break;
        case 'hotpot': //hot potato test
            return $courseActionItemOneobj;
            break;
        case 'scorm': //SCORM/AICC 課程包
            return $courseActionItemOneobj;
            break;
        default:
            return false;
            break;
    }
    return false;
}

//mycourses
function reportmycoursesdata($userID) {
    $mycourses = array();
    if ($mycoursesobj = enrol_get_users_courses($userID, null, null, false, 101)) {
        foreach ($mycoursesobj as $mycourse) {
            if ($mycourse->id){
                $mycourses[$mycourse->id] = format_string($mycourse->fullname);
            }
        }
    }
    return $mycourses;
}

//caculate courseAction HotPot test time total
function caculateHotPotTime($userID, $courseID, $hotpotID) {
    global $DB;

    if ( !$hotpot = $DB->get_record("hotpot", array("id"=>$hotpotID, "course"=>$courseID)) ) {
        return false;
    }
    if ( $all_hotpot_attempt =
        $DB->get_records_select("hotpot_attempts", 'hotpot="'.$hotpotID.'" AND userid="'.$userID.'"')
    ) {
        $actionName = 'hotpot';
        $actionTimeOrderArray = produceTimeInActionLog($userID, $courseID, $actionName, $hotpotID);
        $courseActionTime = calculateActionInteTime($actionTimeOrderArray);
    } else {
        return false;
    }
    return $courseActionTime;
}

//caculate courseAction Quiz time total
function caculateQuizTime($userID, $courseID, $quizID) {
    global $DB;

    if ( !$quiz = $DB->get_record("quiz", array("id"=>$quizID, "course"=>$courseID)) ) {
        return false;
    }
    if ( $all_quiz_attempt =
        $DB->get_records_select("quiz_attempts", 'quiz="'.$quizID.'" AND userid="'.$userID.'"')
    ) {
        $actionName = 'quiz';
        $actionTimeOrderArray = produceTimeInActionLog($userID, $courseID, $actionName, $quizID);
        $courseActionTime = calculateActionInteTime($actionTimeOrderArray);
    } else {
        return false;
    }
    return $courseActionTime;
}

//caculate courseAction Quiz time total
function caculateQuizDate($userID, $courseID, $quizID) {
    global $DB;

    if ( !$quiz = $DB->get_record("quiz", array("id"=>$quizID, "course"=>$courseID)) ) {
        return false;
    }
    if ( $all_quiz_attempt =
        $DB->get_records_select("quiz_attempts", 'quiz="'.$quizID.'" AND userid="'.$userID.'"')
    ) {
        $max = 0;
        $courseActionDate = '';
        foreach ( $all_quiz_attempt as $quiz_attempt ) {
            $current = $quiz_attempt->sumgrades;
            if ($current - $max > 0) {
                $courseActionDate = $quiz_attempt->timefinish;
                $max = $current;
            }
        }
    } else {
        return false;
    }
    return $courseActionDate;
}

//caculate courseAction Lesson time total
function caculateLessonTime($userID, $courseID, $lessonID) {
    global $DB;

    if ( !$lesson = $DB->get_record("lesson", array("id"=>$lessonID, "course"=>$courseID)) ) {
        return false;
    }
    if ( $all_lesson_timer =
        $DB->get_records_select("lesson_timer", 'lessonid="'.$lessonID.'" AND userid="'.$userID.'"', "starttime")
    ) {
        $actionName = 'lesson';
        $actionTimeOrderArray = produceTimeInActionLog($userID, $courseID, $actionName, $lessonID);
        $courseActionTime = calculateActionInteTime($actionTimeOrderArray);
    } else {
        return false;
    }
    return $courseActionTime;
}

//caculate courseAction scorm time total
function caculateScormTime($userID, $courseID, $scormID) {
    global $DB;

    if (!$scorm = $DB->get_record("scorm", array("id"=>$scormID))) {
        return false;
    }
    $courseActionTime = calculateInteTime(integrate_log($userID, $courseID, $scormID));

    return $courseActionTime;
}

//caculate courseAction Lesson time total
function getActionItemModuleName($actionID) {
    global $DB;

    if ( !$actionItem = $DB->get_record("grade_items", array("id"=>$actionID)) ) {
        return false;
    }
    return $actionItem->itemmodule;
}

//caculate user of course log produce course total time
function  produceUserCourseLog($userID, $courseID, $diffTime="0") {
      //pass by this return for produceCourseUserArray
   
    global $DB;
 
    if( !$user = $DB->get_record('user', array('id'=>$userID)) ) {
        return false;
        //error('不正確的學員名稱!');
    }
    
    if( !$courseusers = get_course_students($courseID) ) {
        return false;
        //error('這門課程目前沒有學生參加!');
    }
    
    if ( !$courseActionItem = getCourseActionItem($courseID) ) {
        return false;
        //error('課程未有活動!'); 
    }
    
    if ( !$courseStandardArray = getReportModuleConfig($courseID) ) {
        return false;
        //error('課程尚未設定!');
    }
    
    $userPassArray = array(); //$userPassArray[userID] = true
    
    $Joins = array();
    //$Joins[] = "l.module = 'course'";
    //$Joins[] = "l.action = 'view'";
    $Joins[] = "l.course = '$courseID'";
    $Joins[] = "l.userid = '$userID'";
    
    if ($diffTime) {
      $Joins[] = "l.time > '$diffTime'";
    }

    $order = ' l.time ASC ';
    $limitfrom = '';
    $limitnum = 50000;
    $totalcount = '';

    $selector = implode(' AND ', $Joins);
    $courseUserLogs = get_logs($selector, null, $order, $limitfrom, $limitnum, $totalcount);
        
    $courseUserLogData = array();
    foreach ($courseUserLogs as $courseUserLog) {
            $courseUserLogData[$courseUserLog->id] =
            array(//'userid' => $courseUserLog->userid,
                  //'name'   => $courseUserLog->firstname.$courseUserLog->lastname,
                  'time'   => $courseUserLog->time,
                  //'course' => $courseUserLog->course,
                  //'module' => $courseUserLog->module,
                  //'action' => $courseUserLog->action,
                  //'info'   => $courseUserLog->info
            );
    }
    return $courseUserLogData;
}

//caculate user of action log produce course action time
function  produceTimeInActionLog($userID, $courseID, $actionName, $actionID, $diffTime="0") {
      //pass by this return for produceCourseUserArray

    global $DB;
    
    if( !$user = $DB->get_record('user', array('id'=>$userID)) ) {
        return false;
        //error('不正確的學員名稱!');
    }
    
    if( !$courseusers = get_course_students($courseID) ) {
        return false;
        //error('這門課程目前沒有學生參加!');
    }
    
    if ( !$courseActionItem = getCourseActionItem($courseID) ) {
        return false;
        //error('課程未有活動!'); 
    }
    
    if ( !$courseStandardArray = getReportModuleConfig($courseID) ) {
        return false;
        //error('課程尚未設定!');
    }
    
    $Joins = array();
    switch ($actionName) {
        case 'lesson' :
            $Joins[] = "l.module = 'lesson'";
            $module = $DB->get_record("modules", array("name"=>"lesson"));
            $moduleID = $module->id;
        break;
        case 'assignment' :
            $Joins[] = "l.module = 'assignment'";
            $module = $DB->get_record("modules", array("name"=>"assignment"));
            $moduleID = $module->id;
        break;
        case 'quiz' :
            $Joins[] = "l.module = 'quiz'";
            $module = $DB->get_record("modules", array("name"=>"quiz"));
            $moduleID = $module->id;
        break;
        case 'hotpot' :
            $Joins[] = "l.module = 'hotpot'";
            $module = $DB->get_record("modules", array("name"=>"hotpot"));
            $moduleID = $module->id;
        break;
        case 'scorm' :
            $Joins[] = "l.module = 'scorm'";
            $module = $DB->get_record("modules", array("name"=>"scorm"));
            $moduleID = $module->id;
        break;
        default:
            return false;
        break;
    }
    
    $Joins[] = "l.course = '$courseID'";
    $Joins[] = "l.userid = '$userID'";
    
    $course_module = $DB->get_record("course_modules", array("instance"=>$actionID, "course"=>$courseID, "module"=>$moduleID));
    $Joins[] = "l.cmid = '$course_module->id'";
    
    if ($diffTime) {
      $Joins[] = "l.time > '$diffTime'";
    }
    
    $order = ' l.time ASC ';
    $limitfrom = '';
    $limitnum = 50000;
    $totalcount = '';
    $selector = implode(' AND ', $Joins);

    $courseUserActionLogs = get_logs($selector, null, $order, $limitfrom, $limitnum, $totalcount);
    
    $actionTimeOrderArray = array();
    foreach ($courseUserActionLogs as $courseUserActionLog) {
            $actionTimeOrderArray[] = $courseUserActionLog->time;
    }
    return $actionTimeOrderArray;
}

//caculate course user action log inter time by motifing $inteTimeMin
function calculateActionInteTime($actionTimeOrderArray, $inteTimeMin=60) {
    $totalTime = 0;
    $compareTimeInter = $inteTimeMin * 60;
    
    if ($actionTimeOrderArray) {
        $firstElement = array_shift($actionTimeOrderArray);
    } else {
        return round($totalTime/60/60, 2); //hours x.xx
    }
    
    foreach($actionTimeOrderArray as $actionTimeOrder) {
        $timeThrough = $actionTimeOrder - $firstElement;
        if ($timeThrough <= $compareTimeInter) {
            $totalTime = $totalTime + $timeThrough;
        }
        $firstElement = $actionTimeOrder;
    }
    return round($totalTime/60/60, 2); //hours x.xx
}

//conpare now time with user select past time cacualte different time
function getTimeDifferent($dateform) {
      switch ($dateform) {
            case 'day' :
                  $nowTime = time();
                  $nowTime = $nowTime - ( strftime("%H", $nowTime)*60*60 +
                                          strftime("%M", $nowTime)*60 +
                                          strftime("%S", $nowTime) );
                  break;
            case 'week' :
                  $nowTime = time();
                  $nowTime = $nowTime - ( strftime("%u", $nowTime)*60*60*24 +
                                          strftime("%H", $nowTime)*60*60 +
                                          strftime("%M", $nowTime)*60 +
                                          strftime("%S", $nowTime) );
                  break;
            case 'month' :
                  $nowTime = time();
                  $nowTime = $nowTime - ( strftime("%d", $nowTime)*60*60*24 +
                                          strftime("%H", $nowTime)*60*60 +
                                          strftime("%M", $nowTime)*60 +
                                          strftime("%S", $nowTime) );
                  break;
            case 'year' :
                  $nowTime = time();
                  $nowTime = $nowTime - ( strftime("%m", $nowTime)*60*60*24*30 +
                                          strftime("%d", $nowTime)*60*60*24 +
                                          strftime("%H", $nowTime)*60*60 +
                                          strftime("%M", $nowTime)*60 +
                                          strftime("%S", $nowTime) );
                  break;
            default:
                  $nowTime = 0;
                  break;
      }
      return $nowTime;
}

//produce course of user array ,input log and other data
function produceCourseUserArray($userID, $courseID, $courseUserLogData, &$userPass) {
    global $DB;

      //relating function produceUserCourseLog
      //use produceUserCourseLog's return as input $courseUserLogData

    if( !$user = $DB->get_record('user', array('id'=>$userID)) ) {
        return false;
        //error('不正確的學員名稱!');
    }

    if( !$courseusers = get_course_students($courseID) ) {
        return false;
        //error('這門課程目前沒有學生參加!');
    }

    if ( !$courseActionItem = getCourseActionItem($courseID) ) {
        return false;
        //error('課程未有活動!');
    }

    if ( !$courseStandardArray = getReportModuleConfig($courseID) ) {
        return false;
        //error('課程尚未設定!');
    }

    //set user pass is true, if has any false it will false
    $userPass = true;

    //compare time or score
        //compare course action course
    if ($courseStandardArray['course']['timeCheck']) {
        //compare time
        $timeEnable = true;
        $courseTime = calculateInteTime($courseUserLogData);
        $courseTimeStandard = ($courseStandardArray['course']['time'])
                               ?$courseStandardArray['course']['time']
                               :"0";
        $courseFirstDate = calculateFirstDate($courseUserLogData, $courseTimeStandard);
        $courseTimePass = ( ($courseTime >= $courseTimeStandard) ?true :false );
        //compare score
        $scoreEnable = false;
        $courseScore = 0;
        $courseScoreStandard = 0;
        $courseScorePass = true;
    } else {
        //compare time
        $timeEnable = false;
        $courseTime = 0;
        $courseTimeStandard = 0;
        $courseFirstDate = '';
        $courseTimePass = true;
        //compare score
        $scoreEnable = false;
        $courseScore = 0;
        $courseScoreStandard = 0;
        $courseScorePass = true;
    }

    //include course action data
    $courseUserArray = array();
    //$courseUserArray['course' or 'modID']['timeCheck' or 'score' ...]
    $courseUserArray['course'] = array(   'timeEnable'=>$timeEnable,
                                          'time'=>$courseTime,
                                          'date'=>$courseFirstDate,
                                          'timeStandard'=>$courseTimeStandard,
                                          'timePass'=>$courseTimePass,
                                          'scoreEnable'=>$scoreEnable,
                                          'score'=>$courseScore,
                                          'scoreStandard'=>$courseScoreStandard,
                                          'scorePass'=>$courseScorePass
                                 );
    //user pass array edit
    if ( !($courseUserArray['course']['timePass'] AND
            $courseUserArray['course']['scorePass']) ) {
            $userPass = false;
    }

    //compare action item
    foreach($courseActionItem as $courseActionItemOne) {
        //switch action item
        if ( !$courseActionItemOne = courseActionItemCheck($courseActionItemOne) ) {
            //if not need action item break the count
            break;
        }

        $courseActionDate = '';

        //setup action time
        switch ($courseActionItemOne->itemmodule) {
            case 'lesson':  //單元課程
                //compare mod time action mod id
                if ($courseStandardArray[$courseActionItemOne->id]['timeCheck']) {
                    //compare time
                    $timeEnable = true;
                    $courseActionTime = 0; //setup action time
                    $lessonID = $courseActionItemOne->iteminstance;
                    if ( $lessonTime = caculateLessonTime($userID, $courseID, $lessonID) ) {
                        $courseActionTime = $lessonTime;
                    }
                    //read action time standard
                    $courseActionTimeStandard = ($courseStandardArray[$courseActionItemOne->id]['time'])
                                                    ?$courseStandardArray[$courseActionItemOne->id]['time']
                                                    :"0";
                    $courseActionTimePass = ( ($courseActionTime >= $courseActionTimeStandard) ?true :false );
                } else {
                    //compare time
                    $timeEnable = false;
                    $courseActionTime = 0;
                    $courseActionTimeStandard = 0;
                    $courseActionTimePass = true;
                }
                //compare mod score action mod id
                if ($courseStandardArray[$courseActionItemOne->id]['scoreCheck']) {
                    //compare score
                    $scoreEnable = true;
                    $courseActionScore = 0;
                    $courseActionScore = getCourseActionScore($courseActionItemOne->id, $userID);
                    $courseActionScore = round($courseActionScore->finalgrade, 2);
                    //read action score standard
                    $courseActionScoreStandard = ($courseStandardArray[$courseActionItemOne->id]['score'])
                                                    ?$courseStandardArray[$courseActionItemOne->id]['score']
                                                    :"0";
                    $courseActionScorePass = ( $courseActionScore >= $courseActionScoreStandard ) ?true :false;
                } else {
                    //compare score
                    $scoreEnable = false;
                    $courseActionScore = 0;
                    $courseActionScoreStandard = 0;
                    $courseActionScorePass = true;
                }
            break;
            case 'assignment': //作業
                //compare mod time action mod id
                if ($courseStandardArray[$courseActionItemOne->id]['timeCheck']) {
                    //compare time
                    $timeEnable = true;
                    $courseActionTime = 0; //setup action time
                    $courseActionTimeStandard = 0;
                    $courseActionTimePass = true;
                } else {
                    //compare time
                    $timeEnable = false;
                    $courseActionTime = 0;
                    $courseActionTimeStandard = 0;
                    $courseActionTimePass = true;
                }
                //compare mod score action mod id
                if ($courseStandardArray[$courseActionItemOne->id]['scoreCheck']) {
                    //compare score
                    $scoreEnable = true;
                    $courseActionScore = 0;
                    $courseActionScore = getCourseActionScore($courseActionItemOne->id, $userID);
                    $courseActionScore = round($courseActionScore->finalgrade, 2);
                    //read action score standard
                    $courseActionScoreStandard = ($courseStandardArray[$courseActionItemOne->id]['score'])
                                                    ?$courseStandardArray[$courseActionItemOne->id]['score']
                                                    :"0";
                    $courseActionScorePass = ( $courseActionScore >= $courseActionScoreStandard ) ?true :false;
                } else {
                    //compare score
                    $scoreEnable = false;
                    $courseActionScore = 0;
                    $courseActionScoreStandard = 0;
                    $courseActionScorePass = true;
                }
            break;
            case 'quiz':  //考試
                //compare mod time action mod id
                if ($courseStandardArray[$courseActionItemOne->id]['timeCheck']) {
                    //compare time
                    $timeEnable = true;
                    $courseActionTime = 0; //setup action time
                    $quizID = $courseActionItemOne->iteminstance;
                    if ( $quizTime = caculateQuizTime($userID, $courseID, $quizID) ) {
                        $courseActionTime = $quizTime;
                    }
                    //read action time standard
                    $courseActionTimeStandard = ($courseStandardArray[$courseActionItemOne->id]['time'])
                                                    ?$courseStandardArray[$courseActionItemOne->id]['time']
                                                    :"0";
                    $courseActionTimePass = ( ($courseActionTime >= $courseActionTimeStandard) ?true :false );
                } else {
                    //compare time
                    $timeEnable = false;
                    $courseActionTime = 0;
                    $courseActionTimeStandard = 0;
                    $courseActionTimePass = true;
                }
                //compare mod score action mod id
                if ($courseStandardArray[$courseActionItemOne->id]['scoreCheck']) {
                    //compare score
                    $scoreEnable = true;
                    $courseActionScore = 0;
                    if($courseActionScore = getCourseActionScore($courseActionItemOne->id, $userID))
                        $courseActionScore = round($courseActionScore->finalgrade, 2);
                    //read action score standard
                    $courseActionScoreStandard = ($courseStandardArray[$courseActionItemOne->id]['score'])
                                                    ?$courseStandardArray[$courseActionItemOne->id]['score']
                                                    :"0";
                    $courseActionScorePass = ( $courseActionScore >= $courseActionScoreStandard ) ?true :false;
                    $quizID = $courseActionItemOne->iteminstance;
                    if ( $quizDate = caculateQuizDate($userID, $courseID, $quizID) ) {
                        $courseActionDate = $quizDate;
                    }
                } else {
                    //compare score
                    $scoreEnable = false;
                    $courseActionScore = 0;
                    $courseActionScoreStandard = 0;
                    $courseActionScorePass = true;
                }
            break;
            case 'hotpot': //hot potato test
                //compare mod time action mod id
                if ($courseStandardArray[$courseActionItemOne->id]['timeCheck']) {
                    //compare time
                    $timeEnable = true;
                    $courseActionTime = 0; //setup action time
                    $hotpotID = $courseActionItemOne->iteminstance;
                    if ( $hotpotTime = caculateHotPotTime($userID, $courseID, $hotpotID) ) {
                        $courseActionTime = $hotpotTime;
                    }
                    //read action time standard
                    $courseActionTimeStandard = ($courseStandardArray[$courseActionItemOne->id]['time'])
                                                    ?$courseStandardArray[$courseActionItemOne->id]['time']
                                                    :"0";
                    $courseActionTimePass = ( ($courseActionTime >= $courseActionTimeStandard) ?true :false );
                } else {
                    //compare time
                    $timeEnable = false;
                    $courseActionTime = 0;
                    $courseActionTimeStandard = 0;
                    $courseActionTimePass = true;
                }
                //compare mod score action mod id
                if ($courseStandardArray[$courseActionItemOne->id]['scoreCheck']) {
                    //compare score
                    $scoreEnable = true;
                    $courseActionScore = 0;
                    $courseActionScore = getCourseActionScore($courseActionItemOne->id, $userID);
                    $courseActionScore = round($courseActionScore->finalgrade, 2);
                    //read action score standard
                    $courseActionScoreStandard = ($courseStandardArray[$courseActionItemOne->id]['score'])
                                                    ?$courseStandardArray[$courseActionItemOne->id]['score']
                                                    :"0";
                    $courseActionScorePass = ( $courseActionScore >= $courseActionScoreStandard ) ?true :false;
                } else {
                    //compare score
                    $scoreEnable = false;
                    $courseActionScore = 0;
                    $courseActionScoreStandard = 0;
                    $courseActionScorePass = true;
                }
            break;
            case 'scorm':
                //compare mod time action mod id
                if ($courseStandardArray[$courseActionItemOne->id]['timeCheck']) {
                    //compare time
                    $timeEnable = true;
                    $courseActionTime = 0; //setup action time
                    $scormID = $courseActionItemOne->iteminstance;
                    if ( $scormTime = caculateScormTime($userID, $courseID, $scormID) ) {
                        $courseActionTime = $scormTime;
                    }
                    //read action time standard
                    $courseActionTimeStandard = ($courseStandardArray[$courseActionItemOne->id]['time'])
                                                    ?$courseStandardArray[$courseActionItemOne->id]['time']
                                                    :"0";
                    $courseActionTimePass = ( ($courseActionTime >= $courseActionTimeStandard) ?true :false );
                } else {
                    //compare time
                    $timeEnable = false;
                    $courseActionTime = 0;
                    $courseActionTimeStandard = 0;
                    $courseActionTimePass = true;
                }
                //compare mod score action mod id
                if ($courseStandardArray[$courseActionItemOne->id]['scoreCheck']) {
                    //compare score
                    $scoreEnable = true;
                    $courseActionScore = 0;
                    $courseActionScoreStandard = 0;
                    $courseActionScorePass = true;
                } else {
                    //compare score
                    $scoreEnable = false;
                    $courseActionScore = 0;
                    $courseActionScoreStandard = 0;
                    $courseActionScorePass = true;
                }
            break;
        }

        $courseUserArray[$courseActionItemOne->id] = array(
                                              'itemmodule'=>$courseActionItemOne->itemmodule,
                                              'timeEnable'=>$timeEnable,
                                              'time'=>$courseActionTime,
                                              'timeStandard'=>$courseActionTimeStandard,
                                              'timePass'=>$courseActionTimePass,
                                              'scoreEnable'=>$scoreEnable,
                                              'score'=>$courseActionScore,
                                              'scoreStandard'=>$courseActionScoreStandard,
                                              'scorePass'=>$courseActionScorePass,
                                              'scoreDate'=>$courseActionDate
                                            );
        //user pass array edit
        if ( !($courseUserArray[$courseActionItemOne->id]['timePass'] AND
                $courseUserArray[$courseActionItemOne->id]['scorePass']) ) {
                $userPass = false;
        }
    }
    return $courseUserArray;
}

//caculate user log inter time by motifing $inteTimeMin
function calculateInteTime($courseUserLogData, $inteTimeMin=60) {
      $startTime = 0;
      $middleTime = 0;
      $endTime = $inteTimeMin * 60;
      $totalTime = 0;
      foreach($courseUserLogData as $courseUserOneLogKey => $courseUserOneLogValue) {
            if ( $courseUserOneLogValue['time'] > ($startTime + $endTime) ) {
                  $startTime = $courseUserOneLogValue['time'];
                  $middleTime = $startTime;
            } else {
                  $totalTime = $totalTime + ($courseUserOneLogValue['time'] - $middleTime);
                  $middleTime = $courseUserOneLogValue['time'];
            }
      }
      return round($totalTime/60/60, 2); //hours x.xx
}

//caculate user log inter time by motifing $inteTimeMin
function calculateFirstDate($courseUserLogData, $courseTimeStandard, $inteTimeMin=60) {
      $startTime = 0;
      $middleTime = 0;
      $endTime = $inteTimeMin * 60;
      $totalTime = 0;
      foreach($courseUserLogData as $courseUserOneLogKey => $courseUserOneLogValue) {
            if ( $courseUserOneLogValue['time'] > ($startTime + $endTime) ) {
                  $startTime = $courseUserOneLogValue['time'];
                  $middleTime = $startTime;
            } else {
                  $totalTime = $totalTime + ($courseUserOneLogValue['time'] - $middleTime);
                  if ($totalTime > $courseTimeStandard)
                      return $courseUserOneLogValue['time'];
                  $middleTime = $courseUserOneLogValue['time'];
            }
      }
      return false;
}

//get course action item name by action id
function getCourseActionModName($actionModID) {
      global $DB;

      $courseActionModName = '';
      if ($courseActionMod = $DB->get_record('grade_items', array('id'=>$actionModID)))
          $courseActionModName = $courseActionMod->itemname;
      return $courseActionModName;
}

//get course action score by course action id and user id
function getCourseActionScore($courseActionID, $userID) {
      global $DB;

      $courseActionScore = $DB->get_record('grade_grades', array('itemid'=>$courseActionID, 'userid'=>$userID));
      return $courseActionScore;
}

//get report module data table of score standard by course id
function getReportModuleConfig($courseID) {
      global $DB;

      if( $reportmodule = $DB->get_record('reportmodule', array('courseid'=>$courseID)) ) {
            $courseStandardString = $reportmodule->info;
            $courseStandardArray = unserialize($courseStandardString);
            return $courseStandardArray;
      } else {
            return fales;
      }
}

//caculate one course pass rate by course id
function cacualateOneCourseRate($courseID) {
    
    if( !$courseusers = get_course_students($courseID) ) {
        return false;
        //error('這門課程目前沒有學生參加!');
    }
    
    if ( !$courseActionItem = getCourseActionItem($courseID) ) {
        return false;
        //error('課程未有活動!'); 
    }
    
    if ( !$courseStandardArray = getReportModuleConfig($courseID) ) {
        return false;
        //error('課程尚未設定!'); 
    }
    
    $userPassArray = array(); //$userPassArray[userID] = true
    
    $courseJoins = array();
    $courseJoins[] = "l.module = 'course'";
    $courseJoins[] = "l.action = 'view'";
    $courseJoins[] = "l.course = $courseID";

    foreach($courseusers as $courseuser) {
        $joins = $courseJoins;
        $joins[] = "l.userid = '$courseuser->id'";
        $order = ' l.time ASC ';
        $limitfrom = '';
        $limitnum = 50000;
        $totalcount = '';

        $selector = implode(' AND ', $joins);
        $courseUserLogs = get_logs($selector, null, $order, $limitfrom, $limitnum, $totalcount);
        
        $courseUserLogData = array();
        foreach ($courseUserLogs as $courseUserLog) {
            $courseUserLogData[$courseUserLog->id] =
            array('userid' => $courseUserLog->userid,
                  'name'   => $courseUserLog->firstname.$courseUserLog->lastname,
                  'time'   => $courseUserLog->time,
                  'course' => $courseUserLog->course,
                  'module' => $courseUserLog->module,
                  'action' => $courseUserLog->action,
                  'info'   => $courseUserLog->info);
        }
        
        $userPass = '';
        $courseUserTableForm = collectCourseUserInforamtion($courseuser->id, $courseUserLogData, $courseActionItem, $courseStandardArray, $userPass);
        $userPassArray[$courseuser->id] = $userPass;
    }
    
    $passCount = 0;
    $noPassCount = 0;
    foreach($userPassArray as $userPass) {
        if($userPass) {
            $passCount ++;
        }
    }
    return round($passCount/count($userPassArray), 4)*100;
}

//collect course of user information and user pass
function collectCourseUserInforamtion($courseUserID, $courseUserLogData, $courseActionItem, $courseStandardArray, &$userPass) {
    $userPass = true;
    $courseUserTableForm = array();
    
    //compare time or score
    $courseTime = calculateInteTime($courseUserLogData);
    $courseTimeStandard = (($courseStandardArray['course']['timeCheck']=="0")?'':
                            (($courseStandardArray['course']['time']=="0")?'':
                            $courseStandardArray['course']['time']));
    $courseTimePass = (($courseTime >= $courseTimeStandard) ?true :false);
    $courseScore = 0;
    $courseScoreStandard = 0;
    $courseScorePass = (($courseScore >= $courseScoreStandard) ?true :false);
    
    //course
    $courseUserTableForm['course'] = array('time'=>$courseTime,
                                            'score'=>$courseScore,
                                            'timeStandard'=>$courseTimeStandard,
                                            'scoreStandard'=>$courseScoreStandard,
                                            'timePass'=>$courseTimePass,
                                            'scorePass'=>$courseScorePass
                                    );
    //user pass array edit
    if ( !($courseUserTableForm['course']['timePass'] AND
            $courseUserTableForm['course']['scorePass']) ) {
            $userPass = false;
    }
    
    //action item
    foreach($courseActionItem as $courseActionItemOne) {
      

       //insert switch action item
       if ( !$courseActionItemOne = courseActionItemCheck($courseActionItemOne) ) {
            //if not need action item break the count
            break; //once error all not count (nerro error capical
      }
      //setup action time
      $courseActionTime = 0;
      switch ($courseActionItemOne->itemmodule) {
        case 'lesson':  //單元課程
            $courseActionTime = 0; //setup action time
            $lessonID = $courseActionItemOne->iteminstance;
            if ( $lessonTime = caculateLessonTime($userID, $courseID, $lessonID) ) {
                  $courseActionTime = $lessonTime;
            }
            break;
        case 'assignment': //作業
            $courseActionTime = 0; //setup action time
            break;
        case 'quiz':  //考試
            $courseActionTime = 0; //setup action time
            $quizID = $courseActionItemOne->iteminstance;
            if ( $quizTime = caculateQuizTime($userID, $courseID, $quizID) ) {
                  $courseActionTime = $quizTime;
            }
            break;
        case 'hotpot': //hot potato test
            $courseActionTime = 0; //setup action time
            $hotpotID = $courseActionItemOne->iteminstance;
            if ( $hotpotTime = caculateHotPotTime($userID, $courseID, $hotpotID) ) {
                  $courseActionTime = $hotpotTime;
            }
            break;
        case 'scorm':  //SCORM/AICC 課程包
            $courseActionTime = 0; //setup action time
            $scormID = $courseActionItemOne->iteminstance;
            if ( $quizTime = caculateScormTime($userID, $courseID, $scormID) ) {
                  $courseActionTime = $quizTime;
            }
            break;
      }

        $courseActionScore = getCourseActionScore($courseActionItemOne->id, $courseUserID);
        $courseActionScore = round($courseActionScore->finalgrade, 1);
        //read action time standard
        $courseActionTimeStandard =  (($courseStandardArray[$courseActionItemOne->id]['timeCheck']=="0")?'':
                                          (($courseStandardArray[$courseActionItemOne->id]['time']=="0")?'':
                                                $courseStandardArray[$courseActionItemOne->id]['time']));
        
        $courseActionScoreStandard =  (($courseStandardArray[$courseActionItemOne->id]['scoreCheck']=="0")?'':
                                        (($courseStandardArray[$courseActionItemOne->id]['score']=="0")?'':
                                        $courseStandardArray[$courseActionItemOne->id]['score']));
        
        $courseActionTimePass = ($courseActionTime >= $courseActionTimeStandard) ?true :false;
        $courseActionScorePass = ($courseActionScore >= $courseActionScoreStandard) ?true :false;
            
        $courseUserTableForm[$courseActionItemOne->itemname] = array('time'=>$courseActionTime,
                                                                    'score'=> $courseActionScore,
                                                                    'timeStandard'=>$courseActionTimeStandard,
                                                                    'scoreStandard'=>$courseActionScoreStandard,
                                                                    'timePass'=>$courseActionTimePass,
                                                                    'scorePass'=>$courseActionScorePass
                                                                );
        //user pass array edit
        if ( !($courseUserTableForm[$courseActionItemOne->itemname]['timePass'] AND
                $courseUserTableForm[$courseActionItemOne->itemname]['scorePass']) ) {
            $userPass = false;
        }
    }
    //var_dump($courseUserTableForm);
    return $courseUserTableForm;
}

//produce simple course of user array ,input log and other data
//for caculate one course pass rate
//return use pass or not (true or false)
function produceSimpleCourseUserArray($userID, $courseID, $courseUserLogData, &$userPass) {
      //relating function produceUserCourseLog
      //use produceUserCourseLog's return as input $courseUserLogData

    global $DB;
 
    if( !$user = $DB->get_record('user', array('id'=>$userID)) ) {
        return false;
        //error('不正確的學員名稱!');
    }
    
    if( !$courseusers = get_course_students($courseID) ) {
        return false;
        //error('這門課程目前沒有學生參加!');
    }
    
    if ( !$courseActionItem = getCourseActionItem($courseID) ) {
        return false;
        //error('課程未有活動!'); 
    }
    
    if ( !$courseStandardArray = getReportModuleConfig($courseID) ) {
        return false;
        //error('課程尚未設定!');
    }
    
    //compare time or score
        //compare course action course
    if ($courseStandardArray['course']['timeCheck']) {
        //compare time
        $courseTime = calculateInteTime($courseUserLogData);
        $courseTimeStandard = ($courseStandardArray['course']['time'])
                               ?$courseStandardArray['course']['time']
                               :"0";
        $courseTimePass = ( ($courseTime >= $courseTimeStandard) ?true :false );
        //compare score
        $courseScorePass = true;
    } else {
        //compare time
        $courseTimePass = true;
        //compare score
        $courseScorePass = true;
    }
    
    //include course action data
    $courseUserArray = array();
    //$courseUserArray['course' or 'modID']['timeCheck' or 'score' ...]
    $courseUserArray['course'] = array(   'timePass'=>$courseTimePass,
                                          'scorePass'=>$courseScorePass
                                 );
    //user pass array edit
    if ( !($courseUserArray['course']['timePass'] AND
            $courseUserArray['course']['scorePass']) ) {
            return false;
    }
    
    //compare action item
    foreach($courseActionItem as $courseActionItemOne) {
        //switch action item
        if ( !$courseActionItemOne = courseActionItemCheck($courseActionItemOne) ) {
            //if not need action item break the count
            break;
        }
        
        //setup action time
        switch ($courseActionItemOne->itemmodule) {
            case 'lesson':  //單元課程
                //compare mod time action mod id
                if ($courseStandardArray[$courseActionItemOne->id]['timeCheck']) {
                    //compare time
                    $courseActionTime = 0; //setup action time
                    $lessonID = $courseActionItemOne->iteminstance;
                    if ( $lessonTime = caculateLessonTime($userID, $courseID, $lessonID) ) {
                        $courseActionTime = $lessonTime;
                    }
                    //read action time standard
                    $courseActionTimeStandard = ($courseStandardArray[$courseActionItemOne->id]['time'])
                                                    ?$courseStandardArray[$courseActionItemOne->id]['time']
                                                    :"0";
                    $courseActionTimePass = ( ($courseActionTime >= $courseActionTimeStandard) ?true :false );
                } else {
                    //compare time
                    $courseActionTimePass = true;
                }
                //compare mod score action mod id
                if ($courseStandardArray[$courseActionItemOne->id]['scoreCheck']) {
                    //compare score
                    $courseActionScore = 0;
                    $courseActionScore = getCourseActionScore($courseActionItemOne->id, $userID);
                    $courseActionScore = round($courseActionScore->finalgrade, 2);
                    //read action score standard
                    $courseActionScoreStandard = ($courseStandardArray[$courseActionItemOne->id]['score'])
                                                    ?$courseStandardArray[$courseActionItemOne->id]['score']
                                                    :"0";
                    $courseActionScorePass = ( $courseActionScore >= $courseActionScoreStandard ) ?true :false;
                } else {
                    //compare score
                    $courseActionScorePass = true;
                }
            break;
            case 'assignment': //作業
                //compare mod time action mod id
                if ($courseStandardArray[$courseActionItemOne->id]['timeCheck']) {
                    //compare time
                    $courseActionTimePass = true;
                } else {
                    //compare time
                    $courseActionTimePass = true;
                }
                //compare mod score action mod id
                if ($courseStandardArray[$courseActionItemOne->id]['scoreCheck']) {
                    //compare score
                    $courseActionScore = 0;
                    $courseActionScore = getCourseActionScore($courseActionItemOne->id, $userID);
                    $courseActionScore = round($courseActionScore->finalgrade, 2);
                    //read action score standard
                    $courseActionScoreStandard = ($courseStandardArray[$courseActionItemOne->id]['score'])
                                                    ?$courseStandardArray[$courseActionItemOne->id]['score']
                                                    :"0";
                    $courseActionScorePass = ( $courseActionScore >= $courseActionScoreStandard ) ?true :false;
                } else {
                    //compare score
                    $courseActionScorePass = true;
                }
            break;
            case 'quiz':  //考試
                //compare mod time action mod id
                if ($courseStandardArray[$courseActionItemOne->id]['timeCheck']) {
                    //compare time
                    $courseActionTime = 0; //setup action time
                    $quizID = $courseActionItemOne->iteminstance;
                    if ( $quizTime = caculateQuizTime($userID, $courseID, $quizID) ) {
                        $courseActionTime = $quizTime;
                    }
                    //read action time standard
                    $courseActionTimeStandard = ($courseStandardArray[$courseActionItemOne->id]['time'])
                                                    ?$courseStandardArray[$courseActionItemOne->id]['time']
                                                    :"0";
                    $courseActionTimePass = ( ($courseActionTime >= $courseActionTimeStandard) ?true :false );
                } else {
                    //compare time
                    $courseActionTimePass = true;
                }
                //compare mod score action mod id
                if ($courseStandardArray[$courseActionItemOne->id]['scoreCheck']) {
                    //compare score
                    $courseActionScore = 0;
                    $courseActionScore = getCourseActionScore($courseActionItemOne->id, $userID);
                    $courseActionScore = round($courseActionScore->finalgrade, 2);
                    //read action score standard
                    $courseActionScoreStandard = ($courseStandardArray[$courseActionItemOne->id]['score'])
                                                    ?$courseStandardArray[$courseActionItemOne->id]['score']
                                                    :"0";
                    $courseActionScorePass = ( $courseActionScore >= $courseActionScoreStandard ) ?true :false;
                } else {
                    //compare score
                    $courseActionScorePass = true;
                }
            break;
            case 'hotpot': //hot potato test
                //compare mod time action mod id
                if ($courseStandardArray[$courseActionItemOne->id]['timeCheck']) {
                    //compare time
                    $courseActionTime = 0; //setup action time
                    $hotpotID = $courseActionItemOne->iteminstance;
                    if ( $hotpotTime = caculateHotPotTime($userID, $courseID, $hotpotID) ) {
                        $courseActionTime = $hotpotTime;
                    }
                    //read action time standard
                    $courseActionTimeStandard = ($courseStandardArray[$courseActionItemOne->id]['time'])
                                                    ?$courseStandardArray[$courseActionItemOne->id]['time']
                                                    :"0";
                    $courseActionTimePass = ( ($courseActionTime >= $courseActionTimeStandard) ?true :false );
                } else {
                    //compare time
                    $courseActionTimePass = true;
                }
                //compare mod score action mod id
                if ($courseStandardArray[$courseActionItemOne->id]['scoreCheck']) {
                    //compare score
                    $courseActionScore = 0;
                    $courseActionScore = getCourseActionScore($courseActionItemOne->id, $userID);
                    $courseActionScore = round($courseActionScore->finalgrade, 2);
                    //read action score standard
                    $courseActionScoreStandard = ($courseStandardArray[$courseActionItemOne->id]['score'])
                                                    ?$courseStandardArray[$courseActionItemOne->id]['score']
                                                    :"0";
                    $courseActionScorePass = ( $courseActionScore >= $courseActionScoreStandard ) ?true :false;
                } else {
                    //compare score
                    $courseActionScorePass = true;
                }
            break;
            case 'scorm':
                //compare mod time action mod id
                if ($courseStandardArray[$courseActionItemOne->id]['timeCheck']) {
                    //compare time
                    $courseActionTime = 0; //setup action time
                    $scormID = $courseActionItemOne->iteminstance;
                    if ( $scormTime = caculateScormTime($userID, $courseID, $scormID) ) {
                        $courseActionTime = $scormTime;
                    }
                    //read action time standard
                    $courseActionTimeStandard = ($courseStandardArray[$courseActionItemOne->id]['time'])
                                                    ?$courseStandardArray[$courseActionItemOne->id]['time']
                                                    :"0";
                    $courseActionTimePass = ( ($courseActionTime >= $courseActionTimeStandard) ?true :false );
                } else {
                    //compare time
                    $courseActionTimePass = true;
                }
                //compare mod score action mod id
                if ($courseStandardArray[$courseActionItemOne->id]['scoreCheck']) {
                    //compare score
                    $courseActionScorePass = true;
                } else {
                    //compare score
                    $courseActionScorePass = true;
                }
            break;
        }
        
        $courseUserArray[$courseActionItemOne->id] = array('timePass'=>$courseActionTimePass,
                                                            'scorePass'=>$courseActionScorePass
                                                    );
        //user pass array edit
        if ( !($courseUserArray[$courseActionItemOne->id]['timePass'] AND
                $courseUserArray[$courseActionItemOne->id]['scorePass']) ) {
                return false;;
        }
    }
    return true;
}


?>
