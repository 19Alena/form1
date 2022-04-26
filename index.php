<?php
//require('./function.php');
class Helpers
{
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
                    $err=Helpers::errorsPage(4,$err);
                }
            }
            else {$err=Helpers::errorsPage(3,$err);}
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

}
$err=[];
class Template
{
    function view(string $content_view, string $template_view, $data, $err1 = null): void
    {
        include 'view/' . $template_view;
    }

    public function get_data($arr)
    {
        return $arr;//55
    }

    public function generate_text($tpl)
    {
        //date
        $nowDate = date('jS \of F Y');
        $endDate = Helpers::dateEnd();
        $dateArray = ['EXECDATE' => $nowDate, 'ENDDATE' => $endDate];
        $newTpl = Helpers::ParseStr('%', $tpl, $_SESSION);
        $newTpl = Helpers::ParseStr('#', $newTpl, $dateArray);
        $newTpl = str_replace(array("\r\n", "\r", "\n"), '<br>', $newTpl);
        return $newTpl;
    }

    public function get_error($err1)
    {
        return $err1;
    }

}
class Main
{
    const TEMPLATE = './template.tpl';

    private array $postData;
    private array $sessionData;

    function __constructor(array $post, array $sessions)
    {
        $this->postData = $post;
        $this->sessionData = $sessions;
    }
    public function readfile():string
    {
        if (!file_exists(self::TEMPLATE)) {
            //   $err = errorsPage(1, $err);
        }
        $tpl = "";
        if (count($err) === 0)
            $tpl = file_get_contents(self::TEMPLATE);
        return $tpl;
    }
    public function calculateTemplateVariables(string $template)
    {
        $arr=Helpers::defineVar($template);
        return $arr;
    }
    public function isSessionHasVariables( $cVar, $mVar)
    {
        /* if (count($mVar) === 0) { return true;}
         else return false;*/
        return true;
    }
    public function loadTemplateVariablesRequed($arrVar,$err)
    {
        $rr = new Template();
        //$data=$rr->get_data($arrVar);
        //$err1=$rr->get_error($err);
        $rr->view('form.php', 'template_view.php', $arrVar, $err);
        echo "6666";
    }
    public function loadTemplateWithVariables($cVar,$mVar,$tpl)
    {
        $rr = new Template();
        $text = $rr->generate_text($tpl);
        $rr->view('text.php', 'template_view.php', $text);

    }
    public function main()
    {
//Helpers::defineVar();
        $err=[];
        $template = $this->readFile();
        $calculateVariables = (array)$this->calculateTemplateVariables($template);
print_r($calculateVariables);
print_r($this->sessionData);
        //  echo "555";
        var_dump($this->isSessionHasVariables($calculateVariables, $err)) ;
           if ($this->isSessionHasVariables($calculateVariables, $this->sessionData)) { echo "555";
              // return $this->loadTemplateVariablesRequed($calculateVariables,$err);
           } else { echo "444";
            //   return $this->loadTemplateWithVariables($calculateVariables, $this->sessionData, $template);

           }
        return $calculateVariables[0];

    }
    function old()
    {
        if (!file_exists('./template.tpl')) {
            $err = Helpers::errorsPage(1, $err);
        }
        $tpl = "";
        if (count($err) === 0)

            $tpl = file_get_contents('./template.tpl');

        session_start();
        $arrVar = Helpers::defineVar($tpl);
        if ($arrVar === null) $err = Helpers::errorsPage(2, $err);

        if ((isset($_POST['destroy'])) && ($_POST['destroy'] === 'destroy')) {
            foreach ($arrVar as $value) {
                unset($_SESSION[$value]);
            }
            session_destroy();
        } else {
            if ((count($_SESSION) === 0) && (count($_POST) > 0)) {

                $err = Helpers::validatePost($arrVar, $err);
                if (count($err) === 0) {
                    foreach ($arrVar as $value) {

                        $_SESSION[$value] = $_POST[$value];
                    }
                }
            }
        }

//form
        if (count($_SESSION) === 0) {
            $rr = new Template();
            //$data=$rr->get_data($arrVar);
            //$err1=$rr->get_error($err);
            $rr->view('form.php', 'template_view.php', $arrVar, $err);
        }

//output

        if (count($_SESSION) > 0) {
            $rr = new Template();
            $text = $rr->generate_text($tpl);
            $rr->view('text.php', 'template_view.php', $text);

        }
    }}
session_start();
echo (new Main($_POST, $_SESSION))->main();
echo "gggg";


