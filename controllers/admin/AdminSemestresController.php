<?php

include_once dirname(__FILE__).'/../../config/config.inc.php';
require_once dirname(__FILE__).'../../init.php';

class AdminSemestresController extends ModuleAdminController
{

public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'configuration';
        parent::__construct();

    }
  /**
   * Ćonfigura el formulario de creación de semestre
   */

 public function renderForm()
    {
        $shops = Db::getInstance()->executeS('select name, id_shop from ps_shop');
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->trans('Crear semestres', array(), 'Admin.Orderscustomers.Feature'),
                'icon' => 'icon-pencil'
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->trans('elige tienda', array(), 'Admin.Catalog.Feature'),
                    'name' => 'selTienda',
                    'required' => true,
                    'options' => array(
                        'query' => $shops,
                        'id' => 'id_shop',
                        'name' => 'name'
                    ),
                    'hint' => $this->trans('Selecciona la tienda asignada al semestres', array(), 'Admin.Catalog.Help')
                    ),
                array(
                    'type' => 'text',
                    'label' => $this->trans('clave semestres', array(), 'Admin.Global'),
                    'name' => 'clave_semestre',
                    'maxlength' => 10,
                    'required' => true,
                    'col' => 3,
                    'hint' => $this->trans('Debes introducir una clave referida a la fecha (p.e. "2sem1819)', array(), 'Admin.Orderscustomers.Help'),
                    'icon' => 'icon-pencil'
                ),
                array(
                    'type' => 'date',
                    'label' => $this->trans('Inicio semestres', array(), 'Admin.Global'),
                    'name' => 'inicio_semestre',
                    'maxlength' => 10,
                    'required' => true,
                    'hint' => $this->trans('Format: 2011-12-31 (inclusive).', array(), 'Admin.Orderscustomers.Help')
                ),
                array(
                    'type' => 'date',
                    'label' => $this->trans('Inicio clases', array(), 'Admin.Global'),
                    'name' => 'inicio_clases',
                    'maxlength' => 10,
                    'required' => true,
                    'hint' => $this->trans('Format: 2012-12-31 (inclusive).', array(), 'Admin.Orderscustomers.Help')
                ),
                array(
                    'type' => 'date',
                    'label' => $this->trans('Fin semestres', array(), 'Admin.Global'),
                    'name' => 'fin_semestre',
                    'maxlength' => 10,
                    'required' => true,
                    'hint' => $this->trans('Format: 2012-12-31 (inclusive).', array(), 'Admin.Orderscustomers.Help')
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->trans('Enable', array(), 'Admin.Actions'),
                    'name' => 'activo',
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->trans('activo', array(), 'Admin.Global')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->trans('no activo', array(), 'Admin.Global')
                        )
                    )

                )
            ),
            'submit' => array(
                'title' => $this->trans('Guardar', array(), 'Admin.Orderscustomers.Feature'),
                'name' => 'submitAdd'.$this->table.'AndStay',
                'icon' => 'process-icon-save'
            )
            );

        return parent::renderForm();
    }

public function postProcess()
{
   parent::postProcess();
   //recoge valores del formulario al activar el submit
    if (Tools::isSubmit('submitAddconfiguration'))
        {
        $idTienda = (int)Tools::getValue('selTienda');
        $key=Tools::getValue('clave_semestre');
        $inicioSemestre = Tools::getValue('inicio_semestre');
        $finSemestre = Tools::getValue('fin_semestre');
        $inicioClases = Tools::getValue('inicio_clases');
        $semestreActivo = (int)Tools::getValue('activo');

        // validaciones
        if ($finSemestre<$inicioSemestre) {
            $this->errors[] = $this->trans('La fecha de fin debe ser posterior a fecha inicio', array(), 'Admin.Catalog.Notification');
        }elseif (($inicioClases<$inicioSemestre) || ($inicioClases>$finSemestre)) {
            $this->errors[] = $this->trans('El periodo clases debe estar dentro del periodo del semestres', array(), 'Admin.Catalog.Notification');
        }elseif (!Validate::isDate($inicioSemestre) || !Validate::isDate($finSemestre) || !Validate::isDate($inicioClases)){
        $this->errors[] = $this->trans('Formato de fechas no valido', array(), 'Admin.Catalog.Notification');
        }elseif (Tools::getValue('activo')==1)
        {
          $cont=Db::getInstance()->executeS('select count(*) from ps_semestre where active=1');
          $cont1=(int)$cont[0]['count(*)'];
          switch ($cont1) {
               case 0:
                    $semestre= new Semestre();
                    $semestre->key = $key;
                    $semestre->id_shop = $idTienda;
                    $semestre->date_start = $inicioSemestre;
                    $semestre->date_end = $finSemestre;
                    $semestre->class_start = $inicioClases;
                    $semestre->active = $semestreActivo;
                    $semestre->add();
                    Db::getInstance()->update('configuration', array('value' => (string)Semestre::getIdActive()),'name="PS_SEMESTRE_ACTIVO"');
                    $this->confirmations[] = $this->_conf[3];
                    break;
               default:
                   $this->errors[] = $this->trans('semestres ya activado!', array(), 'Admin.Catalog.Notification');
                   break;
           }
        }
          elseif(Semestre::solapanSemestres($inicioSemestre, $finSemestre)!=null)
        {
            $solapar=Semestre::solapanSemestres($inicioSemestre, $finSemestre);
            $this->errors[] = $this->trans('Estas solapando con el semestres '.$solapar[1].' que empieza el '.$solapar[2].' y acaba el '.$solapar[3], array(), 'Admin.Catalog.Notification');
        }
          else
        {
          // si está todo validado, se crea el objeto semestre y se añade a bbdd
          $semestre= new Semestre();
          $semestre->id_semestre = $idSemestre;
          $semestre->key = $key;
          $semestre->id_shop = $idTienda;
          $semestre->date_start = $inicioSemestre;
          $semestre->date_end = $finSemestre;
          $semestre->class_start = $inicioClases;
          $semestre->active = $semestreActivo;
          $semestre->add();

           $this->confirmations[] = $this->_conf[3];
        }
      }
}
    //  renderiza el contendio en la plantilla smarty
    public function initContent()
    {
        $this->show_toolbar = false;

        $this->content .= $this->renderForm();
        $this->content .= $this->renderOptions();

        $this->context->smarty->assign(array(
            'content' => $this->content,
        ));
    }
}



?>
