<?php
require_once(__DIR__ . '/chlbt.php');

class WpImport  {
    protected $month;
    protected $year;
    /** sources */
    private $emps;
    private $reds;
    private $plan;
    
    /* data */
    private $dvss;
    private $stfs;

    
    public function __construct( int $month, int $year ) {
        $this->month = $month;
        $this->year = $year;
        $this->getData();
    }
    
    private function getData(){
        
        /** sources */
        $api = "http://localhost:8060/zdyn/apinf.html";
        
        $params = array(
            "q"  => "emps",
            "yr" => $this->year,
            "mn" => $this->month
        );
        
        $url = new \Bitrix\Main\Web\Uri($api);
        $url->addParams($params);
        $this->emps = json_decode(\App\Helpers\CurlHelper::sendRequest( 'GET', $url->getUri() )["body"]);
        if ( json_last_error() != JSON_ERROR_NONE ){
            echo 'Can`t get Employees data';
            return false;
        }
        
        $params["q"] = "reds";
        $url = new \Bitrix\Main\Web\Uri($api);
        $url->addParams($params);
        
        $this->reds = json_decode(\App\Helpers\CurlHelper::sendRequest( 'GET', $url->getUri() )["body"]);
        if ( json_last_error() != JSON_ERROR_NONE ){
            echo 'Can`t get Red-days';
            return false;
        }
        
        $params["q"] = "plan";
        $url = new \Bitrix\Main\Web\Uri($api);
        $url->addParams($params);
        
        $this->plan = json_decode(\App\Helpers\CurlHelper::sendRequest( 'GET', $url->getUri() )["body"]);
        if ( json_last_error() != JSON_ERROR_NONE ){
            echo 'Can`t get Work Plan data';
            return false;
        }
        
        /** data */
        $dvss = new CHLBTEntity('department_codes');
        $args = array();
        $this->dvss = $dvss->list($args);
        
        $stfs = new CHLBTEntity('staffing');
        $this->stfs = $stfs->list($args);
        
        
        return true;
        
    }   //getData
    
    /**
     * get a existing staff by name
     * @param string $name
     * @return int (ID of staffing tb)
     */
    private function stf_by_name( $name ){
        $res = -1;
        foreach($this->stfs as $s){
            if ( strcasecmp($s["UF_NAME"], $name)==0 ){
                $res = $s["ID"];
                break;
            }
        }
        return $res;
        
    }
    
    private function add_dvs( $o ){
        $dvss = new CHLBTEntity('department_codes');
        $item = array(
                                "UF_ACTIVE" => 1, 
                                "UF_CODE" => $o->dvscode,
                                "UF_NAME" => $o->dvsname,
                                "UF_SORT" => 999
        );
        $_res = $dvss->save($item);
        $dvsId = $_res["ID"];
        $item["ID"] = $dvsId;
        $this->dvss[] = $item;
        return $dvsId;
    }
    
    
    /** 
     * get a exists Division by code 
     * @param string $code
     * @return int (ID of department_codes tb)
     */
    private function dvs_by_code( $code ){
        $res = -1;
        foreach($this->dvss as $d){
            if ($d["UF_CODE"] == $code){
                $res = $d["ID"];
                break;
            }
        }
        return $res;
    }
    
    
    
    /**
     * Check & get int ID by employees
     * @param uuid $id of Employee
     * 
     */
    private function emp_by_id( $uid ){
        $adds = new CHLBTAdds("EMP_" . $uid, -1);
        $res  = $adds->exists();
        if ($res > 0){
            return $res;
        }

        $emp = null;
        foreach($this->emps as $e){
            if ($e->ID == $uid){
                $emp = $e;
                break;
            }
        }
        
        if ($emp == null){
            return -1;
        }
        
        $dvsId = $this->dvs_by_code($emp->dvscode);
        if ($dvsId < 1){
            $dvsId = $this->add_dvs($emp);
        }
        
        
        $stfId = $this->stf_by_name($emp->stfname);
        if ($stfId < 1){
            $stfs = new CHLBTEntity('staffing');
            $item = array(
                            "UF_NAME" => $emp->stfname,
                            "UF_DISABLE" => 0,
                            "UF_SORT" => 999
            );
            $_res = $stfs->save($item);
            $stfId = $_res["ID"];
            $item["ID"] = $_res["ID"];
            $this->stfs[] = $item;
        }
        
        $entity = new CHLBTEntity('employees');
        $item  = array(
                    "UF_EMPNAME"=>$emp->name,
                    "UF_DVS"  => $dvsId,
                    "UF_STAFF"=> $stfId,
                    "UF_ADDED"=> Bitrix\Main\Type\Date::createFromTimestamp(strtotime($emp->stfdt))
        );
        $res = $entity->save($item);
        $empId = $res["ID"];
        
        $adds->set("main", $empId);
        $adds->add($empId);
        
        return $empId;
    }
    
