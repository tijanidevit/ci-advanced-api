<?php

    class Semester_model extends CI_Model{
        private $table = 'tbl_semester_projects';
        public function __construct()
        {
            parent::__construct();
            $this->load->database();
        }

        public function create_semester_project($data)
        {
            return $this->db->insert($this->table, $data);
        }
        
        public function fetch_all_semester_projects()
        {
            $this->db->select('project.*, student.name as student_name, student.email as student_email')->from($this->table . " as project");
            $this->db->join('tbl_students as student', 'student.id = project.student_id');
            $this->db->order_by('id', 'desc');
            return $this->db->get()->result();
        }
        
        public function fetch_student_projects($student_id)
        {
            $this->db->select('project.*, student.name as student_name, student.email as student_email')->from($this->table . " as project");
            $this->db->join('tbl_students as student', 'student.id = project.student_id');
            $this->db->where(['student_id' => $student_id]);
            $this->db->order_by('id', 'desc');
            return $this->db->get()->result();
        }
        
        public function delete_student_projects($student_id)
        {
            $this->db->where(['student_id' => $student_id]);
            return $this->db->delete($this->table);
        }
        
        public function delete_semester_project($id)
        {
            $this->db->where('id', $id);
            return $this->db->delete($this->table);
        }
    }