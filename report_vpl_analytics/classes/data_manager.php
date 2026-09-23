<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * VPL Analytics Dashboard
 *
 * @package    report_vpl_analytics
 * @copyright  2024 Pablobll
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_vpl_analytics;

defined('MOODLE_INTERNAL') || die();

class data_manager {

    /**
     * Devuelve la estructura vacía estándar cuando no hay datos disponibles o el usuario no tiene permisos.
     *
     * @param int|null $courseid
     * @param bool $has_scales
     * @return array
     */
    private static function empty_payload($courseid, $has_scales = false) {
        return [
            'courses' => empty($courseid) ? [] : [$courseid],
            'groups' => [],
            'users' => [],
            'vpls' => [],
            'submissions' => [],
            'user_names_map' => [],
            'total_students' => 0,
            'has_scales_excluded' => $has_scales
        ];
    }

    /**
     * Extrae, limpia y empaqueta todos los datos de las actividades VPL de un curso.
     *
     * @param int $courseid ID del curso en Moodle.
     * @return array Estructura con VPLs, entregas, grupos y métricas.
     */
    public static function get_dashboard_data($courseid) {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/lib/grouplib.php');

        if (empty($courseid)) {
            return self::empty_payload($courseid);
        }

        /*
         * Obtención de todas las actividades VPL del curso.
         * Se construye el array $vpls_data que representa la metadata básica (nombre, fechas, si es grupal) de cada VPL.
         */
        $vpls = $DB->get_records('vpl', ['course' => $courseid]);
        if (empty($vpls)) {
            return self::empty_payload($courseid);
        }

        $modinfo = get_fast_modinfo($courseid);
        $vpl_cms = $modinfo->get_instances_of('vpl');

        $has_scales_excluded = false;
        foreach ($vpls as $key => $vpl) {
            if ((float)$vpl->grade < 0) {
                $has_scales_excluded = true;
                unset($vpls[$key]);
                continue;
            }
            if (!isset($vpl_cms[$vpl->id]) || !$vpl_cms[$vpl->id]->uservisible) {
                unset($vpls[$key]);
            }
        }

        $vpl_ids = array_keys($vpls);
        if (empty($vpl_ids)) {
            return self::empty_payload($courseid, $has_scales_excluded);
        }

        $vpls_data = [];
        
        $now = time();
        $cm_pos_map = [];
        $pos = 0;
        foreach ($modinfo->cms as $cm) {
            $pos++;
            if ($cm->modname === 'vpl') {
                $cm_pos_map[$cm->instance] = $pos;
            }
        }

        foreach ($vpls as $vpl) {
            $cm = $vpl_cms[$vpl->id];
            $sectioninfo = $modinfo->get_section_info($cm->sectionnum);
            $section_name = $sectioninfo->name ?: get_string('section') . ' ' . $cm->sectionnum;
            $is_cm_group = ($cm->groupmode > 0);
            $course_order = $cm_pos_map[$vpl->id] ?? 99999;

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
        
        /*
         * Extracción masiva de entregas (submissions) de los VPLs recuperados.
         * Se obtiene el conjunto bruto de entregas desde la BD para procesarlas en memoria.
         */
        list($in_sql, $in_params) = $DB->get_in_or_equal($vpl_ids);
        $sql = "SELECT id, vpl, userid, datesubmitted, grade, groupid, nevaluations, run_count, debug_count 
                FROM {vpl_submissions} 
                WHERE vpl $in_sql";
        $submissions = $DB->get_records_sql($sql, $in_params);

        /*
         * Identificación de usuarios matriculados (excluyendo profesores).
         * Se construyen estructuras clave devueltas en el JSON final:
         * - $all_enrolled_users: Array plano con los IDs de los estudiantes.
         * - $user_names_map: Diccionario (ID -> Nombre Completo) usado en el frontend.
         * - $total_students: Total de alumnos para cálculos de porcentajes en KPIs.
         */
        $context = \context_course::instance($courseid);
        $course = $DB->get_record('course', ['id' => $courseid]);
        $groupmode = groups_get_course_groupmode($course);
        $accessallgroups = has_capability('moodle/site:accessallgroups', $context);
        
        $allowed_group_ids = null;
        $allowed_users = [];
        if ($groupmode == SEPARATEGROUPS && !$accessallgroups) {
            global $USER;
            $my_groups = groups_get_all_groups($courseid, $USER->id);
            $allowed_group_ids = [];
            foreach ($my_groups as $g) {
                $allowed_group_ids[$g->id] = true;
                $members = groups_get_members($g->id, 'u.id');
                if ($members) {
                    foreach ($members as $u) {
                        $allowed_users[$u->id] = true;
                    }
                }
            }
            if (empty($allowed_group_ids)) {
                return self::empty_payload($courseid, $has_scales_excluded);
            }
        }

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
                    if ($allowed_group_ids !== null && !isset($allowed_users[$uid])) {
                        continue;
                    }
                    $all_enrolled_users[] = $uid;
                    $enrolled_map[$uid] = true;
                    $user_names_map[$uid] = fullname($eu);
                }
            }
        }
        sort($all_enrolled_users);
        $total_students = count($all_enrolled_users);

        /*
         * Mapeo de grupos de Moodle y asociación con los estudiantes.
         * Se genera el diccionario $user_groups que vincula cada userid con sus grupos, permitiendo filtros comparativos.
         */
        $course_groups = groups_get_all_groups($courseid);
        $groups_data = [];
        $user_groups = [];
        if ($course_groups) {
            foreach ($course_groups as $g) {
                if ($allowed_group_ids !== null && !isset($allowed_group_ids[$g->id])) {
                    continue;
                }
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
        

        /*
         * Limpieza y estructuración final de entregas válidas.
         * Se genera $final_submissions asociando cada entrega al alumno, su grupo 
         * y calculando métricas como 'first_submission' o 'procrastinator'.
         */
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
                    $grade = null;
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
            'submissions' => $final_submissions,
            'has_scales_excluded' => $has_scales_excluded
        ];
    }
}
