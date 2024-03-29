<?php

class Crud
{
    private $name;
    private $champs;
    private $type;
    private $folder;
    private $conn;

    public function __construct()
    {
        $this->champs = array();
        $this->type = array();
        $this->conn = new PDO("mysql:host=localhost; dbname=crud_generator", 'root', '');
    }

    public function init($argv)
    {
        if ($argv[1] == "-g") {
            if ($argv[2] == "entity") {
                $this->entity($argv);
            }
        }
    }

    public function entity($argv)
    {
        if ($argv[3] != "") {
            $this->folder = $argv[3];
            mkdir($this->folder);
            $entityname = readline("=> Quel est le nom de l'entité que vous voulez créer ?\n");
            $this->name = $entityname;
            $this->newChamps();
        }
    }

    public function newChamps()
    {
        $champs = readline("=> Quel est le nom du nouveau champ à ajouter à l'entité product ? (Tapez \"done\"
pour arrêter d'ajouter des champs et générer l'entité)\n");
        if ($champs !== "done") {
            $this->champs[] = $champs;
            $type = readline("=> Quel est le type du champ name ? (string || integer) (string par défaut)");
            if($type == "")
            {
                $this->type[$champs] = "string";
            }else{
                if($type === "string" || $type === "integer"){
                    $this->type[$champs] = $type;
                }else{
                    unset($this->type[$champs]);
                    foreach($this->champs as $key => $value)
                    {
                        if($value === $champs)
                        {
                            unset($this->champs[$key]);
                        }
                    }
                }
            }
            $this->newChamps();
        }else{
            echo "=> Entité " . $this->name . " créée dans " . $this->folder . " avec les champs : name (string).
=> Table products créée dans la base de données my_CRUD_generator.
=> Model Product créé dans Product.php";
            $this->generateEntity();
        }
    }

    public function generateEntity()
    {
        $champs = "";
        foreach ($this->champs as $champ)
        {
            $champs .= "private $".$champ.';'.PHP_EOL;
        }
        $file = '
<?php
        
class ' . $this->name . ' {
        
    private $id;
    '.$champs.
    '
    
    public static function index(){
        $conn = new PDO("mysql:host=localhost; dbname=crud_generator", \'root\', \'\');
        return $conn->query("SELECT * FROM '.$this->name.'");
    
    }
    
    
    public static function show($id){
        $conn = new PDO("mysql:host=localhost; dbname=crud_generator", \'root\', \'\');
        return $conn->query("SELECT * FROM '.$this->name.' WHERE id =".$id" ");
    }
    
    public function load($id){
        $conn = new PDO("mysql:host=localhost; dbname=crud_generator", \'root\', \'\');
        $entity = new '.$this->name.'();
        return $conn->query("SELECT * FROM '.$this->name.' WHERE id =".$id" ");
    }
}';
        $path = $this->folder."/".$this->name.".php";
        file_put_contents($path, $file);
        $sql = "`id` INT(255) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id`), ".PHP_EOL;
        $i = 0;
        $len = count($this->champs);
        foreach ($this->champs as $champ)
        {
            $type = "";
            if($this->type[$champ] === "string"){
                $type = "text";
            }elseif($this->type[$champ] === "integer"){
                $type = "int";
            }
            if($i == $len-1)
            {
                $sql .= $champ . " " . $type.PHP_EOL;
            }else{

                $sql .= $champ . " " . $type.','.PHP_EOL;
            }
            $i++;
        }
        $this->conn->query('CREATE TABLE `crud_generator`.'.$this->name.' ('.$sql.' )');

    }

    /**
     * @return mixed
     */
    public
    function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Crud
     */
    public
    function setName($name)
    {
        $this->name = $name;
        return $this;
    }
}

$crud = new Crud();
$crud->init($argv);
