<?php

class AgendaCalendarForm extends TPage
{
    protected $form;
    private $formFields = [];
    private static $database = 'sistema';
    private static $activeRecord = 'Agenda';
    private static $primaryKey = 'id';
    private static $formName = 'form_Agenda';
    private static $startDateField = 'data_inicio';
    private static $endDateField = 'data_fim';

    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct()
    {
        parent::__construct();
        parent::setTargetContainer('adianti_right_panel');
        // creates the form
        $this->form = new BootstrapFormBuilder(self::$formName);
        // define the form title
        $this->form->setFormTitle("Agendamento");

        $view = new THidden('view');

        $id = new THidden('id');
        $data_inicio = new TDateTime('data_inicio');
        $data_fim = new TDateTime('data_fim');
        $id_paciente = new TDBUniqueSearch('id_paciente', 'sistema', 'Paciente', 'id', 'nome','id asc'  );
        $id_cadastrante = new THidden('id_cadastrante');
        $id_cadastrante->setValue(TSession::getValue('user_id'));
    
        $comentario = new TEntry('comentario');

        $id->setEditable(false);

        $data_fim->setMask('dd/mm/yyyy hh:ii');
        $data_inicio->setMask('dd/mm/yyyy hh:ii');

        $data_fim->setDatabaseMask('yyyy-mm-dd hh:ii');
        $data_inicio->setDatabaseMask('yyyy-mm-dd hh:ii');

        $id->setSize(100);
        $data_fim->setSize(150);
        $data_inicio->setSize(150);
        $comentario->setSize('100%');
        $id_paciente->setSize('90%');


        
        $button = new TActionLink('', new TAction(['PacienteForm', 'onClear']), 'green', null, null, 'fa:plus-circle');
        $button->class = 'btn btn-default inline-button';
        $button->title = _t('New');
        $id_paciente->after($button);

        $row1 = $this->form->addFields([$id,$id_cadastrante]);
        $row2 = $this->form->addFields([new TLabel("Data inicio:", null, '14px', null)],[$data_inicio]);
        $row3 = $this->form->addFields([new TLabel("Data fim:", null, '14px', null)],[$data_fim]);
        $row4 = $this->form->addFields([new TLabel("Paciente:", null, '14px', null)],[$id_paciente]);
        $row5 = $this->form->addFields([new TLabel("Comentario:", null, '14px', null)],[$comentario]);

        $this->form->addFields([$view]);

        // create the form actions
        $btn_onsave = $this->form->addAction("Salvar", new TAction([$this, 'onSave']), 'fas:save #ffffff');
        $btn_onsave->addStyleClass('btn-primary'); 

        $btn_onclear = $this->form->addAction("Limpar formulário", new TAction([$this, 'onClear']), 'fas:eraser #dd5a43');

        $btn_ondelete = $this->form->addAction("Excluir", new TAction([$this, 'onDelete']), 'fas:trash-alt #dd5a43');
        $btn_oncancel = $this->form->addAction("Cancelar", new TAction([$this, 'onCancel']), 'fas:ban #dd5a43');

        parent::add($this->form);

    }

    public static function onCancel($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
    public function onSave($param = null) 
    {
        try
        {
            TTransaction::open(self::$database); // open a transaction


            $messageAction = null;

            $this->form->validate(); // validate form data

            $object = new Agenda(); // create an empty object 

            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data

            $object->store(); // save the object 

            $messageAction = new TAction(['AgendaCalendarFormView', 'onReload']);
            $messageAction->setParameter('view', $data->view);
            $messageAction->setParameter('date', explode(' ', $data->data_inicio)[0]);

            // get the generated {PRIMARY_KEY}
            $data->id = $object->id; 

            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction

            /**
            // To define an action to be executed on the message close event:
            $messageAction = new TAction(['className', 'methodName']);
            **/

            new TMessage('info', "Registro salvo", $messageAction); 

        }
        catch (Exception $e) // in case of exception
        {
            //</catchAutoCode> 

            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }
    public function onDelete($param = null) 
    {
        if(isset($param['delete']) && $param['delete'] == 1)
        {
            try
            {
                $key = $param[self::$primaryKey];

                // open a transaction with database
                TTransaction::open(self::$database);

                $class = self::$activeRecord;

                // instantiates object
                $object = new $class($key, FALSE);

                // deletes the object from the database
                $object->delete();

                // close the transaction
                TTransaction::close();

                $messageAction = new TAction(array(__CLASS__.'View', 'onReload'));
                $messageAction->setParameter('view', $param['view']);
                $messageAction->setParameter('date', explode(' ',$param[self::$startDateField])[0]);

                // shows the success message
                new TMessage('info', AdiantiCoreTranslator::translate('Record deleted'), $messageAction);
            }
            catch (Exception $e) // in case of exception
            {
                // shows the exception error message
                new TMessage('error', $e->getMessage());
                // undo all pending operations
                TTransaction::rollback();
            }
        }
        else
        {
            // define the delete action
            $action = new TAction(array($this, 'onDelete'));
            $action->setParameters((array) $this->form->getData());
            $action->setParameter('delete', 1);
            // shows a dialog to the user
            new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);   
        }
    }

    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open(self::$database); // open a transaction

                $object = new Agenda($key); // instantiates the Active Record 

                $object->view = $param['view']; 

                $btnonSaladeEspera = $this->form->addAction("Enviar para Espera", new TAction(['SaladeesperaFormList', 'onVindodaAgenda']), 'fas:business-time #ffffff');
                $btnonSaladeEspera->addStyleClass('btn btn-default btn-primary m-1'); 

                $this->form->setData($object); // fill the form 

                TTransaction::close(); // close the transaction 
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }

    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear(true);

    }

    public function onShow($param = null)
    {

    } 

    public function onStartEdit($param)
    {
        TSession::setValue('{$detailId}_items', null);

        $this->form->clear(true);

        $data = new stdClass;
        $data->view = $param['view']; // calendar view

        if ($param['date'])
        {
            if(strlen($param['date']) == '10')
                $param['date'].= ' 09:00';

            $data->data_inicio = str_replace('T', ' ', $param['date']);

            $data_fim = new DateTime($data->data_inicio);
            $data_fim->add(new DateInterval('PT1H'));
            $data->data_fim = $data_fim->format('Y-m-d H:i:s');

            $this->form->setData( $data );
        }
    }

    public static function onUpdateEvent($param)
    {
        try
        {
            if (isset($param['id']))
            {
                TTransaction::open(self::$database);

                $class = self::$activeRecord;
                $object = new $class($param['id']);

                $object->data_inicio = str_replace('T', ' ', $param['start_time']);
                $object->data_fim   = str_replace('T', ' ', $param['end_time']);

                $object->store();

                // close the transaction
                TTransaction::close();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            TTransaction::rollback();
        }
    }

}

