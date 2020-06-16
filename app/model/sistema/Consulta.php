<?php

class Consulta extends TRecord
{
    const TABLENAME  = 'consulta';
    const PRIMARYKEY = 'id';
    const IDPOLICY   =  'serial'; // {max, serial}

    private $paciente;

    

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('data_consulta');
        parent::addAttribute('id_medico');
        parent::addAttribute('tempo_espera');
        parent::addAttribute('id_paciente');
        parent::addAttribute('atestado_nome');
        parent::addAttribute('atestado_rg');
        parent::addAttribute('atestado_');
        parent::addAttribute('atestado_hora_inicio');
        parent::addAttribute('atestado_hora_fim');
        parent::addAttribute('atestado_dias_repouso');
        parent::addAttribute('atestado_CID');
        parent::addAttribute('id_saladeespera');
            
    }

    /**
     * Method set_paciente
     * Sample of usage: $var->paciente = $object;
     * @param $object Instance of Paciente
     */
    public function set_paciente(Paciente $object)
    {
        $this->paciente = $object;
        $this->id_paciente = $object->id;
    }

    /**
     * Method get_paciente
     * Sample of usage: $var->paciente->attribute;
     * @returns Paciente instance
     */
    public function get_paciente()
    {
    
        // loads the associated object
        if (empty($this->paciente))
            $this->paciente = new Paciente($this->id_paciente);
    
        // returns the associated object
        return $this->paciente;
    }




    public function set_medico(SystemUser $object)
    {
        $this->medico = $object;
        $this->id_medico = $object->id;
    }

    /**
     * Method get_paciente
     * Sample of usage: $var->paciente->attribute;
     * @returns Paciente instance
     */
    public function get_medico()
    {
        // loads the associated object
        if (empty($this->medico))
            $this->medico = new SystemUser($this->id_medico);
        // returns the associated object
        return $this->medico;
    }







    public function set_SaladeEspera(Saladeespera $object)
    {
        $this->saladeespera = $object;
        $this->id_saladeespera = $object->id;
    }

    /**
     * Method get_paciente
     * Sample of usage: $var->paciente->attribute;
     * @returns Paciente instance
     */
    public function get_SaladeEspera()
    {
    
        // loads the associated object
        if (empty($this->saladeespera))
            $this->saladeespera = new Saladeespera($this->id_saladeespera);
    
        // returns the associated object

        return $this->saladeespera;
    }













    /**
     * Method getReceitass
     */
    public function getReceitass()
    {
        $criteria = new TCriteria;
        $criteria->add(new TFilter('id_consulta', '=', $this->id));
        return Receitas::getObjects( $criteria );
    }
    /**
     * Method getSintomass
     */
    public function getSintomass()
    {
        $criteria = new TCriteria;
        $criteria->add(new TFilter('id_consulta', '=', $this->id));
        return Sintomas::getObjects( $criteria );
    }

    
}

