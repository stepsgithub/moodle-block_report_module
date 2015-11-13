<?php

define('COURSE_MAX_LOG_DISPLAY', 150);          // days
define('COURSE_MAX_LOGS_PER_PAGE', 1000);       // records
define('COURSE_LIVELOG_REFRESH', 60);           // Seconds
define('COURSE_MAX_RECENT_PERIOD', 172800);     // Two days, in seconds
define('COURSE_MAX_SUMMARIES_PER_PAGE', 10);    // courses
define('COURSE_MAX_COURSES_PER_DROPDOWN',1000); //  max courses in log dropdown before switching to optional
define('COURSE_MAX_USERS_PER_DROPDOWN',1000);   //  max users in log dropdown before switching to optional
define('FRONTPAGENEWS',           '0');
define('FRONTPAGECOURSELIST',     '1');
define('FRONTPAGECATEGORYNAMES',  '2');
define('FRONTPAGETOPICONLY',      '3');
define('FRONTPAGECATEGORYCOMBO',  '4');
if (!defined('FRONTPAGECOURSELIMIT')) {
    define('FRONTPAGECOURSELIMIT',    200);     // maximum number of courses displayed on the frontpage
}
define('EXCELROWS', 65535);
define('FIRSTUSEDEXCELROW', 3);

define('MOD_CLASS_ACTIVITY', 0);
define('MOD_CLASS_RESOURCE', 1);

if (!defined('MAX_MODINFO_CACHE_SIZE')) { 
    define('MAX_MODINFO_CACHE_SIZE', 10);
}

//student report
function print_studentreport_ods($studentreport_ods) {

    global $CFG;
    require_once("$CFG->libdir/odslib.class.php");

    //release studnetreport ods data
    $userID = $studentreport_ods->userID;
    $userName = $studentreport_ods->userName;
    $userDescription = $studentreport_ods->userDescription;
    $userEmail = $studentreport_ods->userEmail;
    $courseIDDataArray = $studentreport_ods->courseIDDataArray;
    $actionIDNameArray = $studentreport_ods->actionIDNameArray;
    $courseUserArray = $studentreport_ods->courseUserArray;
    $userPassArray = $studentreport_ods->userPassArray;

    $actionCount = countActionItem($courseUserArray);

    $odsForm = array();
    foreach($courseUserArray as $courseID => $actionInfo) {
        $odsForm[$courseID] = array();
        //$odsForm[$courseID]['courseName'] = $courseIDNameArray[$courseID];
        $i = 1;
        foreach($actionInfo as $actionID => $actionItem) {
            $odsForm[$courseID]['actionName_'.$i] = $actionIDNameArray[$actionID];
            $odsForm[$courseID]['actionTime_'.$i] = ($actionItem['time'])?$actionItem['time']:'';
            $odsForm[$courseID]['actionScore_'.$i] = ($actionItem['score'])?$actionItem['score']:'';
            $i ++;
        }
        $odsForm[$courseID]['pass'] = $userPassArray[$courseID];
    }

    $ldcache = array();
    $tt = getdate(time());
    $today = mktime (0, 0, 0, $tt["mon"], $tt["mday"], $tt["year"]);

    $filename = 'student_reports_'.userdate(time(),get_string('backupnameformat', 'langconfig'),99,false);
    $filename .= '.ods';

    $workbook = new MoodleODSWorkbook('-');
    $workbook->send($filename);

    $worksheet = array();
    $headers = array();
    $headers[] = '課程類別';
    $headers[] = '課程名稱';
    for ($i = 1; $i <= $actionCount; $i++) {
        $headers[] = '活動名稱'.$i;
        $headers[] = '活動時數'.$i;
        $headers[] = '活動分數'.$i;
    }
    $headers[] = '通過與否';

    // Creating worksheets
    $sheettitle = 'student_report'; //紀錄1-1
    $worksheet = & $workbook->add_worksheet($sheettitle);
    $worksheet->set_column(1, 1, 60);
    $worksheet->write_string(0, 0, $userName);
    $worksheet->write_string(1, 0, $userDescription);
    $worksheet->write_string(2, 0, $userEmail);
    
    $col = 0;
    $row = 5;
    foreach ($headers as $item) {
        $worksheet->write($row-1,$col,$item,'');
        $col++;
    }
    
    $myxls =& $worksheet;
    foreach ($odsForm as $courseID => $actionInfo) {
        $myxls->write_string($row, 0, $courseIDDataArray[$courseID]['categoryName']);
        $myxls->write_string($row, 1, $courseIDDataArray[$courseID]['courseName']);
        for ($i = 0; $i < $actionCount; $i++) {
            $myxls->write_string($row, ($i*3)+2, $odsForm[$courseID]['actionName_'.($i+1)]);
            $myxls->write_string($row, ($i*3)+3, $odsForm[$courseID]['actionTime_'.($i+1)]);
            $myxls->write_string($row, ($i*3)+4, $odsForm[$courseID]['actionScore_'.($i+1)]);
        }
        $myxls->write_string($row, ($actionCount*3)+2,
                             ($userPassArray[$courseID])?'通過':'不通過');

        $row++;
    }

    $workbook->close();
    return true;
}

