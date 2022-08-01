<?php 
\Bitrix\Main\Loader::includeModule('highloadblock');
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;

abstract class CHLBT {
    static function hlbtByName($name){
        $args = array(
            'select' => array('ID'),
            'filter' => array('TABLE_NAME' => $name)
        );
        $res = -1;
        $hlblock = HLBT::getList($args);
        if ( $row = $hlblock->fetch() ){
            $res = $row["ID"];
        }
        return $res;
    }   //hlbtByName

    abstract function list($args);
    abstract function save($item);
    abstract function del($id);
}

class CHLBTEntity extends CHLBT {
    protected $hlbtId;
    protected $hlbtName = "";
    protected $entity_data_class = null;

    /**
     * @param {String} Table-name of highloadblock
     */
    public function __construct( string $hlbtName ) {
        $this->hlbtName = $hlbtName;
        $this->hlbtId = $this::hlbtByName($hlbtName);
        $hlblock = HLBT::getById($this->hlbtId)->fetch();
        $entity = HLBT::compileEntity($hlblock);
        $this->entity_data_class = $entity->getDataClass();
    }   //__construct

    private function assert( &$res ) {
        if ( $this->entity_data_class ){
            return true;
        }
        $res = array("result" => false, "error" => 'No data table "' . $this->hlbtName . '" exists');
        return false;
    }   //assert

    /**
     * Getting array of entityes
     * @param Array $args getList arguments
     */
    public function list( $args ){
        $res = array();
        if ( !$this->assert( $res ) ){
            return $res;
        }
        
        $params = array(
            "select" => !!$args["select"] ? $args["select"] : array('*')
        );
        
        if (!!$args['filter']){
            $params['filter'] = $args['filter'];
        }
        if (!!$args['order']){
            $params['order'] = $args['order'];
        }

        $rsData = $this->entity_data_class::getList($params);
        while( $el = $rsData->fetch() ){
            $res[] = $el;
        }
        return $res;
    }   //list

    public function save($item){
        $res = array();
        if ( !$this->assert( $res ) ){
            return $res;
        }
        $id = intval($item['ID']);
        $obResult = ( $id > 0 ) 
                        ? $this->entity_data_class::update( $id, $item ) 
                        : $this->entity_data_class::add( $item );
        $id = $obResult->getID();
        $res = array(
                        "success" => $obResult->isSuccess(), 
                        "ID"=> $id,
                        "error" => $obResult->isSuccess() ? null : $obResult->getErrorMessages(),
                        "item"  => $obResult->isSuccess() ? $this->list( array("filter" => array("=ID" => $id)) ) : null
                    );
        return $res;
    }   //save

    public function del($id){
        $res = array();
        if ( !$this->assert( $res ) ){
            return $res;
        }

        $res = ( $id > 0 ) ? $this->entity_data_class::delete($id) : false;

        if (!!$res){
            if ( $res->isSuccess() ){
                $res = array("id" => $id, "success" => true);
            } else {
                $res = array("id" => $id, "success" => false, "error" => $res->getErrorMessages());
            }
        } else {
            $res = array("success" => false, "error"=>"Unknown item #");
        }
        
        return $res;
    }   //del
}       //CHLBTEntity

class CHLBTAdds extends CHLBTEntity{
    private $q = 'USER'; /** USER | HDRS | EMPS | IMP_uuid */
    private $mainId;
    /**
     * @param {String} q USER | HEADS | EMPS
     * @parem {integer} mainId
     */
    public function __construct( $q, $mainId ) {
        $this->q = $q;
        $this->mainId = $mainId;
        parent::__construct('WpAdds');
    }
    
    public function list( $args = false){
        $args = array(
                        "select" => ["UF_LINK"],
                        "filter" => [
                            "LOGIC" => "AND",
                            ['=UF_Q' => $this->q],
                            ['=UF_MAIN' => $this->mainId]
                        ]
        );
        $_res = parent::list($args);
        $res = array();
        foreach($_res as $r){
            $res[] = $r["UF_LINK"];
        }
        return $res;
    }
    
    public function exists(){
        $args = array(
                        "select" => ["UF_MAIN"],
                        "filter" => ['=UF_Q' => $this->q]
        );
        $res = parent::list($args);
        
        return count($res) > 0 ? $res["UF_MAIN"] : -1;
        
    }
    
    /**
     * Deleting all by mainId
     * @return 
     */
    public function delAll(){
        global $DB;
        $res = $DB->Query(sprintf("delete from wpadds where UF_Q='%s' and UF_MAIN=%d", $this->q, $this->mainId));
        return $res;
    }
    
    public function add($link){
        global $DB;
        $arr = array("UF_Q" => "'" . $this->q . "'", "UF_MAIN" => $this->mainId, "UF_LINK" => $link);
        $res = $DB->Insert("wpadds", $arr, "",  false /*debug*/, ""  /*exist_id*/, true /*ignore_errors*/);
        return $res;
    }
    
    public function addAll($links){
        global $DB;
        $res = array();
        
        $DB->StartTransaction();
        
        $ok = true;
        
        $this->delAll();
        foreach($links as $l){
            $arr = array("UF_Q" => "'" . $this->q . "'", "UF_MAIN" => $this->mainId, "UF_LINK" => $l);
            $_res = $DB->Insert("wpadds", $arr, "",  false /*debug*/, ""  /*exist_id*/, true /*ignore_errors*/);
            if (intval($_res) > 0 ){
                $res[] = $_res;
            } else {
                $res = array("success" => false, "error" => sprintf("don`t save links at %s # %d->%d: %s", $this->q, $this->mainId, $l, $DB->db_Error));
                $ok = false;
                break;
            }
        }
        
        if ($ok){
            $DB->Commit();
        } else {
            $DB->Rollback();
        }
        
        return $res;
    }   //addAll
    
}   //CHLBTAdds

?>