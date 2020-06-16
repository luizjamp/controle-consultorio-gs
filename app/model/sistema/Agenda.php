<?php

class Agenda extends TRecord
{
    const TABLENAME  = 'agenda';
    const PRIMARYKEY = 'id';
    const IDPOLICY   =  'serial'; // {max, serial}

    private $paciente;

    

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('data_inicio');
        parent::addAttribute('data_fim');
        parent::addAttribute('id_paciente');
        parent::addAttribute('comentario');
        parent::addAttribute('id_cadastrante');
        
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

    
}