<?php

use Adianti\Control\TPage;

class ConsultaFormView extends TPage
{
    protected $form; // form
    private static $database = 'sistema';
    private static $activeRecord = 'Consulta';
    private static $primaryKey = 'id';
    private static $formName = 'formView_Consulta';

    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        parent::setTargetContainer('adianti_right_panel');

       

        TTransaction::open(self::$database);
        // creates the form
        $this->form = new BootstrapFormBuilder(self::$formName);

        $consulta = new Consulta($param['key']);
        // define the form title
        $this->form->setFormTitle("&nbsp;");

        $label1 = new TLabel("Código:", '#333333', '12px', 'B');
        $text1 = new TTextDisplay($consulta->id, '#333333', '12px', '');
        $label2 = new TLabel("Data consulta:", '#333', '12px', 'B');
        $text2 = new TTextDisplay(TDateTime::convertToMask($consulta->data_consulta, 'yyyy-mm-dd hh:ii', 'dd/mm/yyyy hh:ii'), '#333', '12px', '');
        $label3 = new TLabel("Médico:", '#333', '12px', 'B');
        $text3 = new TTextDisplay($consulta->medico->name, '#333', '12px', '');
        $label4 = new TLabel("Tempo de espera:", '#333', '12px', 'B');
        $text4 = new TTextDisplay($consulta->tempo_espera, '#333', '12px', '');

        $labelmin = new TLabel("Minutos", '#333', '10px', '');
        $text4->after($labelmin);
        // $text4->popover = 'true';
        // $text4->popside = 'right';
        // $text4->poptitle = 'Minutos';
        //$text4->popcontent = 'Minutos';


        $label5 = new TLabel("Paciente:", '#333', '12px', 'B');
        $text5 = new TTextDisplay($consulta->paciente->nome, '#333', '12px', '');
        $label6 = new TLabel("Nome:", '#333333', '12px', 'B');
        $text6 = new TTextDisplay($consulta->atestado_nome, '#333', '12px', '');
        $label7 = new TLabel("Rg:", '#333', '12px', 'B');
        $text7 = new TTextDisplay($consulta->atestado_rg, '#333', '12px', '');
        $label9 = new TLabel("Consulta inicio:", '#333', '12px', 'B');
        $text9 = new TTextDisplay(TDateTime::convertToMask($consulta->atestado_hora_inicio, 'yyyy-mm-dd hh:ii', 'dd/mm/yyyy hh:ii'), '#333', '12px', '');
        $label10 = new TLabel("Consulta fim:", '#333', '12px', 'B');
        $text10 = new TTextDisplay(TDateTime::convertToMask($consulta->atestado_hora_fim, 'yyyy-mm-dd hh:ii', 'dd/mm/yyyy hh:ii'), '#333', '12px', '');
        $label11 = new TLabel("Dias de repouso:", '#333', '12px', 'B');
        $text11 = new TTextDisplay($consulta->atestado_dias_repouso, '#333', '12px', '');
        $label12 = new TLabel("CID:", '#333', '12px', 'B');
        $text12 = new TTextDisplay($consulta->atestado_CID, '#333', '12px', '');


        $this->form->appendPage("Consulta");

        $this->form->addFields([new THidden('current_tab')]);
        $this->form->setTabFunction("$('[name=current_tab]').val($(this).attr('data-current_page'));");

        $row1 = $this->form->addFields([$label1],[$text1]);
        $row1->layout = ['col-sm-4 control-label','col-sm-8'];
        $row2 = $this->form->addFields([$label2],[$text2]);
        $row2->layout = ['col-sm-4 control-label','col-sm-8'];
        $row3 = $this->form->addFields([$label3],[$text3]);
        $row3->layout = ['col-sm-4 control-label','col-sm-8'];
        $row4 = $this->form->addFields([$label4],[$text4]);
        $row4->layout = ['col-sm-4 control-label','col-sm-8'];
        $row5 = $this->form->addFields([$label5],[$text5]);
        $row5->layout = ['col-sm-4 control-label','col-sm-8'];

        $this->receitas_id_consulta_list = new TQuickGrid;
        $this->receitas_id_consulta_list->disableHtmlConversion();
        $this->receitas_id_consulta_list->style = 'width:100%';
        $this->receitas_id_consulta_list->disableDefaultClick();

