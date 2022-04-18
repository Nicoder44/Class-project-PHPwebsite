<?php
    class Student 
    {
        private $conn = NULL;
        private $table_name = "students";
        public $id;
        public $firstname;
        public $lastname;
        public $login;
        public $pass;
        public function __construct($db)
        {
            $this->conn = $db;
        }
        function create()
        {
            $query = "INSERT INTO ".$this->table_name." SET
            firstname=:firstname, lastname=:lastname,
            login=:login, pass=SHA2(CONCAT(SHA1(:login),:pass), 512)";
            $stmt = $this->conn->prepare($query);
            $this->firstname = htmlspecialchars(strip_tags($this->firstname));
            $this->lastname = htmlspecialchars(strip_tags($this->lastname));
            $this->login = htmlspecialchars(strip_tags($this->login));
            $stmt->bindParam(":firstname", $this->firstname);
            $stmt->bindParam(":lastname", $this->lastname);
            $stmt->bindParam(":login", $this->login);
            $stmt->bindParam(":pass", $this->pass);
            if($stmt->execute())
            {
                return true;
            }
            return false;
        }
        function read() 
        {
            $query = "SELECT id, firstname, lastname, login, pass FROM ".$this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        }
    }
