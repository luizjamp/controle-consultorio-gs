<?php

class Saladeespera extends TRecord
{
    const TABLENAME  = 'saladeespera';
    const PRIMARYKEY = 'id';
    const IDPOLICY   =  'serial'; // {max, serial}

    

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('id_paciente');
        parent::addAttribute('data_inicio');
        parent::addAttribute('data_fim');
        parent::addAttribute('id_medico');
        parent::addAttribute('ativo');
        parent::addAttribute('id_cadastrante');
    }


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
    
    
}