        $column_anotacao = $this->receitas_id_consulta_list->addQuickColumn("Anotacao", 'anotacao', 'left');
        $column_periodo = $this->receitas_id_consulta_list->addQuickColumn("Periodo", 'periodo', 'left');

        $this->receitas_id_consulta_list->createModel();

        $criteria_receitas_id_consulta = new TCriteria();
        $criteria_receitas_id_consulta->add(new TFilter('id_consulta', '=', $consulta->id));

        $criteria_receitas_id_consulta->setProperty('order', 'id desc');

        $receitas_id_consulta_items = Receitas::getObjects($criteria_receitas_id_consulta);

        $this->receitas_id_consulta_list->addItems($receitas_id_consulta_items);

        $icon = new TImage('fas:file-medical #000000');
        $title = new TTextDisplay("{$icon} Receitas", '#333333', '12px', '');

        $panel = new TPanelGroup($title, '#f5f5f5');
        $panel->class = 'panel panel-default formView-detail';
        $panel->add(new BootstrapDatagridWrapper($this->receitas_id_consulta_list));

        $this->form->addContent([$panel]);

        $this->sintomas_id_consulta_list = new TQuickGrid;
        $this->sintomas_id_consulta_list->disableHtmlConversion();
        $this->sintomas_id_consulta_list->style = 'width:100%';
        $this->sintomas_id_consulta_list->disableDefaultClick();

        $column_anotacao = $this->sintomas_id_consulta_list->addQuickColumn("Anotacao", 'anotacao', 'left');

        $this->sintomas_id_consulta_list->createModel();

        $criteria_sintomas_id_consulta = new TCriteria();
        $criteria_sintomas_id_consulta->add(new TFilter('id_consulta', '=', $consulta->id));

        $criteria_sintomas_id_consulta->setProperty('order', 'id desc');

        $sintomas_id_consulta_items = Sintomas::getObjects($criteria_sintomas_id_consulta);

        $this->sintomas_id_consulta_list->addItems($sintomas_id_consulta_items);

        $icon = new TImage('fas:comment-medical #000000');
        $title = new TTextDisplay("{$icon} Sintomas", '#333333', '12px', '');

        $panel = new TPanelGroup($title, '#f5f5f5');
        $panel->class = 'panel panel-default formView-detail';
        $panel->add(new BootstrapDatagridWrapper($this->sintomas_id_consulta_list));

        $this->form->addContent([$panel]);

        $this->form->appendPage("Atestado");
        $row6 = $this->form->addFields([$label6],[$text6]);
        $row6->layout = ['col-sm-4 control-label','col-sm-8'];
        $row7 = $this->form->addFields([$label7],[$text7]);
        $row7->layout = ['col-sm-4 control-label','col-sm-8'];
        $row8 = $this->form->addFields([$label9],[$text9]);
        $row8->layout = ['col-sm-4 control-label','col-sm-8'];
        $row9 = $this->form->addFields([$label10],[$text10]);
        $row9->layout = ['col-sm-4 control-label','col-sm-8'];
        $row10 = $this->form->addFields([$label11],[$text11]);
        $row10->layout = ['col-sm-4 control-label','col-sm-8'];
        $row11 = $this->form->addFields([$label12],[$text12]);
        $row11->layout = ['col-sm-4 control-label','col-sm-8'];

        // create the form actions
        


        $btnClose = new TButton('closeCurtain');
        $btnClose->class = 'btn btn-sm btn-default';
        $btnClose->style = 'margin-right:10px;';
        $btnClose->onClick = "Template.closeRightPanel();";
        $btnClose->setLabel("Fechar");
        $btnClose->setImage('fas:times');

        $btnEdit = new TActionLink('Editar', new TAction(['ConsultaForm', 'onEdit'], ['key'=>$consulta->id, 'register_state' => 'false']), 'green', null, null, 'fas:edit');
        $btnEdit->class = 'btn btn-sm btn-default';
        $btnEdit->style = 'margin-right:10px;';
        $btnEdit->onClick = "Template.closeRightPanel();";
        
        $this->form->addHeaderWidget($btnClose);
        $this->form->addHeaderWidget($btnEdit);
        TTransaction::close();
        parent::add($this->form);

    }

    public function onShow($param = null)
    {     

    }
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }

}

