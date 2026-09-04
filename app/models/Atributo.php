<?php
namespace app\models;



class Atributo{

    private ?int $id_atributo;
    private ?int $fk_tabela;
    private ?int $fk_atributo;
    private ?string $nome_atributo;
    private ?string $tipo;
    private bool $pk;
    private bool $nn;
    private bool $ai;
    private bool $uq;
    private ?string $input;



    public function __construct($id_att,$fk_tab,$fk_att,$nome,$tipo,$pk,$nn,$ai,$uq){
        $this->id_atributo   = $id_att;
        $this->fk_tabela     = $fk_tab;
        $this->fk_atributo   = $fk_att;
        $this->nome_atributo = $nome;
        $this->tipo  = $tipo;
        $this->pk    = $pk;
        $this->nn    = $nn;
        $this->ai    = $ai;
        $this->uq    = $uq;
        $this->input = $this->inputs();
    }



    private function inputs(){
        switch(true){
            case $this->tipo=='int':
            case substr($this->tipo,0,3)=='int':
                return "<input type='number' name='{$this->nome_atributo}' id='{$this->nome_atributo}' value='<?= \$obj?\$obj['{$this->nome_atributo}']:''?>'><br>\n";
            break;
            case substr($this->tipo,0,7)=='decimal':
                return "<input type='number' step='0.01' name='{$this->nome_atributo}' id='{$this->nome_atributo}' value='<?= \$obj?\$obj['{$this->nome_atributo}']:''?>'><br>\n";
            break;
            
            case $this->tipo =='text':
            case substr($this->tipo,0,4) =='char':
            case substr($this->tipo,0,7) == 'varchar':
                return "<input type='text' name='{$this->nome_atributo}' id='{$this->nome_atributo}' value='<?= \$obj?\$obj['{$this->nome_atributo}']:''?>'><br>\n";
            break;
            
            case $this->tipo=='date':
                return "<input type='date' name='{$this->nome_atributo}' id='{$this->nome_atributo}' value='<?= \$obj?\$obj['{$this->nome_atributo}']:''?>'><br>\n";
            break;

            case $this->tipo=='year':
                return "<input type=\"number\" min=\"0\" max=\"2077\" step=\"1\" value=\"2000\" name='{$this->nome_atributo}' id='{$this->nome_atributo}' value='<?= \$obj?\$obj['{$this->nome_atributo}']:''?>'>";
            break;

            case substr($this->tipo,0,4)=='enum':
                $tipoString = str_ireplace(['enum(',')',"'",'"'],'',$this->tipo);
                $result = "<select name=\"{$this->nome_atributo}\" id=\"{$this->nome_atributo}\">\n";
                $result .= "<option value=\"\"><b>--</b></option>\n";
                foreach(explode(',',$tipoString) as $tipo){
                    $result .= "<option value=\"$tipo\" <?= \$obj? (\$obj['{$this->nome_atributo}']=='{$tipo}' ? 'selected' : null) : null ?> ><b>$tipo</b></option>\n";
                }
                $result .= "</select>\n";

                return $result;
            break;
            
            default:
                return"<p><b>Erro:o tipo de atributo $this->tipo não foi reconhecido.</b></p>";
            break;
        }
    }



    /**
     * Get the value of pk
     */ 
    public function getPk()
    {
        return $this->pk;
    }

    /**
     * Get the value of input
     */ 
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Get the value of nome_atributo
     */ 
    public function getNome_atributo()
    {
        return $this->nome_atributo;
    }
}