<?php
namespace report_vpl_analytics;

defined('MOODLE_INTERNAL') || die();

class data_manager {

    public static function get_dashboard_data($courseid) {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/lib/grouplib.php');

        if (empty($courseid)) {
            return [
                'courses' => [],
                'groups' => [],
                'users' => [],
                'submissions' => []
            ];
        }

        $vpls = $DB->get_records('vpl', ['course' => $courseid]);
        if (empty($vpls)) {
            return [
                'courses' => [$courseid],
                'groups' => [],
                'users' => [],
                'submissions' => []
            ];
        }

        $vpl_ids = array_keys($vpls);
        $vpls_data = [];
        
        $modinfo = get_fast_modinfo($courseid);
        $now = time();

        foreach ($vpls as $vpl) {
            $section_name = 'General';
            $is_cm_group = false;
            $course_order = 99999;
            $cm_pos = 0;
            foreach ($modinfo->cms as $cm) {
                $cm_pos++;
                if ($cm->modname === 'vpl' && $cm->instance == $vpl->id) {
                    $sectioninfo = $modinfo->get_section_info($cm->sectionnum);
                    $section_name = $sectioninfo->name ?: get_string('section') . ' ' . $cm->sectionnum;
                    $is_cm_group = ($cm->groupmode > 0);
                    $course_order = $cm_pos;
                    break;
                }
            }

            $is_graded = ($vpl->grade > 0);
            $is_closed = ($vpl->duedate > 0 && $vpl->duedate < $now);
            $is_group = ($vpl->worktype > 0);

            $vpls_data[] = [
                'id' => $vpl->id, 
                'name' => $vpl->name,
                'section' => $section_name,
                'graded' => $is_graded,
                'closed' => $is_closed,
                'is_group' => $is_group,
                'duedate' => (int)$vpl->duedate,
                'course_order' => $course_order

            ];
        }
        
        list($in_sql, $in_params) = $DB->get_in_or_equal($vpl_ids);
        $sql = "SELECT id, vpl, userid, datesubmitted, grade, groupid, nevaluations, run_count, debug_count 
                FROM {vpl_submissions} 
                WHERE vpl $in_sql";
        $submissions = $DB->get_records_sql($sql, $in_params);

        $enriched_submissions = [];

        $context = \context_course::instance($courseid);
        $enrolled_users_obj = get_enrolled_users($context, 'mod/vpl:submit', 0, 'u.id, u.firstname, u.lastname');
        $teachers_obj = get_enrolled_users($context, 'mod/vpl:grade', 0, 'u.id');
        $teacher_ids = [];
        if ($teachers_obj) {
            foreach ($teachers_obj as $t) {
                $teacher_ids[(int)$t->id] = true;
            }
        }

        $all_enrolled_users = [];
        $enrolled_map = [];
        $user_names_map = [];
        if ($enrolled_users_obj) {
            foreach ($enrolled_users_obj as $eu) {
                $uid = (int)$eu->id;
                if (!isset($teacher_ids[$uid])) {
                    $all_enrolled_users[] = $uid;
                    $enrolled_map[$uid] = true;
                    $user_names_map[$uid] = fullname($eu);
                }
            }
        }
        sort($all_enrolled_users);
        $total_students = count($all_enrolled_users);

        $course_groups = groups_get_all_groups($courseid);
        $groups_data = [];
        $user_groups = [];
        if ($course_groups) {
            foreach ($course_groups as $g) {
                $members = groups_get_members($g->id, 'u.id');
                $student_count = 0;
                if ($members) {
                    foreach ($members as $u) {
                        if (isset($enrolled_map[$u->id])) {
                            $student_count++;
                            if (!isset($user_groups[$u->id])) {
                                $user_groups[$u->id] = [];
                            }
                            $user_groups[$u->id][] = (int)$g->id;
                        }
                    }
                }
                $groups_data[] = ['id' => $g->id, 'name' => $g->name, 'member_count' => $student_count];
            }
        }
        

        $final_submissions = [];
        foreach ($submissions as $sub) {
            $user = $sub->userid;
            if (!isset($enrolled_map[$user])) {
                continue;
            }

            $vpl_id = $sub->vpl;
            $vpl_name = $vpls[$vpl_id]->name;

            $u_groups = isset($user_groups[$user]) ? $user_groups[$user] : [];
            if (empty($u_groups)) {
                $u_groups = [0];
            }

            $grade = null;
            if ($sub->grade !== null && $sub->grade !== '') {
                $raw_grade = (float)$sub->grade;
                $max_grade = (float)$vpls[$vpl_id]->grade;
                if ($max_grade > 0) {
                    $grade = $raw_grade / $max_grade;
                } else {
                    $grade = $raw_grade;
                }
            }

            $final_submissions[] = [
                'id' => $sub->id,
                'vpl' => $vpl_id,
                'vpl_name' => $vpl_name,
                'course' => (int)$courseid,
                'userid' => (int)$user,
                'groupid' => (int)$sub->groupid,
                'user_groups' => $u_groups,
                'datesubmitted' => (int)$sub->datesubmitted,
                'grade' => $grade,
                'run_count' => (int)$sub->run_count,
                'debug_count' => (int)$sub->debug_count,
                'nevaluations' => (int)$sub->nevaluations
            ];
        }

        $no_group_count = 0;
        foreach ($all_enrolled_users as $uid) {
            if (empty($user_groups[$uid])) {
                $no_group_count++;
            }
        }
        if ($no_group_count > 0) {
            $groups_data[] = ['id' => 0, 'name' => get_string('label_no_group', 'report_vpl_analytics'), 'member_count' => $no_group_count];
        }

        return [
            'courses' => [(int)$courseid],
            'vpls' => $vpls_data,
            'groups' => $groups_data,
            'users' => $all_enrolled_users,
            'user_names_map' => $user_names_map,
            'user_groups_map' => $user_groups,
            'total_students' => $total_students,
            'submissions' => $final_submissions
        ];
    }
}
