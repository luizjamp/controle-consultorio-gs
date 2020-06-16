<?php

class Paciente extends TRecord
{
    const TABLENAME  = 'paciente';
    const PRIMARYKEY = 'id';
    const IDPOLICY   =  'serial'; // {max, serial}

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('data_nascimento');
        parent::addAttribute('cpf');
        parent::addAttribute('cep');
        parent::addAttribute('rua');
        parent::addAttribute('bairro');
        parent::addAttribute('cidade');
        parent::addAttribute('estado');
    }

    
    /**
     * Method getAgendas
     */
    public function getAgendas()
    {
        $criteria = new TCriteria;
        $criteria->add(new TFilter('id_paciente', '=', $this->id));
        return Agenda::getObjects( $criteria );
    }

}