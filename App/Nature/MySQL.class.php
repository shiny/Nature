<?php
    namespace Nature;
    class MySQL{
        private $dsn;
        private $sth;
        private $dbh;
        private $user;
        private $charset;
        private $password;
        
        function __setup($configure=[]){
            $this->dsn = $configure['dsn'];
            $this->user = $configure['username'];
            $this->password = $configure['password'];
            $this->charset = $configure['charset'];
            $this->connect();
        }
        function connect(){
            if(!$this->dbh){
                $this->dbh = new \PDO($this->dsn, $this->user, $this->password);
                $this->dbh->query('SET NAMES '.$this->charset);
            }
        }
        function watchException($execute_state){
            if(!$execute_state){
                throw new MySQLException($this->sth->errorInfo()[2], $this->sth->errorCode());
            }
        }
        function fetchAll($sql, $parameters=[]){
            $result = [];
            $this->sth = $this->dbh->prepare($sql);
            $this->watchException($this->sth->execute($parameters));
            while($result[] = $this->sth->fetch(\PDO::FETCH_ASSOC)){ }
            array_pop($result);
            return $result;
        }
        function fetchColumnAll($sql, $parameters=[], $position=0){
            $result = [];
            $this->sth = $this->dbh->prepare($sql);
            $this->watchException($this->sth->execute($parameters));
            while($result[] = $this->sth->fetch(\PDO::FETCH_COLUMN, $position)){ }
            array_pop($result);
            return $result;
        }
        function exists($sql, $parameters=[]){
            $data = $this->fetch($sql, $parameters);
            return !empty($data);
        }
        function query($sql, $parameters=[]){
            $this->sth = $this->dbh->prepare($sql);
            $this->watchException($this->sth->execute($parameters));
            return $this->sth->rowCount();
        }
        function fetch($sql, $parameters=[], $type=\PDO::FETCH_ASSOC){
            $this->sth = $this->dbh->prepare($sql);
             $this->watchException($this->sth->execute($parameters));
            return $this->sth->fetch($type);
        }
        function fetchColumn($sql, $parameters=[], $position=0){
            $this->sth = $this->dbh->prepare($sql);
            $this->watchException($this->sth->execute($parameters));
            return $this->sth->fetch(\PDO::FETCH_COLUMN, $position);
        }
        function insert($table, $parameters=[]){
            $sql = "INSERT INTO `$table`";
            $fields = [];
            $placeholder = [];
            foreach ( $parameters as $field=>$value){
                $fields[] = $field;
                $placeholder[] = ':'.$field;
            }
            $sql .= '('.implode(",", $fields).') VALUES ('.implode(",", $placeholder).')';
            
            $this->sth = $this->dbh->prepare($sql);
            $this->watchException($this->sth->execute($parameters));
            $id = $this->dbh->lastInsertId();
            return $id;
        }
    }
    class MySQLException extends \Exception { }