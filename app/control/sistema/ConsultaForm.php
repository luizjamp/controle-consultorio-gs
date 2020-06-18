<?php

use gasparimsat\FuncoesData;

class ConsultaForm extends TPage
{
    protected $form;
    private $formFields = [];
    private static $database = 'sistema';
    private static $activeRecord = 'Consulta';
    private static $primaryKey = 'id';
    private static $formName = 'form_Consulta';

    use Adianti\Base\AdiantiMasterDetailTrait;
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();

        // creates the form
        $this->form = new BootstrapFormBuilder(self::$formName);
        // define the form title
        $this->form->setFormTitle("Consulta");

        $id = new THidden('id');
        $data_consulta = new TDateTime('data_consulta');
        $id_medico = new THidden('id_medico');
        $tempo_espera = new TTime('tempo_espera');
        
        
        if(isset($param['esperaid'])){
            //CAMPO COM NOME DO PACIENTE
            $criteria = new TCriteria;
            $criteria->add( new TFilter( 'id', 'in', '(select id_paciente from saladeespera where id='.$param['esperaid'].')' )); // operator =, <, >, BETWEEN, IN, NOT IN, LIKE, IS NOT, 
            $id_paciente = new TDBUniqueSearch('id_paciente', self::$database, 'Paciente', 'id', 'nome', 'nome', $criteria); //TCriteria criteria = NULL    
        
            TTransaction::open(self::$database);
            $pacientenasala = SaladeEspera::where('id', '=', $param['esperaid'])->first();
            $id_paciente->setValue($pacientenasala->id_paciente);
            //atualizar atendimento para false, para não aparecer na sala de espera
            SaladeEspera::where('id', '=', $param['esperaid'])
                    ->set('ativo', 'false')->update();
            

            TTransaction::close();
            $id_paciente->setEditable(false);

            //CAMPO COM DEMORA DO ATENDIMENTO ATÉ A CONSULTA
            $difMin = new FuncoesData();
            $difMin->setDataInicio($pacientenasala->data_inicio);
            $difMin->setDataFim(date('Y-m-d H:i:s'));
            $diferenca_minutos = $difMin->calculo_de_minutos();
            $color = $diferenca_minutos/100;
            if($color>9) $color = 1;

            $tempo_espera->style = "background-color: rgba(255, 152, 0, ".$color.");"; 
            $tempo_espera->setValue($diferenca_minutos);

        }
        else
        {
            $id_paciente = new TDBUniqueSearch('id_paciente', 'sistema', 'Paciente', 'id', 'nome', 'nome'); //TCriteria criteria = NULL    

        }
        ///#############################
                    

        $atestado_rg = new TEntry('atestado_rg');
        $sintomas_consulta_anotacao = new TEntry('sintomas_consulta_anotacao');
        $receitas_consulta_anotacao = new TEntry('receitas_consulta_anotacao');
        $receitas_consulta_periodo = new TEntry('receitas_consulta_periodo');
        $atestado_nome = new TEntry('atestado_nome');
        $atestado_hora_inicio = new TDateTime('atestado_hora_inicio');
        $atestado_hora_fim = new TDateTime('atestado_hora_fim');
        $atestado_dias_repouso = new TEntry('atestado_dias_repouso');
        $atestado_CID = new TEntry('atestado_CID');
        $sintomas_consulta_id = new THidden('sintomas_consulta_id');
        $receitas_consulta_id = new THidden('receitas_consulta_id');
        $id_saladeespera = new THidden('id_saladeespera');
                
       //CAMPO COM DATA DA CONSULTA 
       $data_consulta->setValue(date('d/m/Y H:i:s'));
       $id_medico->setValue(TSession::getValue('userid'));
       

      

        $data_consulta->setMask('dd/mm/yyyy hh:ii');
        $atestado_hora_fim->setMask('dd/mm/yyyy hh:ii');
        $atestado_hora_inicio->setMask('dd/mm/yyyy hh:ii');

        $data_consulta->setDatabaseMask('yyyy-mm-dd hh:ii');
        $atestado_hora_fim->setDatabaseMask('yyyy-mm-dd hh:ii');
        $atestado_hora_inicio->setDatabaseMask('yyyy-mm-dd hh:ii');

