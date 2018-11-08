<?php

namespace FwBD\Model;

use FwBD\DBConect\DBConect;

class BaseModel implements iBaseModel
{
	protected $conPDO;
    // protected $table;
    protected $preFix;
    protected $fillable;
    protected $rules;

    protected $select='*', $where='1', $val, $join, $groupBy, $orderBy,$limit, $distinct, $total=0;
    protected $result=[], $totalPage = 10, $isPaginate = False;

	/*public function __construct()
    {
        $this->conPDO = Conexao::getCon('db/database.db');
    }*/

    public function __construct(\PDO $conn)
    {
        $this->conPDO = $conn;
    }


    ## HELPERS ###############################################################
    public function setTable(string $table)
    {
        $this->table = $table;
        return $this;
    }

    public function setRules(array $rules=[])
    {
        $this->rules = $rules;
    }

    public function getRules()
    {
        return $this->rules;
    }

    private function setPrefixFields($fields='')
    {
        if (!empty($fields) AND !empty($this->preFix))
            return $this->preFix.$fields;

        if (!empty($this->preFix))
            return $this->preFix.'id' . ', ' . implode(', ', $this->fillable);

        return $allFields = implode(', ', $this->fillable);

    }

    public function setPrefix($prefix)
    {
        return $this->preFix = $prefix;
    }
    public function getPrefix()
    {
        return $this->preFix;
    }

    private function getParams($params)
    {

        if (!empty($params))
            $data = (is_numeric($params)) ?
                    $this->setPrefixFields('id').'  = :params' :
                    $this->setPrefixFields('uri').' = :params' ;

        return $data;
    }

    private function prepareData($data)
    {

        if ( isset($this->val) && count($this->val) > 0 ) {
            foreach ($this->val as $k => $rs) {
                $data->bindValue(":{$k}", $rs);
            }
        }

        return $data;
    }

    private function cleanString($string)
    {
        if ( is_numeric($string) )
        return $string;

        $a = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜüÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ"!@#$%&*()_-+={[}]/?;:.,\\\'<>°ºª';
        $b = 'aaaaaaaceeeeiiiidnoooooouuuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr                                 ';
        $string = utf8_decode($string);
        $string = strtr($string, utf8_decode($a), $b);
        $string = strip_tags(trim($string));
        $string = str_replace(" ","_",$string);
        $string = str_replace(array("-----","----","---","--"),"",$string);
        return strtolower(utf8_encode($string));
    }


    ## EXEC SCRIPT SQL ###########################################################
    /**
     * exec: Realiza conexão com BD e executa uma consulta direto no banco;
     * @param string $execSql: string sql da consulta
     * @return
     */
    public static function exec(string $execSql)
    {
        try {
            $pdo = \FwBD\DBConect\DBConect::getCon(DB_CONFIG);
            return $pdo->exec($execSql);
            # return $pdo->lastInsertId();
        } catch (PDOException $e) {
            die('Error execute sql in BaseModel::exec() ').$e->getMessage();
        }

        /*$pdo = \FwBD\DBConect\DBConect::getCon(DB_DRIVE,DB_CONFIG);
        return $pdo->exec($execSql);
        # return $pdo->lastInsertId();*/
    }

    ## CRUD ######################################################################
    public function insert(array $datas, $rtn='')
    {
        $fields = implode(", ", array_values($this->fillable));
        $keys   = ':' . implode(", :", array_values($this->fillable));

        $sqlIns = "INSERT INTO {$this->table} ({$fields}) VALUES ({$keys})";
        // pp($sqlIns,1);

        $qryIns = $this->conPDO->prepare($sqlIns);

        foreach ($this->fillable as $value) {
            $qryIns->bindValue(":{$value}", $datas[$value]);
        }

        if ( $rtn ) {
            $qryIns->execute();
            return $this->conPDO->lastInsertId();
        } else
            return $qryIns->execute();
    }

