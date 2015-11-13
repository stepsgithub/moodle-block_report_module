<?php

    require_once('../../config.php');
    require_once($CFG->libdir.'/adminlib.php');
    require_once($CFG->dirroot.'/user/filters/lib.php');

    httpsrequired();
    require_login();
    if (isguestuser()) {
        die();
    }

    $context = get_context_instance(CONTEXT_SYSTEM);
    require_capability('moodle/site:viewreports', $context); // basic capability for listing of reports

    $delete       = optional_param('delete', 0, PARAM_INT);
    $confirm      = optional_param('confirm', '', PARAM_ALPHANUM);   //md5 confirmation hash
    $confirmuser  = optional_param('confirmuser', 0, PARAM_INT);
    $sort         = optional_param('sort', 'name', PARAM_ALPHA);
    $dir          = optional_param('dir', 'ASC', PARAM_ALPHA);
    $page         = optional_param('page', 0, PARAM_INT);
    $perpage      = optional_param('perpage', 30, PARAM_INT);        // how many per page
    $ru           = optional_param('ru', '2', PARAM_INT);            // show remote users
    $lu           = optional_param('lu', '2', PARAM_INT);            // show local users
    $acl          = optional_param('acl', '0', PARAM_INT);           // id of user to tweak mnet ACL (requires $access)


    //admin_externalpage_setup('editusers');

    $sitecontext = get_context_instance(CONTEXT_SYSTEM);
    $site = get_site();

    $stredit   = get_string('edit');
    $strdelete = get_string('delete');
    $strdeletecheck = get_string('deletecheck');
    $strshowallusers = get_string('showallusers');

    if (empty($CFG->loginhttps)) {
        $securewwwroot = $CFG->wwwroot;
    } else {
        $securewwwroot = str_replace('http:','https:',$CFG->wwwroot);
    }

$PAGE->set_context($context);

$PAGE->set_url('/blocks/report_module/selectstudent.php');
$PAGE->set_pagelayout('base');

$strreports = get_string('studnetReport', 'block_report_module');

$PAGE->set_title($strreports);
$PAGE->set_heading($strreports);

$PAGE->navbar->add(get_string('reportModuleTitle', 'block_report_module'));
$PAGE->navbar->add($strreports);

echo $OUTPUT->header();

    // create the user filter form
    $ufiltering = new user_filtering();

    // Carry on with the user listing

    $columns = array("firstname", "lastname", "email", "city", "country", "lastaccess");

    foreach ($columns as $column) {
        $string[$column] = get_string("$column");
        if ($sort != $column) {
            $columnicon = "";
            if ($column == "lastaccess") {
                $columndir = "DESC";
            } else {
                $columndir = "ASC";
            }
        } else {
            $columndir = $dir == "ASC" ? "DESC":"ASC";
            if ($column == "lastaccess") {
                $columnicon = $dir == "ASC" ? "up":"down";
            } else {
                $columnicon = $dir == "ASC" ? "down":"up";
            }
            $columnicon = " <img src=\"$CFG->pixpath/t/$columnicon.gif\" alt=\"\" />";

        }
        $$column = "<a href=\"selectstudent.php?sort=$column&amp;dir=$columndir\">".$string[$column]."</a>$columnicon";
    }

    if ($sort == "name") {
        $sort = "firstname";
    }

    list($extrasql, $params) = $ufiltering->get_sql_filter();
    $users = get_users_listing($sort, $dir, $page*$perpage, $perpage, '', '', '', $extrasql, $params, $context);
    $usercount = get_users(false);
    $usersearchcount = get_users(false, '', true, null, "", '', '', '', '', '*', $extrasql, $params);

    if ($extrasql !== '') {
        echo $OUTPUT->heading("$usersearchcount / $usercount ".get_string('users'));
        $usercount = $usersearchcount;
    } else {
        echo $OUTPUT->heading("$usercount ".get_string('users'));
    }

    $alphabet = explode(',', get_string('alphabet'));
    $strall = get_string('all');

    echo $OUTPUT->paging_bar($usercount, $page, $perpage,
            "selectstudent.php?sort=$sort&amp;dir=$dir&amp;perpage=$perpage&amp;");

    flush();

    if (!$users) {
        $match = array();
        echo $OUTPUT->heading(get_string('nousersfound'));

        $table = NULL;

    } else {

        $countries = get_string_manager()->get_list_of_countries();
        if (empty($mnethosts)) {
            $mnethosts = $DB->get_records('mnet_host', null, '', 'id', 'id,wwwroot,name');
        }

        foreach ($users as $key => $user) {
            if (!empty($user->country)) {
                $users[$key]->country = $countries[$user->country];
            }
        }
        if ($sort == "country") {  // Need to resort by full country name, not code
            foreach ($users as $user) {
                $susers[$user->id] = $user->country;
            }
            asort($susers);
            foreach ($susers as $key => $value) {
                $nusers[] = $users[$key];
            }
            $users = $nusers;
        }

        $mainadmin = get_admin();

        $override = new object();
        $override->firstname = 'firstname';
        $override->lastname = 'lastname';
        $fullnamelanguage = get_string('fullnamedisplay', '', $override);
        if (($CFG->fullnamedisplay == 'firstname lastname') or
            ($CFG->fullnamedisplay == 'firstname') or
            ($CFG->fullnamedisplay == 'language' and $fullnamelanguage == 'firstname lastname' )) {
            $fullnamedisplay = "$firstname / $lastname";
        } else { // ($CFG->fullnamedisplay == 'language' and $fullnamelanguage == 'lastname firstname') 
            $fullnamedisplay = "$lastname / $firstname";
        }
        $table = new html_table();
        $table->head = array ($fullnamedisplay, $email, $city, $country, $lastaccess, get_string('action', 'block_report_module'));
        $table->align = array ("left", "left", "left", "left", "left", "center");
        $table->width = "95%";
        foreach ($users as $user) {
            if ($user->username == 'guest') {
                continue; // do not dispaly dummy new user and guest here
            }
            //$viewreportbutton = "<a href=\"selectstudent.php?delete=$user->id&amp;sesskey=$USER->sesskey\">學員報表</a>";
            $viewreportbutton = "<a href=\"reportstudent/reportstudent.php?userid=$user->id \">".get_string('studentreport1', 'block_report_module')."</a>";

            // for remote users, shuffle columns around and display MNET stuff

            if ($user->lastaccess) {
                $strlastaccess = format_time(time() - $user->lastaccess);
            } else {
                $strlastaccess = get_string('never');
            }
            $fullname = fullname($user, true);

            $table->data[] = array ("$fullname",
                                "$user->email",
                                "$user->city",
                                "$user->country",
                                $strlastaccess,
                                $viewreportbutton);
        }
    }

    // add filters
    $ufiltering->display_add();
    $ufiltering->display_active();

    if (!empty($table)) {
        echo html_writer::table($table);
        echo $OUTPUT->paging_bar($usercount, $page, $perpage,
                         "selectstudent.php?sort=$sort&amp;dir=$dir&amp;perpage=$perpage&amp;");
    }

echo $OUTPUT->footer();
