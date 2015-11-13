<?php 
function integrate_log($user_id, $course_id, $scorm_id) {
    global $DB;

    $selector="";
    $Joins=array();
    $course_module=array();

    $course_module = $DB->get_records("course_modules", array("instance"=>$scorm_id, "course"=>$course_id));

    $Joins_cm = array();
    foreach($course_module as $cm)
        $Joins_cm[] = "l.cmid = '$cm->id'";
    $Joins[] = '(' . implode(' OR ', $Joins_cm) . ')';		

    $Joins[] = "l.course = $course_id";
    $Joins[] = "l.userid = $user_id";
    $Joins[] = "l.module = 'scorm'";
    $selector = implode(' AND ', $Joins);
    $order = ' l.time ASC ';
    $limitfrom = '';
    $limitnum = 50000;
    $totalcount = '';

    $courseUserActionLogs = get_logs($selector, null, $order, $limitfrom, $limitnum, $totalcount);

    $courseUserLogData = array();
    foreach ($courseUserActionLogs as $courseUserLog) {
        $courseUserLogData[$courseUserLog->id] =
        array(//'userid' => $courseUserLog->userid,
            'time'   => $courseUserLog->time,
        );
    }

    return $courseUserLogData;
}
