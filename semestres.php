<?php

/**
 * 	Instalación y configuración del módulo en back-office.
 *	Aparecerá pestaña en el menú lateral
 */
if (!defined('_PS_VERSION_'))
exit;

class semestres extends Module {

	public function __construct() {
	$this->name = 'semestres';
	$this->tab = 'front_office_features';
	$this->version = '1.0.0';
	$this->author = 'xavi';
	$this->need_instance = 0;
	$this->ps_versions_compliancy = array('min'=>'1.6','max'=>_PS_VERSION_);
	$this->bootstrap = true;
	parent::__construct();
	$this->displayName = $this->l('semestres');
	$this->description = $this->l('Creacion Semestres');
	$this->confirmUninstall = $this->l('¿Desea desinstalar?');
	}

	public function install() {
		if (!parent::install()  || !$this->installModuleTab('AdminSemestres', array(1=>'Creacion Semestres'), 2))
			return false;
		return true;
	}


	public function uninstall() {
		if (!parent::uninstall()  || !$this->uninstallModuleTab('AdminSemestres'))
			return false;
		return true;
	}

	/* crea pestaña y linka con el controlador*/

	public function installModuleTab($tabClass, $tabName, $idTabParent) {
        $tab = new Tab();
        $tab->name=$tabName;
        $tab->class_name = $tabClass;
        $tab->module = $this->name;
        $tab->id_parent = $idTabParent;
        $tab->position=5;
        if(!$tab->save())
        	return false;
        return true;
	}

	public function uninstallModuleTab($tabClass)
	{
		$idTab = Tab::getIdFromClassName($tabClass);
		if($idTab !=0) {
			$tab = new Tab($idTab);
			$tab->delete();
			return true;
		}
        return false;
    }

}
?>