        $atestado_dias_repouso->placeholder = "5 dias";
        $receitas_consulta_anotacao->placeholder = "Nome do Remédio";
        $receitas_consulta_periodo->placeholder = "de 1 em 1 hora";

        $id->setEditable(false);
        $id_medico->setEditable(false);
        $tempo_espera->setEditable(false);
        $data_consulta->setEditable(false);

        $atestado_rg->setMaxLength(50);
        $atestado_CID->setMaxLength(50);
        $atestado_nome->setMaxLength(100);
        $atestado_dias_repouso->setMaxLength(100);
        $receitas_consulta_periodo->setMaxLength(50);

        $id->setSize(100);
        $id_medico->setSize('100%');
        $tempo_espera->setSize(110);
        $data_consulta->setSize(150);
        $id_paciente->setSize('100%');
        $atestado_rg->setSize('100%');
        $atestado_CID->setSize('100%');
        $atestado_nome->setSize('100%');
        $atestado_hora_fim->setSize(150);
        $atestado_hora_inicio->setSize(150);
        $atestado_dias_repouso->setSize('100%');
        $receitas_consulta_periodo->setSize('100%');
        $sintomas_consulta_anotacao->setSize('100%');
        $receitas_consulta_anotacao->setSize('100%');

        $this->form->appendPage("Consulta");

        $this->form->addFields([new THidden('current_tab')]);
        $this->form->setTabFunction("$('[name=current_tab]').val($(this).attr('data-current_page'));");

        $row1 = $this->form->addFields([new TLabel("Paciente", null, '14px', null)],[$id_paciente],[new TLabel("Data consulta:", null, '14px', null)],[$data_consulta]);
        $row2 = $this->form->addFields([new TLabel("Tempo espera (min):", null, '14px', null)],[$tempo_espera]);
        $row3 = $this->form->addFields([$id],[$id_medico],[],[]);


        $this->form->appendPage("Sintomas");
        $row5 = $this->form->addFields([new TLabel("Sintoma:", null, '14px', null)],[$sintomas_consulta_anotacao]);
        $row6 = $this->form->addFields([$sintomas_consulta_id]);         
        $add_sintomas_consulta = new TButton('add_sintomas_consulta');

        $action_sintomas_consulta = new TAction(array($this, 'onAddSintomasConsulta'));

        $add_sintomas_consulta->setAction($action_sintomas_consulta, "Adicionar");
        $add_sintomas_consulta->setImage('fas:plus #000000');

        $this->form->addFields([$add_sintomas_consulta]);

        $detailDatagrid = new TQuickGrid;
        $detailDatagrid->disableHtmlConversion();
        $this->sintomas_consulta_list = new BootstrapDatagridWrapper($detailDatagrid);
        $this->sintomas_consulta_list->style = 'width:100%';
        $this->sintomas_consulta_list->class .= ' table-bordered';
        $this->sintomas_consulta_list->disableDefaultClick();
        $this->sintomas_consulta_list->addQuickColumn('', 'edit', 'left', 50);
        $this->sintomas_consulta_list->addQuickColumn('', 'delete', 'left', 50);

        $column_sintomas_consulta_anotacao = $this->sintomas_consulta_list->addQuickColumn("Sintomas", 'sintomas_consulta_anotacao', 'left');

        $this->sintomas_consulta_list->createModel();
        $this->form->addContent([$this->sintomas_consulta_list]);

        $this->form->appendPage("Receita");
        $row7 = $this->form->addFields([new TLabel("Medicamento", null, '14px', null)],[$receitas_consulta_anotacao],[new TLabel("Periodo:", null, '14px', null)],[$receitas_consulta_periodo]);
        $row8 = $this->form->addFields([$receitas_consulta_id]);         
        $add_receitas_consulta = new TButton('add_receitas_consulta');

        $action_receitas_consulta = new TAction(array($this, 'onAddReceitasConsulta'));

        $add_receitas_consulta->setAction($action_receitas_consulta, "Adicionar");
        $add_receitas_consulta->setImage('fas:plus #000000');

        $this->form->addFields([$add_receitas_consulta]);

