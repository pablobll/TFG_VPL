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
        
        list($in_sql, $in_params) = $DB->get_in_or_equal($vpl_ids);
        $sql = "SELECT id, vpl, userid, datesubmitted, grade, groupid, nevaluations, run_count, debug_count 
                FROM {vpl_submissions} 
                WHERE vpl $in_sql";
        $submissions = $DB->get_records_sql($sql, $in_params);

        $enriched_submissions = [];
        $unique_users = [];

        $course_groups = groups_get_all_groups($courseid);
        $groups_data = [];
        $user_groups = [];
        if ($course_groups) {
            foreach ($course_groups as $g) {
                $groups_data[] = ['id' => $g->id, 'name' => $g->name];
                $members = groups_get_members($g->id, 'u.id');
                if ($members) {
                    foreach ($members as $u) {
                        if (!isset($user_groups[$u->id])) {
                            $user_groups[$u->id] = [];
                        }
                        $user_groups[$u->id][] = (int)$g->id;
                    }
                }
            }
        }
        
        $has_no_group = false;

        foreach ($submissions as $sub) {
            $vpl_id = $sub->vpl;
            $vpl_name = $vpls[$vpl_id]->name;
            $user = $sub->userid;

            $u_groups = isset($user_groups[$user]) ? $user_groups[$user] : [];
            if (empty($u_groups)) {
                $u_groups = [0];
                $has_no_group = true;
            }

            $unique_users[$user] = true;

            $grade = null;
            if ($sub->grade !== null && $sub->grade !== '') {
                $grade = (float)$sub->grade;
            }

            $enriched_submissions[] = [
                'id' => $sub->id,
                'vpl' => $vpl_id,
                'vpl_name' => $vpl_name,
                'course' => (int)$courseid,
                'userid' => (int)$user,
                'user_groups' => $u_groups,
                'datesubmitted' => (int)$sub->datesubmitted,
                'grade' => $grade,
                'run_count' => (int)$sub->run_count,
                'debug_count' => (int)$sub->debug_count,
                'nevaluations' => (int)$sub->nevaluations
            ];
        }

        if ($has_no_group) {
            $groups_data[] = ['id' => 0, 'name' => 'Sin Grupo'];
        }

        $users = array_keys($unique_users); sort($users);
        
        $context = \context_course::instance($courseid);
        $total_students = count_enrolled_users($context, 'mod/vpl:submit');

        return [
            'courses' => [(int)$courseid],
            'groups' => $groups_data,
            'users' => $users,
            'total_students' => $total_students,
            'submissions' => $enriched_submissions
        ];
    }
}
