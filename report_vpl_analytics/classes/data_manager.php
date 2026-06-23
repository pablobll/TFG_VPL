<?php
namespace report_vpl_analytics;

defined('MOODLE_INTERNAL') || die();

class data_manager {

    public static function get_vpl_data() {
        $file = __DIR__ . '/../data/vpl.csv';
        return self::read_csv($file);
    }

    public static function get_submissions_data() {
        $file = __DIR__ . '/../data/vpl_submissions.csv';
        return self::read_csv($file);
    }

    private static function read_csv($filepath) {
        $data = [];
        if (($handle = fopen($filepath, "r")) !== false) {
            $headers = fgetcsv($handle, 1000, ",");
            while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                $data[] = array_combine($headers, $row);
            }
            fclose($handle);
        }
        return $data;
    }

    public static function get_dashboard_data() {
        $vpls = self::get_vpl_data();
        $submissions = self::get_submissions_data();

        $vpl_course_map = [];
        $vpl_name_map = [];
        foreach ($vpls as $v) {
            $vpl_course_map[$v['id']] = $v['course'];
            $vpl_name_map[$v['id']] = $v['name'];
        }

        $enriched_submissions = [];
        $unique_courses = [];
        $unique_groups = [];
        $unique_users = [];

        foreach ($submissions as $sub) {
            $vpl_id = isset($sub['vpl']) ? $sub['vpl'] : null;
            if (!$vpl_id) continue;

            $course = isset($vpl_course_map[$vpl_id]) ? $vpl_course_map[$vpl_id] : 'Desconocido';
            $vpl_name = isset($vpl_name_map[$vpl_id]) ? $vpl_name_map[$vpl_id] : 'VPL ' . $vpl_id;
            $group = isset($sub['groupid']) && $sub['groupid'] !== '' ? (int)$sub['groupid'] : 0; 
            $user = $sub['userid'];

            if($course !== 'Desconocido') {
                $unique_courses[$course] = true;
            }
            $unique_groups[$group] = true;
            $unique_users[$user] = true;

            $enriched_submissions[] = [
                'id' => $sub['id'],
                'vpl' => $vpl_id,
                'vpl_name' => $vpl_name,
                'course' => $course,
                'userid' => $user,
                'groupid' => $group,
                'datesubmitted' => (int)$sub['datesubmitted'],
                'grade' => (float)$sub['grade'],
                'run_count' => (int)$sub['run_count'],
                'debug_count' => (int)$sub['debug_count'],
                'nevaluations' => (int)$sub['nevaluations']
            ];
        }

        $courses = array_keys($unique_courses); sort($courses);
        $groups = array_keys($unique_groups); sort($groups);
        $users = array_keys($unique_users); sort($users);

        return [
            'courses' => $courses,
            'groups' => $groups,
            'users' => $users,
            'submissions' => $enriched_submissions
        ];
    }
}
