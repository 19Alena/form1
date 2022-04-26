<?php

function errorsPage(int $n,array $error):array
{
    switch ($n)
    {
        case 1: $error[]="File template not exist";break;
        case 2: $error[]="File template error parameter";break;
        case 3: $error[]="Error form";break;
        case 4: $error[]="Error String fields length  must be 3 or more";break;


    }
    return $error;
}

function defineVar(string $tpl):?array
{
    if (substr_count($tpl,'%')%2!==0) return null;
    $arrStr=[];
    $newArr=[];
    if ($tpl==="") return $arrStr;
    $arrStr=explode(" ",$tpl);
    foreach($arrStr as $key => $value)
    {
        if (substr_count($value,'%')===0)
        {
            unset($arrStr[$key]);

        }
        elseif (substr_count($value,'%')>2)
        {
            $countp=0;
            for($i=0;$i<strlen($value);$i++)
            {
                if ($value[$i]==='%')  {$arrayN[]=$i;}
            }

            for($i=0;$i<count($arrayN);$i=$i+2)
            {
                $newArr[]=substr($value,$arrayN[$i],$arrayN[$i+1]-$arrayN[$i]+1);
            }
            unset($arrStr[$key]);
        }
    }
    $arrStr=array_merge($arrStr,$newArr);
    foreach($arrStr as $key=>$value)
    {
        $begin=strpos($arrStr[$key],'%');
        $end=strrpos($arrStr[$key],'%');
        $arrStr[$key]=substr($arrStr[$key],$begin+1,$end-$begin-1);

    }
    $arrStr=array_unique($arrStr);
    return $arrStr;
}

function validatePost(array $arr,$err):array
{
    foreach($arr as $value)
    {
        if (isset($_POST[$value])){
            if ((substr_count($value,"NUM")===0)&&(strlen($_POST[$value])<3))
            {
                $err=errorsPage(4,$err);
            }
        }
        else {$err=errorsPage(3,$err);}
    }
    return $err;
}
//function define end date
function dateEnd():string
{
    $endDate='ENDDATE';
    if (isset($_SESSION['MONTHNUM']))
    {
        $dateE=strtotime('+'.$_SESSION['MONTHNUM'].' MONTH', strtotime(date('Y-m-d H:i:s')));
        $endDate=date('jS \of F Y',$dateE);
    }
    return $endDate;
}
//function put date from massive to str
function ParseStr(string $selector,string $str,array $massive):string
{
    $arr=explode($selector,$str);

    foreach($arr as $key=>$value)
    {
        if (isset($massive[$value]))
        {
            $arr[$key]=$massive[$value];
        }
    }
    $str=implode($arr);
    return $str;
}
?>
