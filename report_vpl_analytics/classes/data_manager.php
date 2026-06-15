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

    public static function get_student_metrics() {
        $submissions = self::get_submissions_data();
        $metrics = [];

        foreach ($submissions as $sub) {
            $userid = $sub['userid'];
            if (!isset($metrics[$userid])) {
                $metrics[$userid] = [
                    'userid' => $userid,
                    'total_submissions' => 0,
                    'total_runs' => 0,
                    'total_debugs' => 0,
                    'average_grade' => 0,
                    'sum_grades' => 0
                ];
            }
            
            $metrics[$userid]['total_submissions'] += 1;
            $metrics[$userid]['total_runs'] += (int)$sub['run_count'];
            $metrics[$userid]['total_debugs'] += (int)$sub['debug_count'];
            $metrics[$userid]['sum_grades'] += (float)$sub['grade'];
        }

        foreach ($metrics as &$m) {
            if ($m['total_submissions'] > 0) {
                $m['average_grade'] = round($m['sum_grades'] / $m['total_submissions'], 2);
            }
        }

        return $metrics;
    }
}
