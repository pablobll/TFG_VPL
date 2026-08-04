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
        foreach ($vpls as $vpl) {
            $vpls_data[] = ['id' => $vpl->id, 'name' => $vpl->name];
        }
        
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
                $members = groups_get_members($g->id, 'u.id');
                $member_count = $members ? count($members) : 0;
                $groups_data[] = ['id' => $g->id, 'name' => $g->name, 'member_count' => $member_count];
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

        $context = \context_course::instance($courseid);
        $enrolled_users_obj = get_enrolled_users($context, 'mod/vpl:submit', 0, 'u.id');
        $teachers_obj = get_enrolled_users($context, 'mod/vpl:grade', 0, 'u.id');
        $teacher_ids = [];
        if ($teachers_obj) {
            foreach ($teachers_obj as $t) {
                $teacher_ids[(int)$t->id] = true;
            }
        }

        $all_enrolled_users = [];
        $enrolled_map = [];
        if ($enrolled_users_obj) {
            foreach ($enrolled_users_obj as $eu) {
                $uid = (int)$eu->id;
                if (!isset($teacher_ids[$uid])) {
                    $all_enrolled_users[] = $uid;
                    $enrolled_map[$uid] = true;
                }
            }
        }
        sort($all_enrolled_users);
        $total_students = count($all_enrolled_users);

        $no_group_count = 0;
        foreach ($all_enrolled_users as $uid) {
            if (empty($user_groups[$uid])) {
                $no_group_count++;
            }
        }
        if ($no_group_count > 0) {
            $groups_data[] = ['id' => 0, 'name' => 'Sin Grupo', 'member_count' => $no_group_count];
        }

        $final_submissions = [];
        foreach ($enriched_submissions as $sub) {
            if (isset($enrolled_map[$sub['userid']])) {
                $final_submissions[] = $sub;
            }
        }

        return [
            'courses' => [(int)$courseid],
            'vpls' => $vpls_data,
            'groups' => $groups_data,
            'users' => $all_enrolled_users,
            'user_groups_map' => $user_groups,
            'total_students' => $total_students,
            'submissions' => $final_submissions
        ];
    }
}