    public function update($id, array $datas)
    {
        $fields = array();
        foreach ($this->fillable as $value) {
            foreach ($datas as $k => $v) {
                if ( $value == $k )
                    $fields[] = "$value = :$value";
            }
        }
        $fields = implode(', ', $fields);

        $condicao = $this->setPrefixFields('id').'  = :params';
        $sqlUpdate = "UPDATE {$this->table} SET {$fields} WHERE {$condicao} ";
        // pp('>> '.$sqlUpdate,1);

        $qryUpdate = $this->conPDO->prepare($sqlUpdate);

        foreach ($this->fillable as $value) {
            foreach ($datas as $k => $v) {
                if ( $value == $k )
                    $qryUpdate->bindValue(":{$value}", $datas[$value]);
            }
        }

        $qryUpdate->bindValue(":params", $id, \PDO::PARAM_INT);
        // pp('>> '.$id,1);

        return $qryUpdate->execute();
    }

    public function delete($params)
    {
        $condicao = $this->getParams($params);

        $qryDelete = "DELETE FROM {$this->table} WHERE {$condicao} ";
        // pp($qryDelete .'  '. $params,1);

        $delete = $this->conPDO->prepare($qryDelete);
        $delete->bindValue(':params', $params);

        return $delete->execute();
    }

    ## SELECT #####################################################################
    public function find($params='', $field='')
    {

        if ( !is_numeric($params) || !empty($field) )
            $fieldx = $field;
        else
            $fieldx = 'id';

        $condicao = $this->setPrefixFields($fieldx).'  = :params';

        $sqlFind = "SELECT {$this->select} FROM {$this->table} WHERE {$condicao} ";
        // pp($sqlFind);

        $data = $this->conPDO->prepare($sqlFind);
        $data->bindValue(':params', $params);

        $data->execute();

        return $this->result = $data->fetch();

    }

    public function select($select='*')
    {
        $this->select = $select;
        return $this;
    }


    /**
     * Function Where => Create AND WHERE is query
     * Ex: ->where('post_title', 'bruno', 'like');
     * @param string $collum : nome da coluna
     * @param string $val : valor da query ('Bruno')
     * @param string $op : operador da query (=, like ou etc)
     */
    public function where($collum, $val='1', $op='=')
    {

        $valKey = $this->cleanString($collum).uniqid(date('YmdHms'));

        $this->where .= " AND {$collum} {$op} :{$valKey}";

        $this->val[$valKey] = $val;

        return $this;

    }

    public function orWhere($collum, $val='1', $op='=')
    {

        // $valKey = $collum.uniqid(date('YmdHms'));
        $valKey = $this->cleanString($collum).uniqid(date('YmdHms'));
        $this->where .= " OR {$collum} {$op} :{$valKey}";
        $this->val[$valKey] = $val;

        return $this;

    }

    /**
     * Function Where => Create JOIN Tables
     * Ex: ->join('tb_gb', 'tb_gb.post_id = tb_post.post_id');
     * Dicas: https://www.devmedia.com.br/sql-join-entenda-como-funciona-o-retorno-dos-dados/31006
     * @param string $tbjoin : Name Table Join ()
     * @param string $whereJoin : Where join ()
     * @param string $tpJoin : operador join (INNER, LEFT, RIGHT ou FULL OUTER)
     */
    public function join($tbjoin, $whereJoin, $tpJoin='INNER')
    {
        $this->join .= " {$tpJoin} JOIN {$tbjoin} ON {$whereJoin} ";

        return $this;
    }

    /**
     * Function GroupBy => agrupa linhas baseado em semelhanças entre elas
     * Ex: ->groupBy('tb_gb.post_id');
     * @param string $collum : Name Collum Table (post_title)
     */
    public function groupBy($collum)
    {
        $this->groupBy = "GROUP BY {$collum} ";

        return $this;
    }

    /**
     * Function OrderBy => Create Ordenação dos dados
     * Ex: ->orderBy('tb_post.post_id', 'ASC');
     * @param string $whereOrder : Name Table Join ()
     * @param string $ordem : Where join ()
     */
    public function orderBy($whereOrder, $ordem = 'DESC')
    {
        $this->orderBy = "ORDER BY {$whereOrder} {$ordem}";

        return $this;
    }

    /**
     * Function LIMIT => limita a quantidade  dos resultados
     * Ex: ->limit('10');
     * @param string $limit : valor do limite
     */
    public function limit($limit = '10')
    {
        $this->limit = "LIMIT {$limit} ";

        return $this;
    }

    /**
     * Function DISTINCT => limita a quantidade de resultados
     * Ex: ->distinct();
     */
    public function distinct()
    {
        $this->distinct = "DISTINCT";

        return $this;
    }

