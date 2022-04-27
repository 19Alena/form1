<?php

class Template
{
    /**
     * @var array
     */
    private array $sessionData;

    /**
     * @param array $sessions
     */
    public function __construct(array $sessions = [])
    {
        $this->sessionData = $sessions;
    }

    /**
     * @param string $content_view
     * @param string $template_view
     * @param array $data
     * @param string $error
     * @return string
     */
    function viewTemplate(string $content_view, string $template_view, array $data, string $error = ""): string
    {
        ob_start();
        include 'view/' . $content_view;
        $content = ob_get_contents();
        ob_end_clean();

        ob_start();
        include 'view/' . $template_view;
        $buf = ob_get_contents();
        ob_end_clean();
        return $buf;
    }

    /**
     * @return string
     */
    public function dateEndDefine(): string
    {
        if (isset($this->sessionData['MONTHNUM'])) {
            $dateE = strtotime('+' . $this->sessionData['MONTHNUM'] . ' MONTH', strtotime(date('Y-m-d H:i:s')));
            $endDate = date('jS \of F Y', $dateE);
        }
        return $endDate;
    }

    /**
     * @param string $selector
     * @param string $str
     * @param array $arrayVar
     * @return string
     */
    public function parseTemplate(string $selector, string $str, array $arrayVar): string
    {
        foreach ($arrayVar as $key => $value) {
            $pattern = '/' . $selector . $key . $selector . '/';
            $str = preg_replace($pattern, $value, $str);
        }
        return $str;
    }

    /**
     * @param string $tpl
     * @return string
     */
    public function generateTextFromTemplate(string $tpl): string
    {
        //date
        $nowDate = date('jS \of F Y');
        $endDate = $this->dateEndDefine();
        $dateArray = ['EXECDATE' => $nowDate, 'ENDDATE' => $endDate];
        $newTpl = $this->parseTemplate('%', $tpl, $_SESSION);
        $newTpl = $this->parseTemplate('#', $newTpl, $dateArray);
        $newTpl = str_replace(array("\r\n", "\r", "\n"), '<br>', $newTpl);
        return $newTpl;
    }

}

Class FileErrorException extends Exception
{

}
class Main
{

    const TEMPLATE = './view/template.tpl';
    /**
     * @var array
     */
    private array $postData;
    private array $sessionData;

    public function __construct(array $post = [], array $sessions = [])
    {
        $this->postData = $post;
        $this->sessionData = $sessions;
    }


    /**
     * @return string
     * @throws ErrorException
     */
    public function readfile()
    {
        if (!file_exists(self::TEMPLATE)) {
            throw new ErrorException('File Error');
        }

        return file_get_contents(self::TEMPLATE);
    }

    /**
     * @param string $template
     * @return array
     */
    public function parseTemplateVariables(string $template): array
    {
        preg_match_all("/\%(.*)\%/", $template, $array);
        return $array[1];
    }

    /**
     * @param array $parseVariables
     * @param array $sessionData
     * @return bool\
     */
    public function isSessionHasVariables(array $parseVariables = [], array $sessionData = []): bool
    {
        $parseVariablesCount = count($parseVariables);
        $count = 0;
        foreach ($parseVariables as $sessionItem) {
            $count += isset($sessionData[$sessionItem]) ? 1 : 0;
        }
        return $parseVariablesCount === $count && $parseVariablesCount > 0;
    }

    /**
     * @param array $arrVar
     * @return string
     */
    public function loadTemplateVariablesRequired(array $arrVar, $err = ""): string
    {
        $page = new Template();
        return $page->viewTemplate('form.php', 'template_view.php', $arrVar, $err);
    }

    /**
     * @param array $sessionData
     * @param string $tpl
     * @return string
     */
    public function loadTemplateWithVariables(array $sessionData, string $tpl): string
    {
        $page = new Template($sessionData);
        $text[] = $page->generateTextFromTemplate($tpl);
        return $page->viewTemplate('text.php', 'template_view.php', $text);
    }

    /**
     * @param array $postData
     * @param array $parseVariables
     * @return bool
     */
    public function validateForm(array $postData, array $parseVariables): bool
    {
        $countVar = 0;
        $count = 0;
        foreach ($parseVariables as $item) {
            if (isset($postData[$item])) {
                $countVar++;
                if ((substr_count($item, "NUM") === 0) && (strlen($postData[$item]) > 3) && (strlen($postData[$item]) < 50)) {
                    $count++;
                }
                if ((substr_count($item, "NUM") > 0) && (strlen($postData[$item]) > 0) && (strlen($postData[$item]) < 30) && (is_numeric($postData[$item]))) {
                    $count++;
                }
            }
        }
        return ($count === $countVar);
    }

    /**
     * @return string
     */
    public function main(): string
    {
        try {
            $template = $this->readFile(self::TEMPLATE);
            $parseVariables = $this->parseTemplateVariables($template);
            if (!$this->validateForm($this->postData, $parseVariables)) {
                throw new ErrorException('Validate data error');
            }

            $this->updateSessions($this->postData, $parseVariables);
            $this->destroySessions($this->postData, $parseVariables);

            if (!$this->isSessionHasVariables($parseVariables, $this->sessionData)) {
                return $this->loadTemplateVariablesRequired($parseVariables);
            } else {
                return $this->loadTemplateWithVariables($this->sessionData, $template);

            }
        } catch (Exception $exception) {
            //  return $exception->getMessage();
            return $this->loadTemplateVariablesRequired($parseVariables, $exception->getMessage());
        }
        /*catch (FileErrorException $exception) {
              return $exception->getMessage();
           // return $this->loadTemplateVariablesRequired($parseVariables, $exception->getMessage());
        }*/
    }

    /**
     * @param array $postData
     * @param array $parseVariables
     * @return void
     */
    function updateSessions(array $postData, array $parseVariables): void
    {
        foreach ($parseVariables as $item) {
            if (isset($postData[$item])) {
                $this->sessionData[$item] = $postData[$item];
                $_SESSION[$item] = $postData[$item];
            }
        }
    }

    /**
     * @param array $postData
     * @param array $parseVariables
     * @return void
     */
    function destroySessions(array $postData, array $parseVariables): void
    {
        if (isset($postData['destroy'])) {
            foreach ($parseVariables as $item) {
                unset($_SESSION[$item]);
                unset($this->sessionData[$item]);
            }
        }
    }


}

session_start();
echo (new Main($_POST, $_SESSION))->main();