    public function doPlan(){
        
        $res = array();
        $entity = new CHLBTEntity('WpActions');
        
        foreach($this->plan as $p){
            $uuid = $p->ID;
            $adds = new CHLBTAdds("PLN_" . $uuid, -1);
            if ( $adds->exists() > 0 ){
                continue;
            }
            $dvsId = $this->dvs_by_code($p->dvscode);
            if ($dvsId < 1){
                $dvsId = $this->add_dvs($p);
            }
            
            $hdrId = $this->emp_by_id($p->headid);
            
            $row = array(
                    "UF_ADT"      => Bitrix\Main\Type\DateTime::createFromTimestamp(strtotime($p->actdt)),
                    "UF_RED"      => 0,
                    "UF_DVS"      => $dvsId,
                    "UF_GRATTR"   => $p->grattr,
                    "UF_DAYATTR"  => $p->dayattr,
                    "UF_YEARATTR" => 0,
                    "UF_SPECATTR" => $p->specattr,
                    "UF_WWWATTR"  => $p->wwwattr,
                    "UF_READY"    => strcasecmp("ГОТОВ", $p->state)==0 ? 1 : 0,
                    "UF_TEXT"     => $p->action,
                    "UF_PLACE"    => $p->plcname,
                    "UF_CHIEF"    => $hdrId,
                    "UF_AUTHOR"   => 10, //$USER->GetID(),
                    "UF_INSTIME"  => Bitrix\Main\Type\DateTime::createFromTimestamp(strtotime($p->instime))
            );
            $res = $entity->save($row);
            $plnId = $res["ID"];
            if ($p->adds){
                $heads = array();
                $emps  = array();
                foreach($p->adds as $a){
                    $empId = $this->emp_by_id($a->empid);
                    if ($a->attr==0){
                        $emps[] = $empId;
                    } else {
                        $heads[] = $empId;
                    }
                }
                if ( count($heads)>0 ){
                    $adds = new CHLBTAdds("HEADS", $plnId);
                    $adds->addAll($heads);
                }
                if ( count($emps)>0 ){
                    $adds = new CHLBTAdds("EMPS", $plnId);
                    $adds->addAll($emps);
                }
            }
            $adds = new CHLBTAdds("PLN_" . $uuid, $plnId);
            $adds->add($plnId);
            $res[] = $plnId;
        }
        
        return $res;
    }   //doPlan
    
    public function doReds(){
        $res = array();
        $entity = new CHLBTEntity('WpActions');
        foreach($this->reds as $r){
            $uuid = $r->ID;
            $adds = new CHLBTAdds("RED_" . $uuid, -1);
            if ( $adds->exists() > 0 ){
                continue;
            }
            $row  = array(
                    "UF_ADT"      => Bitrix\Main\Type\Date::createFromTimestamp(strtotime($r->day)),
                    "UF_RED"      => 1,
                    "UF_DVS"      => null,
                    "UF_GRATTR"   => 0,
                    "UF_DAYATTR"  => 0,
                    "UF_YEARATTR" => $r->yr,
                    "UF_SPECATTR" => 0,
                    "UF_WWWATTR"  => 1,
                    "UF_READY"    => 1,
                    "UF_TEXT"     => $r->name,
                    "UF_AUTHOR"   => 10, //TODO: $USER->GetID(),
                    "UF_INSTIME"  => new Bitrix\Main\Type\DateTime()
            );
            $res = $entity->save($row);
            $redId = $res["ID"];
            $adds = new CHLBTAdds("RED_" . $uuid, $redId);
            $adds->add($redId);
            $res[] = $redId;
        }
        
        return $res;
        
    }
    
}   //WpImport  
?>