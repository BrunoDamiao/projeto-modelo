<?php
namespace FwBD\Model;

interface iBaseModel
{
	
    public static function exec(string $execSql);

    public function insert(array $datas);
    public function update($id, array $datas);
    public function delete($params);

    public function find($params='');
    public function select($select='*');

    public function where($collum, $val='1', $op='=');
    public function orWhere($collum, $val='1', $op='=');
    public function join($tbjoin, $whereJoin, $op='ON');

    public function orderBy($whereOrder, $ordem = 'DESC');
    public function limit($limit = '10');
    public function distinct();

    public function all();
}