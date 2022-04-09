<?php

    class Branch_model extends CI_Model{
        private $table = 'tbl_branches';
        public function __construct()
        {
            parent::__construct();
            $this->load->database();
        }

        public function create_branch($data)
        {
            return $this->db->insert($this->table, $data);
        }
        
        public function fetch_branch($key)
        {
            $this->db->select('*')->where(['name' => $key])->or_where(['id' => $key])->order_by('id', 'desc');
            return $this->db->get($this->table)->row_object();
        }
        
        public function fetch_all_branches()
        {
            $this->db->select('*')->order_by('id', 'desc');
            return $this->db->get($this->table)->result();
        }
        
        public function delete_branch($id)
        {
            $this->db->where('id', $id);
            return $this->db->delete($this->table);
        }
    }