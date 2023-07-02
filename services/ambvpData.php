<?php
class AmbvpData
{
    public $frenchText;
    public $frenchDict;

    public $russianText;
    public $russianDict;

    public $persianText;
    public $persianDict;

    public $tamilText;
    public $tamilDict;

    public function __construct($french, $russian, $persian, $tamil)
    {
        $this->frenchText = $french;
        $this->russianText = $russian;
        $this->persianText = $persian;
        $this->tamilText = $tamil;

        function get_books($textArray)
        {
            $result = [];
            foreach ($textArray as $arr) {
                if (!in_array($arr["b"], $result))
                    array_push($result, $arr['b']);
            }
            return $result;
        }

        $this->frenchDict =  get_books($this->frenchText);
        $this->russianDict =  get_books($this->russianText);
        $this->persianDict =  get_books($this->persianText);
        $this->tamilDict =  get_books($this->tamilText);
    }
}
