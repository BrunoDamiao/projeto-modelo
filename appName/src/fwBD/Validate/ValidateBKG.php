<?php
namespace OsHome\Validate;

// use OsHome\Validate\iValidate;
use OsHome\Validate\ValidateTrait;
use OsHome\Model\BaseModel;

/**
* TIPOS de Validações desta Class
* String = requerid | email | unique:User | min:2 | max:10
* File = requerid-file | type-file:image/png, image/jpg, aplication/pdf | size-file:2
*/
class Validate //implements iValidate
{
    use ValidateTrait;

    public $status;
    public $messages = '';
    public $groupMsg = [];
    private $model;
    // private $prefix;

    public function __construct()
    {
        $this->groupMsg = [
            'requerid' => 'O campo #field é obrigatório!',
            'mail' => 'O #field #value, está no formato inválido. Favor informe um e-mail válido!',
            'unique' => 'O #field #value, já se encontra registrado no sistema. Favor informe outro #field!',
            'min' => 'O tamanho mínimo do #field deve ser de #limit caracters!',
            'max' => 'O tamanho máximo do #field deve ser de #limit caracters!',
            'requerid-file' => 'O campo #field é obrigatório!',
            'types-file' => 'O tipo #value do campo #field não é valido, os aceitos são: #limit.',
            'max-file-size' => 'O arquivo #field possui o tamanho de #value<b>MB</b>, o tamnaho máximo permitido é #limit<b>MB</b>.',
        ];
    }

    public function validateData( BaseModel $model, array $datas)
    {

        $this->model = $model;

        pp($this->model);
        pp($this->model->getRules(),1);

        foreach ($model->getRules() as $field => $rules) {

            $cleanRules = str_replace(' ', '', $rules);
            // $cleanDatas = $datas[$field];

            if ( isset($datas[$field]) ){
                $cleanDatas = trim(filter_var($datas[$field], FILTER_SANITIZE_STRING));
            }else{
                $cleanDatas = $_FILES[$field];
            }

            $listRules = explode('|', $cleanRules);

            // pp($listRules,1);

            foreach ($listRules as $rule) {
                $this->Selected($rule, $field, $cleanDatas);
            }

        }

    }

    # Funções GETS e SETS;
    public function getStatus()
    {
        return $this->status;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function setMessages(array $listParams)
    {
        $rule  = strtolower( (!empty($listParams[0]))? $listParams[0] : null );
        $fieldPrefix = strtolower( (!empty($listParams[1]))? $listParams[1] : null );
        $field = str_replace($this->model->getPrefix(), '', $fieldPrefix);
        $value = strtolower( (!empty($listParams[2]))? $listParams[2] : null );
        $limit = strtolower( (!empty($listParams[3]))? $listParams[3] : null );

        foreach ($this->groupMsg as $key => $messages):
            if ( $rule == $key ) {
                $messages = str_replace('#field', "<b>$field</b>", $messages);
                $messages = str_replace('#value', "<b>$value</b>", $messages);
                $messages = str_replace('#limit', "<b>$limit</b>", $messages);

                $this->messages .= $messages . '<br>';
            }

        endforeach;
    }

    public function setListMessages(array $listMessages)
    {
        foreach ($listMessages as $rule => $message) {
            $this->groupMsg[$rule] = $message;
        }
    }

    # Funções Auxiliares;
    public function Selected($rule, $field, $value)
    {
        $listRule = explode(':', $rule);
        switch ( $listRule[0] ) {
            case 'requerid': $this->Requerid($field, $value); break;
            case 'email':    $this->Mail($field, $value); break;
            case 'unique':   $this->Unique($field, $value); break;
            // case 'unique':   $this->Unique($listRule[1], $field, $value); break;
            case 'min':      $this->Min($field, $value, $listRule[1]); break;
            case 'max':      $this->Max($field, $value, $listRule[1]); break;
            case 'required-file': $this->RequeridFile($field, $value); break;
            case 'types-file':    $this->typesFile($field, $listRule[1]); break;
            case 'max-file-size': $this->MaxFileSize($field, $listRule[1]); break;
        }
    }

    public function Requerid($field, $value)
    {
        if (!empty($value) && $value !='')
            return true;

        $this->status = true;
        $this->setMessages( ['Requerid', $field, $value] );
    }

    public function Mail($field, $value)
    {
        if ( !empty($value) && $value !='' && filter_var($value, FILTER_VALIDATE_EMAIL ) == true ){
            return true;

        }elseif( !empty($value) && $value !='' ){
            $this->status = true;
            $this->setMessages( ['Mail', $field, $value] );
        }
    }

    public function Unique($fields, $value)
    {
        // $rs = $this->getUniqueDB($model, $fields, $value);
        $rs = $this->model
            ->where("$fields", "%{$value}%", 'LIKE')
            ->all()
            ->getResult();

        if ( empty($rs) )
            return true;

        $this->status = true;
        $this->setMessages( ['Unique', $fields, $value] );
    }

    public function Min($field, $value, $limit)
    {
        if (!empty($value) && strstr($value,"@") ) {
            $rs = explode('@', $value);
            $value = $rs[0];
        }

        if ( !empty($value) && $value != '' && strlen($value) < $limit ) {
            $this->status = TRUE;
            $this->setMessages( ['Min', $field, $value, $limit] );
        }else
            return true;

    }

    public function Max($field, $value, $limit)
    {
        if (!empty($value) && strstr($value,"@") ) {
            $rs = explode('@', $value);
            $value = $rs[0];
        }

        if ( ($value != '') && strlen($value) > $limit ) {
            $this->status = TRUE;
            $this->setMessages( ['Max', $field, $value, $limit] );
        }else
            return true;
            // $this->status = FALSE;
            // $this->messages = " email - ok <br>";
    }

    public function RequeridFile($field, $value)
    {
        if ( isset($_FILES[$field]['name']) && $_FILES[$field]['name'] != '' )
            return true;

        $this->status = true;
        $this->setMessages( ['requerid-file', $field, $value['name']] );
    }

    public function typesFile($field, $limit)
    {
        $file = ( isset($_FILES[$field]) )? $_FILES[$field] : '';
        $type = ( isset($_FILES[$field]['tmp_name']) && $_FILES[$field]['tmp_name'] != '' )? mime_content_type($_FILES[$field]['tmp_name']) : '' ;

        $types = explode(',', $limit);
        $value = $type;

        if ( $_FILES[$field]['name'] == '' || in_array($type, $types) )
            return true;

        $this->status = true;
        $this->setMessages( ['types-file', $field, $value, $limit] );

    }

    public function MaxFileSize($field, $limit)
    {
        $file = $_FILES[$field];
        $bitTotKb = (int) ($file['size'] / 1024);
        $kbTobit = (float) $limit * 1024;

        if ( $_FILES[$field]['name'] == '' || $bitTotKb <= $kbTobit )
            return true;

        $this->status = true;
        $this->setMessages( ['max-file-size', $field, $bitTotKb, $kbTobit] );

    }

}