<?php
    class Notes 
    {
        private $conn = NULL;
        private $table_name = "notes";
        public $id;
        public $id_student;
        public $id_subject;
        public $note;
        public function __construct($db)
        {
            $this->conn = $db;
        }
        function readnote() 
        {
            $query = "SELECT id, id_student, id_subject, note FROM ".$this->table_name ;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        }
        function readnoteleve($log) 
        {
            $query = "SELECT id, id_student, id_subject, note FROM ".$this->table_name." WHERE id_student =".$log ;
            //echo $query;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        }
    }