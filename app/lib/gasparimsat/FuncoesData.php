<?php
namespace gasparimsat;

class FuncoesData   
{
    private $DataInicio;
    private $DataFim;

    public function __construct()
    {
       
    }
    public function setDataInicio($data){
        $this->DataInicio = $data;
    }
    public function setDataFim($data){
        $this->DataFim = $data;
    }
    public function calculo_de_minutos(){

        $dateStart = new \DateTime($this->DataInicio);
        $dateNow   = new \DateTime($this->DataFim);
        $dateDiff = $dateStart->diff($dateNow);
        
        $minutes = $dateDiff->days * 24 * 60;
        $minutes += $dateDiff->h * 60;
        $minutes += $dateDiff->i;

        return  $minutes;
    }
}