        $detailDatagrid = new TQuickGrid;
        $detailDatagrid->disableHtmlConversion();
        $this->receitas_consulta_list = new BootstrapDatagridWrapper($detailDatagrid);
        $this->receitas_consulta_list->style = 'width:100%';
        $this->receitas_consulta_list->class .= ' table-bordered';
        $this->receitas_consulta_list->disableDefaultClick();
        $this->receitas_consulta_list->addQuickColumn('', 'edit', 'left', 50);
        $this->receitas_consulta_list->addQuickColumn('', 'delete', 'left', 50);

        $column_receitas_consulta_anotacao = $this->receitas_consulta_list->addQuickColumn("Anotacao", 'receitas_consulta_anotacao', 'left');
        $column_receitas_consulta_periodo = $this->receitas_consulta_list->addQuickColumn("Periodo", 'receitas_consulta_periodo', 'left');

        $this->receitas_consulta_list->createModel();
        $this->form->addContent([$this->receitas_consulta_list]);

        $this->form->appendPage("Atestado Médico");
        $row9 = $this->form->addFields([new TLabel("Nome:", null, '14px', null)],[$atestado_nome],[new TLabel("RG:", null, '14px', null)],[$atestado_rg]);
        $row10 = $this->form->addFields([new TLabel("Consulta hora inicio:", null, '14px', null)],[$atestado_hora_inicio],[new TLabel("Consulta hora fim:", null, '14px', null)],[$atestado_hora_fim]);
        $row11 = $this->form->addFields([new TLabel("Dias de repouso:", null, '14px', null)],[$atestado_dias_repouso],[new TLabel("CID:", null, '14px', null)],[$atestado_CID]);

        // create the form actions
        $btn_onsave = $this->form->addAction("Salvar", new TAction([$this, 'onSave']), 'fas:save #ffffff');
        $btn_onsave->addStyleClass('btn-primary'); 

        $btn_onclear = $this->form->addAction("Limpar formulário", new TAction([$this, 'onClear']), 'fas:eraser #dd5a43');

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->class = 'form-container';
        $container->add(TBreadCrumb::create(["Básico","Consulta"]));
        $container->add($this->form);

