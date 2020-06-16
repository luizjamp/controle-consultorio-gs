<?php

class ConsultaList extends TPage
{

    private $datagrid; // listing
    private $pageNavigation;
    private $loaded;
    private static $database = 'sistema';
    private static $activeRecord = 'Consulta';
    private static $primaryKey = 'id';
    private static $formName = 'simpleList_Consulta';

    public function __construct()
    {
        parent::__construct();

        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid->disableHtmlConversion();
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);

        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);

        $column_id = new TDataGridColumn('id', "Id", 'center' , '52px');
        $column_data_consulta = new TDataGridColumn('data_consulta', "Data consulta", 'left');
        $column_id_medico = new TDataGridColumn('medico->name', "Médico", 'left');
        $column_tempo_espera = new TDataGridColumn('tempo_espera', "Tempo espera", 'left');
       

        $column_data_consulta->setTransformer( function($value, $object, $row) {
            return TDate::date2br($value);
        });

        $order_id = new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $column_id->setAction($order_id);
        //$column_data_inicio->setTransformer(array($this, 'formatDate'));

        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_data_consulta);
        $this->datagrid->addColumn($column_id_medico);
        $this->datagrid->addColumn($column_tempo_espera);

        $action_onEdit = new TDataGridAction(['ConsultaForm', 'onEdit'], ['id'=>'{id}', 'register_state' => 'false']);
        $action_onEdit->setUseButton(false);
        $action_onEdit->setButtonClass('btn btn-default btn-sm');
        $action_onEdit->setLabel("Editar");
        $action_onEdit->setImage('fas:edit #478fca'); // edit #478fca | trash-alt #dd5a43
        $action_onEdit->setField(self::$primaryKey);
        $this->datagrid->addAction($action_onEdit);


        $action_onEdit = new TDataGridAction(['ConsultaFormView', 'onShow'], ['id'=>'{id}', 'register_state' => 'false']);
        $action_onEdit->setUseButton(false);
        $action_onEdit->setButtonClass('btn btn-default btn-sm');
        $action_onEdit->setLabel("Visualizar");
        $action_onEdit->setImage('fas:search #478fca'); // edit #478fca | trash-alt #dd5a43
        $action_onEdit->setField(self::$primaryKey);
        $this->datagrid->addAction($action_onEdit);

        $action_onDelete = new TDataGridAction(array('ConsultaList', 'onDelete'));
        $action_onDelete->setUseButton(false);
        $action_onDelete->setButtonClass('btn btn-default btn-sm');
        $action_onDelete->setLabel("Excluir");
        $action_onDelete->setImage('fas:trash-alt #dd5a43');
        $action_onDelete->setField(self::$primaryKey);

        $this->datagrid->addAction($action_onDelete);

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
        $container->add(TBreadCrumb::create(["Básico","Consultalist"]));
        $container->add($panel);

        parent::add($container);

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
                $object = new Consulta($key, FALSE); 

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

    /**
     * Load the datagrid with data
     */
    public function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'cidadedigital'
            TTransaction::open(self::$database);

            // creates a repository for Consulta
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
            if(isset( $param['id_paciente']))
                $criteria->add( new TFilter( 'id_paciente', '=', $param['id_paciente']) );

            if($filters = TSession::getValue(__CLASS__.'_filters'))
            {
                foreach ($filters as $filter) 
                {
                    $criteria->add($filter);       
                }
            }

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

    public function onShow($param = null)
    {

    }

    /**
     * method show()
     * Shows the page
     */
    public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR !(in_array($_GET['method'],  array('onReload', 'onSearch')))) )
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

    //Adicionar a função no final da classe
    public function formatData($date, $object, $row)
    {
        $date = TDate::date2br($date);
        return  $date;
    }

}

