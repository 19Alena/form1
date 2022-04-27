<?php

class Template
{
    private array $sessionData;

    public function __construct(array $sessions = [])
    {
        $this->sessionData = $sessions;
    }

    function view(string $content_view, string $template_view, $data): void
    {
        include 'view/' . $template_view;
    }

    public function dateEnd(): string
    {
        if (isset($this->sessionData['MONTHNUM'])) {
            $dateE = strtotime('+' . $this->sessionData['MONTHNUM'] . ' MONTH', strtotime(date('Y-m-d H:i:s')));
            $endDate = date('jS \of F Y', $dateE);
        }
        return $endDate;
    }

    public function parseStr(string $selector, string $str, array $arrayVar): string
    {
        foreach ($arrayVar as $key => $value) {
            $pattern = '/' . $selector . $key . $selector . '/';
            $str = preg_replace($pattern, $value, $str);
        }
        return $str;
    }

    public function generateText($tpl)
    {
//date
        $nowDate = date('jS \of F Y');
        $endDate = $this->dateEnd();
        $dateArray = ['EXECDATE' => $nowDate, 'ENDDATE' => $endDate];
        $newTpl = $this->parseStr('%', $tpl, $_SESSION);
        $newTpl = $this->parseStr('#', $newTpl, $dateArray);
        $newTpl = str_replace(array("\r\n", "\r", "\n"), '<br>', $newTpl);
        return $newTpl;
    }

}