//count first user action item
function countActionItem($courseUserArray) {
    foreach( $courseUserArray as $actionInfo) {
        return count($actionInfo);
    }
}

//course reprot
function print_coursereport_ods($coursereport_ods) {  

    global $CFG;
    require_once("$CFG->libdir/odslib.class.php");

    //release coursereport ods data
    $courseID = $coursereport_ods->courseID;
    $courseName = $coursereport_ods->courseName;
    $categoryName = $coursereport_ods->categoryName;
    $userIDDataArray = $coursereport_ods->userIDDataArray;
    $actionIDNameArray = $coursereport_ods->actionIDNameArray;
    $courseUserArray = $coursereport_ods->courseUserArray;
    $userPassArray = $coursereport_ods->userPassArray;

    $tt = getdate(time());
    $today = mktime (0, 0, 0, $tt["mon"], $tt["mday"], $tt["year"]);
    $filename = 'course_reports_'.userdate(time(),get_string('backupnameformat', 'langconfig'),99,false);
    $filename .= '.ods';
    $workbook = new MoodleODSWorkbook('-');
    $workbook->send($filename);
    $worksheet = array();
    
    // Creating worksheets
    $sheettitle = 'course_report'; //紀錄1-1
    $worksheet = & $workbook->add_worksheet($sheettitle);
    $worksheet->set_column(1, 1, 60);
    $worksheet->write_string(0, 0, $categoryName);
    $worksheet->write_string(1, 0, $courseName);
    
    $col = 0;
    $row = 4;
    $headerArray = processCourseReportHeader($courseUserArray, $actionIDNameArray);
    foreach ($headerArray as $item) {
        $worksheet->write($row-1,$col,$item,'');
        $col++;
    }

    $myxls =& $worksheet;
    foreach ($courseUserArray as $userID => $actionInfo) {
        $col = 0;
        $myxls->write_string($row, $col, $userIDDataArray[$userID]['name']);
        $col++;
        $myxls->write_string($row, $col, $userIDDataArray[$userID]['description']);
        $col++;
        $myxls->write_string($row, $col, $userIDDataArray[$userID]['email']);
        $col++;
        foreach($actionInfo as $actionID => $actionContent) {
            if($actionContent['timeEnable']) {
                $myxls->write_string($row, $col, $actionContent['time']);
                $col++;
            }
            if($actionContent['scoreEnable']) {
                $myxls->write_string($row, $col, $actionContent['score']);
                $col++;
            }
            
            if ( ($actionContent['timePass'] AND $actionContent['scorePass']) ) {
                $myxls->write_string($row, $col, '通過');
                $col++;
            } else {
                $myxls->write_string($row, $col, '不通過');
                $col++;
            }
            
        }
        $myxls->write_string($row, $col, ($userPassArray[$userID])?'通過':'不通過');
        $col++;
        
        $row++;
    }

    $workbook->close();
    return true;
}

function processCourseReportHeader($courseUserArray, $actionIDNameArray) {
    foreach( $courseUserArray as $userid => $actionInfo) {
        $headerArray = array();
        $headerArray[] = '學生名稱';
        $headerArray[] = '學生簡介';
        $headerArray[] = '電子信箱';
        foreach($actionInfo as $actionID => $actionContent) {
            if($actionContent['timeEnable']) {
                $headerArray[] = $actionIDNameArray[$actionID] . "- 時間";
            }
            if($actionContent['scoreEnable']) {
                $headerArray[] = $actionIDNameArray[$actionID] . "- 分數";
            }
            $headerArray[] = $actionIDNameArray[$actionID] . "- 通過";
        }
        $headerArray[] = '課程通過';
        return $headerArray;
    }
}

function processStudentReportHeader($courseUserArray, $actionIDNameArray) {
    foreach( $courseUserArray as $userid => $actionInfo) {
        $headerArray = array();
        $headerArray[] = '課程類別';
        $headerArray[] = '課程名稱';
        foreach($actionInfo as $actionID => $actionContent) {
            if($actionContent['timeEnable']) {
                $headerArray[] = $actionIDNameArray[$actionID] . "- 時間";
            }
            if($actionContent['scoreEnable']) {
                $headerArray[] = $actionIDNameArray[$actionID] . "- 分數";
            }
            $headerArray[] = $actionIDNameArray[$actionID] . "- 通過";
        }
        $headerArray[] = '課程通過';
        return $headerArray;
    }
}

?>
