<?php
namespace FwBD\Validate;

interface iValidate
{
    public function getSatus();
    public function setMessagesw(array $listParams);
    public function getMessagesw();

    public function setModel(string $model);
    public function setListMessages(array $listMessages);
    public function validateData(array $rulesModel, array $datas);

    public function Selected($rule, $field, $value);
    public function Requerid($field, $value);
    public function Mail($field, $value);
    public function Unique($field, $value, $model);
    public function Min($field, $value, $limit);
    public function Max($field, $value, $limit);
    public function RequeridFile($field, $value);
    public function typesFile($field, $limit);
    public function MaxFileSize($field, $limit);

}