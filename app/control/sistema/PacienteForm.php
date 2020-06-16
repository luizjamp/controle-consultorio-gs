<?php

use Adianti\Control\TAction;
use Adianti\Validator\TCPFValidator;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TEntry;

class PacienteForm extends TWindow
{
    protected $form;
    private $formFields = [];
    private static $database = 'sistema';
    private static $activeRecord = 'Paciente';
    private static $primaryKey = 'id';
    private static $formName = 'form_Paciente';

    /**
     * 
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        parent::setModal(true);
        parent::removePadding();
        parent::setSize(600,null);
        parent::setTitle('Paciente');
        // creates the form
        $this->form = new BootstrapFormBuilder(self::$formName);

        $id = new THidden('id');
        $nome = new TEntry('nome');
        $data_nascimento = new TDate('data_nascimento');
        $cpf = new TEntry("cpf");
        $cpf ->setMask('999.999.999-99');
        $cpf->addValidation('cpf', new TCPFValidator);

        $cep = new TEntry('cep');
        $cep ->setMask('99999-999');
        //$cep->setNumericMask( 0, '','','', TRUE );
       
        $rua = new TEntry('rua');
        $bairro = new TEntry('bairro');
        $cidade = new TEntry('cidade');
        $estado = new TEntry('estado');
        $btnonCep = new TButton('btnonCep');


        $id->setEditable(false);
        $nome->setMaxLength(100);
        $data_nascimento->setMask('dd/mm/yyyy');
        $data_nascimento->setDatabaseMask('yyyy-mm-dd');

        $id->setSize(100);
        $nome->setSize('100%');
        $data_nascimento->setSize(110);
      
       
        $btnonCep->setAction(new TAction([$this, 'onCep']), "Completar Endereço");
        
        $btnonCep->setImage('fas:search');



        $row1 = $this->form->addFields([$id]);
        $row2 = $this->form->addFields([new TLabel("Nome:", null, '14px', null)],[$nome]);
        $row3 = $this->form->addFields([new TLabel("Data nascimento:", null, '14px', null)],[$data_nascimento]);
        $row3 = $this->form->addFields([new TLabel("CPF:", null, '14px', null)],[$cpf]);
        $cep = $this->form->addFields([new TLabel("CEP:", null, '14px', null)],[$cep,$btnonCep]);
        $estado = $this->form->addFields([new TLabel("Estado:", null, '14px', null)],[$estado]);
        $cidade = $this->form->addFields([new TLabel("Cidade:", null, '14px', null)],[$cidade]);
        $bairro = $this->form->addFields([new TLabel("Bairro:", null, '14px', null)],[$bairro]);
        $rua = $this->form->addFields([new TLabel("Rua:", null, '14px', null)],[$rua]);



        $btn_onsave = $this->form->addAction("Salvar", new TAction([$this, 'onSave']), 'fas:save #ffffff')->addStyleClass('btn-primary'); ;
        $btn_onclear = $this->form->addAction("Limpar formulário", new TAction([$this, 'onClear']), 'fas:eraser #dd5a43');

        

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->class = 'form-container';
        $container->add($this->form);

        parent::add($container);

    }

//<generated-FormAction-onSave>
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

            $object = new Paciente(); // create an empty object //</blockLine>

            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data

            $object->store(); // save the object //</blockLine>

            // get the generated {PRIMARY_KEY}
            $data->id = $object->id; //</blockLine>

            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction

            /**
            // To define an action to be executed on the message close event:
            $messageAction = new TAction(['className', 'methodName']);
            **/

            new TMessage('info', "Registro salvo", $messageAction);
            TWindow::closeWindow();
            TWindow::close();
        }
        catch (Exception $e) // in case of exception
        {

            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }
    public function onEdit( $param )//</ini>
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open(self::$database); // open a transaction

                $object = new Paciente($key); // instantiates the Active Record //</blockLine>
                $this->form->setData($object); // fill the form //</blockLine>

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
    }//</end>

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

    public function onCep($param = null)
    {
        $resultado = @file_get_contents('http://republicavirtual.com.br/web_cep.php?cep='.urlencode($param['cep']).'&formato=query_string');  
        if(!$resultado){  
            $resultado = "&resultado=0&resultado_txt=erro+ao+buscar+cep";  
        }  

        parse_str($resultado, $retorno);   
        $obj = new StdClass;
        $obj->id                = $param['id'];
        $obj->nome              = $param['nome'];
        $obj->data_nascimento   = $param['data_nascimento'];
        $obj->cpf               = $param['cpf'];

        $obj->cep       = $param['cep'];
        $obj->rua       = strtoupper( $retorno['tipo_logradouro'].' '.$retorno['logradouro']);
        $obj->bairro    = strtoupper( $retorno['bairro']);
        $obj->cidade    = strtoupper( $retorno['cidade']);
        $obj->estado    = strtoupper( $retorno['uf']); 
        TForm::sendData(self::$formName, $obj);
    }
}


