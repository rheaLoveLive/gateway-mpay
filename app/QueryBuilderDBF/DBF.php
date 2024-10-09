<?php

namespace App\QueryBuilderDBF;

use DateTime;

## biggest flaw dari dbf querybuilder buatan saya ini yaitu sql injection :) yamaap

class DBF
{
    protected static $queryBuilder;

    public static function table($table, $dsn, $username = "", $password = "")
    {
        self::$queryBuilder = new QueryBuilder($table, $dsn, $username, $password);
        return self::$queryBuilder;
    }
}

class QueryBuilder
{
    // db setupnya
    protected $dbfTable;
    protected $con;

    // beberapa var buat query 
    protected $selects = '*';
    protected $raw;
    protected $joins = [];
    protected $wheres = [];
    protected $limit = 0;
    protected $orderby = [];
    protected $groupby = [];

    public function __construct($dbfTable, $dsn, $username = "", $password = "")
    {
        $this->resetVar();
        $this->con = odbc_connect($dsn, $username, $password); // koneksi.php ini mah :v
        $this->dbfTable = $dbfTable;
    }

    public function raw($query = null)
    {

        if (!empty($query)) {
            $this->raw = $query;
        } else {
            $this->raw = null;
        }

        return $this;
    }

    public function select(...$fields)
    {
        // return $fields;
        if (is_array($fields)) {
            $this->selects = implode(', ', $fields);
        } else {
            $this->selects = '*';
        }

        return $this;
    }

    public function where($field, $operator = '=', $value = null)
    {
        if (is_null($value)) {
            $value = $operator;
            $operator = '=';
        }

        $type = gettype($value);

        if ($type == "integer") {
            $where = "$field $operator $value";
        }

        if ($type == "string" && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            $where = "$field $operator '$value'";
        }

        if ($type == "object" && $value instanceof DateTime) {
            $formattedDate = $value->format('Y-m-d H:i:s');
            $where = "$field $operator #$formattedDate#";
        }

        if ($type == "string" && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            $where = "$field $operator #$value#";
        }

        if ($type == "string" && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
            $where = "$field $operator #$value#";
        }

        $this->wheres[] = $where;
        return $this;
    }


    public function join($table, $field, $joinedField, $typeJoin = "INNER")
    {
        $this->joins[] = "$typeJoin JOIN $table ON $field = $joinedField";
        return $this;
    }

    public function leftJoin($table, $field, $joinedField)
    {
        $this->join($table, $field, $joinedField, "LEFT");
        return $this;
    }
    public function rightJoin($table, $field, $joinedField)
    {
        $this->join($table, $field, $joinedField, "RIGHT");
        return $this;
    }

    public function orderBy($field, $order)
    {
        $arr = ['ASC', 'DESC'];
        $direction = strtoupper($order);
        if (!in_array($direction, $arr)) {
            $direction = 'ASC';
        }

        $this->orderby[] = "$direction $field";
        return $this;
    }

    public function groupBy(...$fields)
    {
        foreach ($fields as $field) {
            $this->groupby[] = $field;
            return $this;
        }
    }

