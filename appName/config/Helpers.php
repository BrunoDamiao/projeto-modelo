<?php
/**
 * File de Helpers do FwBD
 */

function dd($val,$die='')
{
    echo "<pre>";
    if (empty($die)) {
        var_dump($val);
    }else{
        var_dump($val);
        exit();
    }
    echo "</pre>";
}
function pp($val,$die='')
{
    echo "<pre>";
    if (empty($die)) {
        print_r($val);
    }else{
        print_r($val);
        exit();
    }
    echo "</pre>";
}

function cleanString($string)
{

    if ( is_numeric($string) )
        return $string;

    $a = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜüÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ"!@#$%&*()_-+={[}]/?;:.,\\\'<>°ºª';
    $b = 'aaaaaaaceeeeiiiidnoooooouuuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr                                 ';
    $string = utf8_decode($string);
    $string = strtr($string, utf8_decode($a), $b);
    $string = strip_tags(trim($string));
    $string = str_replace(" ","-",$string);
    $string = str_replace(array("-----","----","---","--"),"-",$string);
    return strtolower(utf8_encode($string));
}

function addDateTimeSession($time='')
{
    // $dataAtual = '2018-03-02 12:56:00';
    $dataAtual = date('Y-m-d H:i:s');
    $ssTimeEnd = $_SESSION['Auth']['session_timeEnd'];

    $timeEndAdd = new \DateTime($ssTimeEnd);
    $timeEnd = new \DateTime($ssTimeEnd);
        $min = ((60*10) < EXPIRE_SESSION_AUTH)?
                 60*10 : (EXPIRE_SESSION_AUTH / 2);

        $timeEnd->sub(new DateInterval('PT'.$min.'S'));
        $dataSessao = $timeEnd->format('Y-m-d H:i:s');

    // echo '<br> time atual: '.$dataAtual;
    // echo '<br> >>> time sessao: '.$dataSessao;

    $dAtual  = \DateTime::createFromFormat ('Y-m-d H:i:s', $dataAtual);
    $dSessao = \DateTime::createFromFormat ('Y-m-d H:i:s', $dataSessao);
    $dEnd    = \DateTime::createFromFormat ('Y-m-d H:i:s', $ssTimeEnd);
    // echo '<br> time end: '.$dEnd->format('Y-m-d H:i:s');

    if ( $dAtual >= $dSessao && $dAtual <= $dEnd ) {

        $minx = !empty($time)? $time : EXPIRE_SESSION_AUTH;
        $timeEndAdd = new \DateTime($ssTimeEnd);
        $timeEndAdd->add(new DateInterval('PT'.$minx.'S'));

        // echo '<br> add time sessionEnd: '.$timeEndAdd->format('Y-m-d H:i:s');
        return $timeEndAdd->format('Y-m-d H:i:s');

    }

    return $ssTimeEnd;

}


function redirect($url)
{
    return header('Location: ' . $url);
}

function setDataInput(array $datas)
{
    foreach ($datas as $name => $value) {
        setMsgFlash($name, $value);
    }
}

function getDataInput($field)
{
    if ( hasMsgFlash($field) )
        return getMsgFlash($field);
    else
        return '';
}

function setMsgFlash($name, $valor)
{
    FwBD\Session\Session::setFlash($name, $valor);
}

function getMsgFlash($name)
{
    return FwBD\Session\Session::getFlash($name);
}

function hasMsgFlash($name)
{
    return FwBD\Session\Session::hasFlash($name);
}


function showMessageFlash($typeMsg)
{
    /*if (hasMsgFlash($typeMsg)) {
        echo '<div class="alert alert-'.$typeMsg.' hidden-time alert-dismissable fade in" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
            echo getMsgFlash($typeMsg);
        echo '</div>';
    }*/

    if (hasMsgFlash($typeMsg)) {
        echo '<div class="alert alert-'.$typeMsg.' alert-dismissible fade show" role="alert">';
            echo getMsgFlash($typeMsg);
            echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>';
    }

}


function showMessageFlashMat($typeMsg)
{
    if (hasMsgFlash($typeMsg)) {

        if ( $typeMsg === 'primary' )
            $alertConf = ['bg'=>'blue-grey', 'txt'=>'grey', 'bd'=>'#bdbdbd'];

        if ( $typeMsg === 'success' )
            $alertConf = ['bg'=>'green', 'txt'=>'green', 'bd'=>'green'];

        if ( $typeMsg === 'info' )
            $alertConf = ['bg'=>'blue', 'txt'=>'blue', 'bd'=>'#90caf9'];

        if ( $typeMsg === 'warning' )
            $alertConf = ['bg'=>'yellow', 'txt'=>'orange', 'bd'=>'#ffcc80'];

        if ( $typeMsg === 'danger' )
            $alertConf = ['bg'=>'red', 'txt'=>'red', 'bd'=>'#ef9a9a'];

        $corBg  = $alertConf['bg']." lighten-4 "; # lighten-5 darken-2
        $corTxt = $alertConf['txt']."-text text-darken-5 ";
        $corBd  = ' style="border: 1px solid '.$alertConf['bd'].' " ';

        echo '<div id="card-alert" class="card-panel '.$corBg.'" '.$corBd.' >';
            echo '<span class="'.$corTxt.' ">';
                echo getMsgFlash($typeMsg);
            echo '</span>';
        echo '</div>';

    }
}


function limitarTexto($string, $limit=100){
    $string = substr($string, 0, strrpos(substr($string, 0, $limit), ' ')) . '...';
    return $string;
}

function showThumb($thumb, $model='', $dirRaiz='uploads')
{

    // pp($thumb);
    if ( empty($thumb) )
        $pathThumb = PATH_AVATAR;
    else {
        $modelPath = ($model)? $model.'/' : '';
        $srcThumb = $dirRaiz . '/' . $modelPath . 'thumb/' . $thumb;

        if ( is_file($srcThumb) )
            $pathThumb = $srcThumb;
        else
            $pathThumb = PATH_AVATAR;
    }

    return $pathThumb;
}

function showImage($img, $model='', $dirRaiz='uploads')
{

    // pp($img);
    if ( empty($img) )
        $pathImg = PATH_AVATAR;
    else {
        $modelPath = ($model)? $model.'/' : '';
        $srcImg = $dirRaiz . '/' . $modelPath . $img;

        if ( is_file($srcImg) )
            $pathImg = $srcImg;
        else
            $pathImg = PATH_AVATAR;
    }

    return $pathImg;
}


function getDateTimePTbr($datetime, $strFormat = 'd-m-Y H:i:s')
{
    if (!empty($datetime))
        return date($strFormat, strtotime($datetime));
    else
        return 'not date time';
}

