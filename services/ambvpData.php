<?php
class AmbvpData
{
    public $frenchText;
    public $french_books;

    public $russianText;
    public $russian_books;

    public $persianText;
    public $persian_books;

    public $tamilText;
    public $tamil_books;

    public $persian_numbers_dict;

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

        $this->french_books =  get_books($this->frenchText);
        $this->russian_books =  get_books($this->russianText);
        $this->persian_books =  get_books($this->persianText);
        $this->tamil_books =  get_books($this->tamilText);

        $this->persian_numbers_dict = [
            "۰"=> "0",
            "۱" => "1",
            "۲" => "2",
            '۳' => "3",
            "۴" => "4",
            "۵" => "5",
            "۶" => "6",
            "۷" => "7",
            "۸" => "8",
            "۹" => "9"
        ];
    }
}