    public function create($data = [])
    {
        $fields = implode(', ', array_keys($data));
        $values = [];

        foreach ($data as $val) {
            $type = gettype($val);

            if ($type == "integer" || $type == "double") {
                $values[] = "$val";
            } elseif ($type == "string" && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $val) && !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $val)) {
                $values[] = "'$val'";
            } elseif ($type == "object" && $val instanceof DateTime) {
                $formattedDate = $val->format('Y-m-d H:i:s');
                $values[] = "#$formattedDate#";
            } elseif ($type == "string" && preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
                $values[] = "#$val#";
            } elseif ($type == "string" && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $val)) {
                $values[] = "#$val#";
            } else {
                $values[] = null;
            }
        }

        // dd($values);


        $stringVal = implode(', ', $values);


        $mess = null;
        $data = [];

        $query = "INSERT INTO $this->dbfTable ($fields) VALUES ($stringVal)";


        // dd($query);

        if ($this->con) {
            $result = odbc_exec($this->con, $query);

            if ($result) {
                $mess = "CREATED";
            } else {
                $mess = "Query gagal: " . odbc_errormsg($this->con);
            }
            odbc_close($this->con);
        } else {
            $mess = "Koneksi gagal: " . odbc_errormsg();
        }

        return $mess;
    }
    public function update($data = [])
    {
        $mess = null;
        $updatedField = [];
        foreach ($data as $key => $val) {

            $type = gettype($val);

            $values = "";
            if ($type == "integer" || $type == "double") {
                $values = "$val";
            } elseif ($type == "string" && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
                $values = "'$val'";
            } elseif ($type == "object" && $val instanceof DateTime) {
                $formattedDate = $val->format('Y-m-d H:i:s');
                $values = "#$formattedDate#";
            } elseif ($type == "string" && preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
                $values = "#$val#";
            } elseif ($type == "string" && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $val)) {
                $values = "#$val#";
            } else {
                $values = null;
            }

            $updatedField[] = "$key = $values";
        }

        $query = "UPDATE $this->dbfTable SET " . implode(', ', $updatedField);

        if (!empty($this->wheres)) {
            $query .= " WHERE " . implode(" AND ", $this->wheres);
        }

        if ($this->con) {

            $result = odbc_exec($this->con, $query);

            if ($result) {
                $mess = "UPDATED";
            } else {
                $mess = "Query gagal: " . odbc_errormsg($this->con);
            }
            odbc_close($this->con);
        } else {
            $mess = "Koneksi gagal: " . odbc_errormsg();
        }

        return $mess;
    }
    public function delete()
    {
        $mess = null;
        $query = "DELETE FROM $this->dbfTable";

        if (!empty($this->wheres)) {
            $query .= " WHERE " . implode(" AND ", $this->wheres);
        }

        if ($this->con) {
            $result = odbc_exec($this->con, $query);

            if ($result) {
                $mess = "DELETED";
            } else {
                $mess = "Query gagal: " . odbc_errormsg($this->con);
            }
            odbc_close($this->con);
        } else {
            $mess = "Koneksi gagal: " . odbc_errormsg();
        }

        return $mess;
    }

    public function get()
    {

        $mess = null;
        $data = [];


        if ($this->con) {

            $result = odbc_exec($this->con, $this->buildQuery());

            if ($result) {
                while ($a = odbc_fetch_array($result)) {
                    $data[] = $a;
                }
            } else {
                $mess = "Query gagal: " . odbc_errormsg($this->con);
            }
            odbc_close($this->con);
        } else {
            $mess = "Koneksi gagal: " . odbc_errormsg();
        }

        if (!empty($mess)) {
            return $mess;
        }

        if ($this->limit > 0) {
            return array_slice($data, 0, $this->limit);
        } else {
            return $data;
        }
    }

    public function first()
    {
        $this->limit(1);
        $res = $this->get();
        return count($res) > 0 ? $res[0] : [];
    }

    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    protected function buildQuery()
    {
        $query = "SELECT $this->selects FROM $this->dbfTable";


        if (!empty($this->raw)) {
            $query = $this->raw;
        }

        if (!empty($this->joins)) {
            $query .= " " . implode(', ', $this->joins);
        }

        // if (!empty($this->wheres)) {
        //     $query .= " WHERE " . implode(" AND ", $this->wheres);
        // }

        if (!empty($this->wheres)) {
            if (stripos($query, 'WHERE') !== false) {
                $query .= " AND " . implode(" AND ", $this->wheres);
            } else {
                $query .= " WHERE " . implode(" AND ", $this->wheres);
            }
        }

        if (!empty($this->orderby)) {
            $query .= " ORDER BY " . implode(', ', $this->orderby);
        }

        if (!empty($this->groupby)) {
            $query .= ' GROUP BY ' . implode(', ', $this->groupby);
        }

        // if (preg_match('/[&<>"\'%]/', $query)) {
        //     // Dekode karakter khusus menjadi bentuk aslinya
        //     $query = htmlspecialchars_decode($query);
        // }
        // dd($query);
        return $query;
    }

    protected function resetVar()
    {
        $this->selects = "*";
        $this->raw = null;
        $this->joins = [];
        $this->wheres = [];
        $this->groupby = [];
        $this->orderby = [];
        $this->limit = 0;
        $this->con = null;
    }
}