          parent::add($container);

    }

    public function onSave($param = null) 
    {
        try
        {
            TTransaction::open(self::$database); // open a transaction

            $messageAction = null;

            $this->form->validate(); // validate form data

            $object = new Consulta(); // create an empty object 

            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data

            $object->store(); // save the object 

            $receitas_consulta_items = $this->storeItems('Receitas', 'id_consulta', $object, 'receitas_consulta', function($masterObject, $detailObject){ 

                //code here

            }); 

            $sintomas_consulta_items = $this->storeItems('Sintomas', 'id_consulta', $object, 'sintomas_consulta', function($masterObject, $detailObject){ 

                //code here

            }); 

            // get the generated {PRIMARY_KEY}
            //$data->id = $object->id; 

            //$this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction

            /**
            // To define an action to be executed on the message close event:
            $messageAction = new TAction(['className', 'methodName']);
            **/
            $this->onClear($param);
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

    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open(self::$database); // open a transaction

                $object = new Consulta($key); // instantiates the Active Record 

                $receitas_consulta_items = $this->loadItems('Receitas', 'id_consulta', $object, 'receitas_consulta', function($masterObject, $detailObject, $objectItems){ 

                    //code here

                }); 

                $sintomas_consulta_items = $this->loadItems('Sintomas', 'id_consulta', $object, 'sintomas_consulta', function($masterObject, $detailObject, $objectItems){ 

                    //code here

                }); 

                $this->form->setData($object); // fill the form 

                    $this->onReload();

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
        TSession::setValue('sintomas_consulta_items', null);
        TSession::setValue('receitas_consulta_items', null);
        
        $this->onReload();

    }

    public function onAddSintomasConsulta( $param )
    {  
        try
        {
            $data = $this->form->getData();

            $sintomas_consulta_items = TSession::getValue('sintomas_consulta_items');
            $key = isset($data->sintomas_consulta_id) && $data->sintomas_consulta_id ? $data->sintomas_consulta_id : uniqid();
            $fields = []; 

            $fields['sintomas_consulta_anotacao'] = $data->sintomas_consulta_anotacao;
            $sintomas_consulta_items[ $key ] = $fields;

            TSession::setValue('sintomas_consulta_items', $sintomas_consulta_items);

            $data->sintomas_consulta_id = '';
            $data->sintomas_consulta_anotacao = '';

            $this->form->setData($data);

            $this->onReload( $param );
        }
        catch (Exception $e)
        {
            $this->form->setData( $this->form->getData());

            new TMessage('error', $e->getMessage());
        }
    }

    public function onEditSintomasConsulta( $param )
    {
        $data = $this->form->getData();

        // read session items
        $items = TSession::getValue('sintomas_consulta_items');

        // get the session item
        $item = $items[$param['sintomas_consulta_id_row_id']];

        $data->sintomas_consulta_anotacao = $item['sintomas_consulta_anotacao'];

        $data->sintomas_consulta_id = $param['sintomas_consulta_id_row_id'];

        // fill product fields
        $this->form->setData( $data );

        $this->onReload( $param );

    }

    public function onDeleteSintomasConsulta( $param )
    {
        $data = $this->form->getData();

        $data->sintomas_consulta_anotacao = '';

        // clear form data
        $this->form->setData( $data );

        // read session items
        $items = TSession::getValue('sintomas_consulta_items');

        // delete the item from session
        unset($items[$param['sintomas_consulta_id_row_id']]);
        TSession::setValue('sintomas_consulta_items', $items);

        // reload sale items
        $this->onReload( $param );

    }

    public function onReloadSintomasConsulta( $param )
    {
        $items = TSession::getValue('sintomas_consulta_items'); 

        $this->sintomas_consulta_list->clear(); 

        if($items) 
        { 
            $cont = 1; 
            foreach ($items as $key => $item) 
            {
                $rowItem = new StdClass;

                $action_del = new TAction(array($this, 'onDeleteSintomasConsulta')); 
                $action_del->setParameter('sintomas_consulta_id_row_id', $key);
                $action_del->setParameter('row_data', base64_encode(serialize($item)));
                $action_del->setParameter('key', $key);

                $action_edi = new TAction(array($this, 'onEditSintomasConsulta'));  
                $action_edi->setParameter('sintomas_consulta_id_row_id', $key);  
                $action_edi->setParameter('row_data', base64_encode(serialize($item)));
                $action_edi->setParameter('key', $key);

                $button_del = new TButton('delete_sintomas_consulta'.$cont);
                $button_del->setAction($action_del, '');
                $button_del->setFormName($this->form->getName());
                $button_del->class = 'btn btn-link btn-sm';
                $button_del->title = "Excluir";
                $button_del->setImage('fas:trash-alt #dd5a43');

                $rowItem->delete = $button_del;

                $button_edi = new TButton('edit_sintomas_consulta'.$cont);
                $button_edi->setAction($action_edi, '');
                $button_edi->setFormName($this->form->getName());
                $button_edi->class = 'btn btn-link btn-sm';
                $button_edi->title = "Editar";
                $button_edi->setImage('far:edit #478fca');

                $rowItem->edit = $button_edi;

                $rowItem->sintomas_consulta_anotacao = isset($item['sintomas_consulta_anotacao']) ? $item['sintomas_consulta_anotacao'] : '';

                $row = $this->sintomas_consulta_list->addItem($rowItem);

                $cont++;
            } 
        } 
    } 

    public function onAddReceitasConsulta( $param )
    {
        try
        {
            $data = $this->form->getData();

            $receitas_consulta_items = TSession::getValue('receitas_consulta_items');
            $key = isset($data->receitas_consulta_id) && $data->receitas_consulta_id ? $data->receitas_consulta_id : uniqid();
            $fields = []; 

            $fields['receitas_consulta_anotacao'] = $data->receitas_consulta_anotacao;
            $fields['receitas_consulta_periodo'] = $data->receitas_consulta_periodo;
            $receitas_consulta_items[ $key ] = $fields;

            TSession::setValue('receitas_consulta_items', $receitas_consulta_items);

            $data->receitas_consulta_id = '';
            $data->receitas_consulta_anotacao = '';
            $data->receitas_consulta_periodo = '';

            $this->form->setData($data);

            $this->onReload( $param );
        }
        catch (Exception $e)
        {
            $this->form->setData( $this->form->getData());

            new TMessage('error', $e->getMessage());
        }
    }

    public function onEditReceitasConsulta( $param )
    {
        $data = $this->form->getData();

        // read session items
        $items = TSession::getValue('receitas_consulta_items');

        // get the session item
        $item = $items[$param['receitas_consulta_id_row_id']];

        $data->receitas_consulta_anotacao = $item['receitas_consulta_anotacao'];
        $data->receitas_consulta_periodo = $item['receitas_consulta_periodo'];

        $data->receitas_consulta_id = $param['receitas_consulta_id_row_id'];

        // fill product fields
        $this->form->setData( $data );

        $this->onReload( $param );

    }

    public function onDeleteReceitasConsulta( $param )
    {
        $data = $this->form->getData();

        $data->receitas_consulta_anotacao = '';
        $data->receitas_consulta_periodo = '';

        // clear form data
        $this->form->setData( $data );

        // read session items
        $items = TSession::getValue('receitas_consulta_items');

        // delete the item from session
        unset($items[$param['receitas_consulta_id_row_id']]);
        TSession::setValue('receitas_consulta_items', $items);

        // reload sale items
        $this->onReload( $param );

    }

    public function onReloadReceitasConsulta( $param )
    {

    
        $items = TSession::getValue('receitas_consulta_items'); 

        $this->receitas_consulta_list->clear(); 

        if($items) 
        { 
            $cont = 1; 
            foreach ($items as $key => $item) 
            {
                $rowItem = new StdClass;

                $action_del = new TAction(array($this, 'onDeleteReceitasConsulta')); 
                $action_del->setParameter('receitas_consulta_id_row_id', $key);
                $action_del->setParameter('row_data', base64_encode(serialize($item)));
                $action_del->setParameter('key', $key);

                $action_edi = new TAction(array($this, 'onEditReceitasConsulta'));  
                $action_edi->setParameter('receitas_consulta_id_row_id', $key);  
                $action_edi->setParameter('row_data', base64_encode(serialize($item)));
                $action_edi->setParameter('key', $key);

                $button_del = new TButton('delete_receitas_consulta'.$cont);
                $button_del->setAction($action_del, '');
                $button_del->setFormName($this->form->getName());
                $button_del->class = 'btn btn-link btn-sm';
                $button_del->title = "Excluir";
                $button_del->setImage('fas:trash-alt #dd5a43');

                $rowItem->delete = $button_del;

                $button_edi = new TButton('edit_receitas_consulta'.$cont);
                $button_edi->setAction($action_edi, '');
                $button_edi->setFormName($this->form->getName());
                $button_edi->class = 'btn btn-link btn-sm';
                $button_edi->title = "Editar";
                $button_edi->setImage('far:edit #478fca');

                $rowItem->edit = $button_edi;

                $rowItem->receitas_consulta_anotacao = isset($item['receitas_consulta_anotacao']) ? $item['receitas_consulta_anotacao'] : '';
                $rowItem->receitas_consulta_periodo = isset($item['receitas_consulta_periodo']) ? $item['receitas_consulta_periodo'] : '';

                $row = $this->receitas_consulta_list->addItem($rowItem);

                $cont++;
            } 
        } 
    } 

    public function onShow($param = null)
    {

       TSession::setValue('sintomas_consulta_items', null);
       TSession::setValue('receitas_consulta_items', null);
       
        $this->onReload();

    } 

    public function onReload($params = null)
    {
        $this->loaded = TRUE;
        
        $this->onReloadSintomasConsulta($params);
        $this->onReloadReceitasConsulta($params);
    }

    public function show() 
    { 
        $param = func_get_arg(0);
        if(!empty($param['current_tab']))
        {
            $this->form->setCurrentPage($param['current_tab']);
        }

        if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') ) 
        { 
            $this->onReload( func_get_arg(0) );
           
        }
        
        parent::show();
    }

}

