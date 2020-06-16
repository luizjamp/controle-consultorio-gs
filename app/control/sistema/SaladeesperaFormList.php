<?php

use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Wrapper\TDBCombo;

class SaladeesperaFormList extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $loaded;
    private static $database = 'sistema';
    private static $activeRecord = 'Saladeespera';
    private static $primaryKey = 'id';
    private static $formName = 'form_list_Saladeespera';

    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct($param = null)
    {
        parent::__construct();
       
        // creates the form
        $this->form = new BootstrapFormBuilder(self::$formName);

        // define the form title
        $this->form->setFormTitle("Sala de Espera");


        $id = new THidden('id');
        $id_paciente = new TDBUniqueSearch('id_paciente', 'sistema', 'Paciente', 'id', 'nome','id asc'  );
            $button = new TActionLink('', new TAction(['PacienteForm', 'onClear']), 'green', null, null, 'fa:plus-circle');
            $button->class = 'btn btn-default inline-button';
            $button->title = _t('New');
            $id_paciente->after($button);
        
        $data_inicio = new TDateTime('data_inicio');
        $data_fim = new THidden('data_fim');
        $id_cadastrante = new THidden('id_cadastrante');
        $id_cadastrante->setValue(TSession::getValue('userid'));

 
        $id->setEditable(false);

        //$data_fim->setMask('dd/mm/yyyy hh:ii');
        $data_inicio->setMask('dd/mm/yyyy hh:ii');
        $data_inicio->setEditable(false);
        $data_inicio->setValue(date("d/m/Y H:i"));


       

        //$data_fim->setDatabaseMask('yyyy-mm-dd hh:ii');
        $data_inicio->setDatabaseMask('yyyy-mm-dd hh:ii');
       

        $id->setSize(100);
        $data_fim->setSize(150);
        $data_inicio->setSize(150);
        


        $id_paciente->setSize('90%');

        $row1 = $this->form->addFields([$id, $id_cadastrante]);
        $row2 = $this->form->addFields([new TLabel("Paciente:", null, '14px', null)],[$id_paciente],[new TLabel("Data inicio:", null, '14px', null)],[$data_inicio]);
        //$row2->layout = ['col-sm-4','col-sm-4', 'col-sm-2', 'col-sm-2'];
        $row4 = $this->form->addFields([$data_fim]);
        
    
        

        //$row5 
        
            $criteria = new TCriteria;
            $criteria->add( new TFilter( 'id', 'in', '(select system_user_id from system_user_group where system_group_id = 3)') ); // operator =, <, >, BETWEEN, IN, NOT IN, LIKE, IS NOT,

            $id_medico = new TDBCombo('id_medico', 'permission', 'SystemUser', 'id', 'name', 'name', $criteria); //TCriteria criteria = NULL
            //$id_medico->setMask('{name}'); // Máscara de exibição
            $id_medico->setSize('90%'); //px
            //$id_medico->enableSearch();
            $id_medico->setTip('Selecione uma opção');
            //$id_medico->setEditable(FALSE);
            //$id_medico->addValidation('id_medico', new TRequiredValidator);
            $row5 = $this->form->addFields( [ new TLabel('Médico') ] , [$id_medico] );
        
        
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );

        $btn_onsave = $this->form->addAction("Salvar", new TAction([$this, 'onSave']), 'fas:save #ffffff');
        $btn_onsave->addStyleClass('btn-primary'); 

        $btn_onclear = $this->form->addAction("Limpar formulário", new TAction([$this, 'onClear']), 'fas:eraser #dd5a43');

        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid->disableHtmlConversion();
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);

        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);

        $column_id = new TDataGridColumn('id', "Id", 'center' , '70px');
        $column_id_paciente = new TDataGridColumn('paciente->nome', "Paciente", 'left');
 
        $column_data_inicio = new TDataGridColumn('data_inicio', "Data inicio", 'left');
        $column_data_inicio->setTransformer(array($this, 'formatDate'));
        $column_id_medico = new TDataGridColumn('medico->name', "Médico", 'left');

        $order_id = new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $column_id->setAction($order_id);

        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_id_paciente);
        $this->datagrid->addColumn($column_data_inicio);
        //$this->datagrid->addColumn($column_data_fim);
        $this->datagrid->addColumn($column_id_medico);

        $action_onEdit = new TDataGridAction(array('SaladeesperaFormList', 'onEdit'));
        $action_onEdit->setUseButton(false);
        $action_onEdit->setButtonClass('btn btn-default btn-sm');
        $action_onEdit->setLabel("Editar");
        $action_onEdit->setImage('far:edit #478fca');
        $action_onEdit->setField(self::$primaryKey);

        $this->datagrid->addAction($action_onEdit);

        $action_onDelete = new TDataGridAction(array('SaladeesperaFormList', 'onDelete'));
        $action_onDelete->setUseButton(false);
        $action_onDelete->setButtonClass('btn btn-default btn-sm');
        $action_onDelete->setLabel("Excluir");
        $action_onDelete->setImage('fas:trash-alt #dd5a43');
        $action_onDelete->setField(self::$primaryKey);

        $this->datagrid->addAction($action_onDelete);

        // echo "É Médico!";
        $usergroupids = TSession::getValue('usergroupids');
        if (in_array(3, $usergroupids)) 
        { 
            $action_onConsulta = new TDataGridAction(array('SaladeesperaFormList', 'onConsulta'));
            $action_onConsulta->setUseButton(false);
            $action_onConsulta->setButtonClass('btn btn-default btn-sm');
            $action_onConsulta->setLabel("Consultar");
            $action_onConsulta->setImage('fas:laptop-medical #4caf50');
            $action_onConsulta->setField(self::$primaryKey);
    
            $this->datagrid->addAction($action_onConsulta);
        }



        // create the datagrid model
        $this->datagrid->createModel();

        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());

        $panel = new TPanelGroup;
        $panel->add($this->datagrid);

        $panel->addFooter($this->pageNavigation);

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(TBreadCrumb::create(["Básico","Sala de Espera"]));
        $container->add($this->form);
        $container->add($panel);

        parent::add($container);

    }
    public function onConsulta($param = null) 
    {
        if (isset($param))
        {
            $paramenvio['esperaid'] = $param['id'];
            TApplication::loadPage('ConsultaForm', 'onReload', $paramenvio);
        }
    }
    public function onEdit($param = null) 
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open(self::$database); // open a transaction

                $object = new Saladeespera($key); // instantiates the Active Record 

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
    public function onDelete($param = null) 
    { 
        if(isset($param['delete']) && $param['delete'] == 1)
        {
            try
            {
                // get the paramseter $key
                $key = $param['key'];
                // open a transaction with database
                TTransaction::open(self::$database);

                // instantiates object
                $object = new Saladeespera($key, FALSE); 

                // deletes the object from the database
                $object->delete();

                // close the transaction
                TTransaction::close();

                // reload the listing
                $this->onReload( $param );
                // shows the success message
                new TMessage('info', AdiantiCoreTranslator::translate('Record deleted'));
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
            $action->setParameters($param); // pass the key paramseter ahead
            $action->setParameter('delete', 1);
            // shows a dialog to the user
            new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);   
        }
    }
    public function onSave($param = null) 
    {
        try
        {
            TTransaction::open(self::$database); // open a transaction

            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/

            $messageAction = null;

            $this->form->validate(); // validate form data

            $object = new Saladeespera(); // create an empty object 

            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data

            $object->store(); // save the object 

            // get the generated {PRIMARY_KEY}
            $data->id = $object->id; 

            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction

            /**
            // To define an action to be executed on the message close event:
            $messageAction = new TAction(['className', 'methodName']);
            **/

            new TMessage('info', "Registro salvo", $messageAction); 

            $this->onReload();

        }
        catch (Exception $e) // in case of exception
        {
            //</catchAutoCode> 

            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }

    /**
     * Load the datagrid with data
     */
    public function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'cidadedigital'
            TTransaction::open(self::$database);

            // creates a repository for Saladeespera
            $repository = new TRepository(self::$activeRecord);
            $limit = 20;
            // creates a criteria
            $criteria = new TCriteria;

            if (empty($param['order']))
            {
                $param['order'] = 'id';    
            }

            if (empty($param['direction']))
            {
                $param['direction'] = 'desc';
            }

            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);

            $criteria->add(new TFilter('ativo', '=', 'true'));


            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);

            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // add the object inside the datagrid

                    $this->datagrid->addItem($object);

                }
            }

            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);

            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit

            // close the transaction
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }

    public function onClear( $param )
    {
        $this->form->clear(true);

    }

    public function onShow($param = null)
    {

    }

    public function onVindodaAgenda($param){

       if (isset($param['id_paciente']))
       {
           $object = new SaladeEspera(); // instantiates the Active Record 
           $object->id_paciente = 1;      
           $this->form->setData($object);
           TScript::create("Template.closeRightPanel()");
           new TMessage('info', 'Paciente selecionado na sala de espera, selecione o médico e inclua na lista.'); //TAction action = NULL, title_msg
            
        }
    }

    /**
     * method show()
     * Shows the page
     */
    public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR !(in_array($_GET['method'],  array('onReload')))) )
        {
            if (func_num_args() > 0)
            {
                $this->onReload( func_get_arg(0) );
            }
            else
            {
                $this->onReload();
            }
        }
        parent::show();
    }
    public function formatDate($date, $object, $row)
    {
        $dateStart = new \DateTime($date);
        $dateNow   = new \DateTime(date('Y-m-d H:i'));
        $dateDiff = $dateStart->diff($dateNow);
        
        $minutes = $dateDiff->days * 24 * 60;
        $minutes += $dateDiff->h * 60;
        $minutes += $dateDiff->i;
       
        $color = $minutes/100;
        if($color>9) $color = 1;
        
        $color = 'rgba(255, 152, 0, '.$color.')';
        $dt = new DateTime($date);
        $row->style = "background: ".$color;
        return  $dt->format('d/m/Y H:i').' <font style="font-size:11px;">(esperando há '.$minutes.' min)</font>';
    }
}

