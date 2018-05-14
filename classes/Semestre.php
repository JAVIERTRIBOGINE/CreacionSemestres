<?php
/**
 *  OBJETO QUE RECOGE ESPEJO DE LA TABLA ps_semestre DE LA BASE DE DATOS.
 *  USADO PARA ÉL MÓDULO PARA LA CREACIÓN DE SEMESTRES.
**/
class Semestre extends ObjectModel
{
    public $key;

    public $id_shop;

    public $date_start;

    public $date_end;

    public $class_start;

    public $active;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'semestre',
        'primary' => 'id_semestre',
        'multilang' => false,
        'fields' => array(
            'key' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 45),
            'id_shop' =>        array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'date_start' =>     array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_end' =>       array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'class_start' =>    array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'active' =>         array('type' => self::TYPE_INT, 'validate' => 'isInt'),
        ),
    );

    public function __construct($id=null){
        parent::__construct($id);

    }

    /**
     *  Estático que encuentra el id del Semestre activo (o sea el que está en curso)
     */
    public static function getIdActive(){
      //llamada  a base de datos
      $activo=Db::getInstance()->executeS('select id_semestre from ps_semestre where active=1');
      return $activo[0]['id_semestre'];

    }

    /**
     *   Estático que comprueba si el semestre que se está creando solapa sus fechas con
     *  ya creado
     *  $inicio: fecha inicio del semestre a comprobar
     *  $fin: fecha de finalización del semestre a comprobar
     */
    public static function solapanSemestres($inicio, $fin)
    {
      // consulta las fechas de todos los regisros de ps_semestre
      $fechas=Db::getInstance()->executeS('select id_semestre as id, date_start as inicio, date_end as fin from ps_semestre');
      foreach ($fechas as $key => $value) {
        if(($value['inicio']<$inicio && $value['fin']>=$inicio) || ($value['fin']>$fin && ($value['inicio']<=$fin)))
        {
          $eval[0]=false;
          $eval[1]=$value['id'];
          $eval[2]=$value['inicio'];
          $eval[3]=$value['fin'];
          return $eval;
      }
    }
    return null;
  }

  /**
   *  Estática que encuentra el semestre colindante con el que se pasa por parámetro
   *  $semestreActual: objeto semsestre a comparar
   */
  public static function findIdNextSemestre($semestreActual)
  {
    //recoge las fechas de inicio de todos los semestres registrados
    $a=Db::getInstance()->executeS('select date_start from ps_semestre');
  do{
      // por si acaso no lo estuviese, los ordena
      $desordenado=false;
      for ($b=0;$b<(count($a)-1);$b++){
        if($a[$b]['date_start']>$a[($b+1)]['date_start']){
          $aux=$a[$b]['date_start'];
          $a[$b]['date_start']=$a[($b+1)]['date_start'];
          $a[($b+1)]['date_start']=$aux;
          $desordenado=true;
      }
    }
  } while($desordenado);
  // una vez ordnadas las fechas, busca la posicion de la fecha del semestre
  // pasado por parámetro
  for ($c=0;$c<(count($a));$c++) {
    if($semestreActual->date_start==$a[$c]['date_start'])
      {
        // recoge el id del semestre con fecha de inicio inmediatamente posterior
        // a la del semestre pasado por parámetro
        $idNextSemestre=Db::getInstance()->executeS('select id_semestre from ps_semestre where date_start="'.$a[($c+1)]['date_start'].'"');
        return $idNextSemestre[0]['id_semestre'];
      }
  }
    return null;
}

/**
 * Estática que comprueba si existe un semestre colindante en el tiempo con el que se pasa por parámetro
 */
  public static function existNextSemestres($Semestre)
  {
    $eval=Db::getInstance()->executeS('select max(date_start) AS maximoInicio from ps_semestre');
    // retorna booeleano :  fecha inicio de semestre de parámetro es mayor que el maximo recogido en bbdd

    return $eval[0]['maximoInicio'] > $Semestre->date_end;
  }
}

?>
