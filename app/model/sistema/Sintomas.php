<?php

class Sintomas extends TRecord
{
    const TABLENAME  = 'sintomas';
    const PRIMARYKEY = 'id';
    const IDPOLICY   =  'serial'; // {max, serial}

    private $consulta;

    

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('id_consulta');
        parent::addAttribute('anotacao');
            
    }

    /**
     * Method set_consulta
     * Sample of usage: $var->consulta = $object;
     * @param $object Instance of Consulta
     */
    public function set_consulta(Consulta $object)
    {
        $this->consulta = $object;
        $this->id_consulta = $object->id;
    }

    /**
     * Method get_consulta
     * Sample of usage: $var->consulta->attribute;
     * @returns Consulta instance
     */
    public function get_consulta()
    {
    
        // loads the associated object
        if (empty($this->consulta))
            $this->consulta = new Consulta($this->id_consulta);
    
        // returns the associated object
        return $this->consulta;
    }

    
}