    public function all()
    {

        $sqlAll ="SELECT {$this->distinct} {$this->select} FROM {$this->table} {$this->join} WHERE {$this->where} {$this->groupBy} {$this->orderBy} {$this->limit}";

        // pp($sqlAll);
        // pp($this->val);

        $data = $this->conPDO->prepare($sqlAll);

        $data = $this->prepareData($data);

        $data->execute();

        $this->result = $data->fetchAll();

        $this->total = count($this->result);

        $data->closeCursor();

        return $this;

    }


    # Metodos de PAGINAÇÂO #######################################################
    public function paginate($limit, $page)
    {

        $this->isPaginate = true;
        $this->totalPage = $limit;
        $offset = ( (int) $page * $limit - $limit);
        # $offset = ( (int) $page * $limit - $limit) +1; #  [error curso]
        $this->limit = " LIMIT {$limit} OFFSET {$offset} ";

        return $this->all();

    }


    /**
     * Function getTotal() => Retorna total de registro
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Function getResult() => Retorna total registro da paginação
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Function totalResults() => Retorna total do banco
     */
    public function totalResults()
    {
        $sqlFull ="SELECT {$this->distinct} {$this->select} FROM {$this->table} {$this->join} WHERE {$this->where} {$this->groupBy} ";

        $data = $this->conPDO->prepare($sqlFull);
        $data = $this->prepareData($data);

        $data->execute();

        return count($data->fetchAll());
    }


    /**
     * Function getPaginator() => Retorna Bool, existe ou não a paginação
     */
    public function getPaginator()
    {
        return $this->isPaginate;
    }

    /**
     * Function getPages() => Retorna os buttons(html) de paginação na página
     */
    public function getPages($url, $page, array $filters = [])
    {

        if ( !$this->isPaginate )
            return '';

        // pp($filters);
        if ( count($filters) > 0 )
            $filters = '/'.implode('/', $filters);
        else
            $filters = '';

        // pp('> '.$filters);

        $totalResults = $this->totalResults();
        $results = ceil($totalResults / $this->totalPage);

        $Previous = (int) $page - 1;
        $Next = (int) $page + 1;

        $paginate = '<ul class="paginator">';

        if ($page > 1) {
            $paginate .= "<li> <a href='{$url}{$Previous}{$filters}'   ><</a> </li>";
        }
        for ($i=1; $i <= $results ; $i++) {
            if ($page == $i )
                $paginate .= "<li> <strong>{$i}</strong> </li>";
            else
                $paginate .= "<li> <a href='{$url}{$i}{$filters}'>{$i}</a> </li>";
        }
        if ($page < $i-1) {
            $paginate .= "  <li> <a href='{$url}{$Next}{$filters}'>></a> </li>";
        }
        $paginate .= '</ul>';

        return $paginate;

    }

    public function getPagesBootStrap($url, $page, array $filters = [])
    {
        if ( !$this->isPaginate )
            return '';

        if ( count($filters) > 0 )
            $filters = implode('/', $filters);
        else
            $filters = '';


        $totalResults = $this->totalResults();
        $results = ceil($totalResults / $this->totalPage);

        $Previous = $page - 1;
        $Next = $page + 1;

        $paginate = '<nav aria-label="Page navigation">
                        <ul class="pagination">';

        if ($page > 1) {
            $paginate .= '<li>
                            <a href="'.$url.$Previous.$filters.'" aria-label="Previous">
                                <span aria-hidden="true">&laquo</span>
                            </a>
                        </li>';
        }
        for ($i=1; $i <= $results ; $i++) {
            if ($page == $i )
                $paginate .= "<li class='disabled'> <a href='{$url}{$i}{$filters}'><strong>{$i}</strong></a> </li>";
            else
                $paginate .= "<li> <a href='{$url}{$i}{$filters}'>{$i}</a> </li>";
        }

        if ($page < $i-1) {
            $paginate .= '  <li>
                                <a href="'.$url.$Next.$filters.'" aria-label="Next">
                                    <span aria-hidden="true">&raquo</span>
                                </a>
                            </li>';
        }
        $paginate .= '</ul>
                    </nav>';

        /*$this->paginator = $paginate;
        return $this->paginator;*/
        return $paginate;
    }



}