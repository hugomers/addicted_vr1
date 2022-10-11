<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{



    private $conn = null;

public function __construct(){
    $access = env("ACCESS_FILE");
    if(file_exists($access)){
    try{
        $this->conn  = new \PDO("odbc:DRIVER={Microsoft Access Driver (*.mdb, *.accdb)};charset=UTF-8; DBQ=".$access."; Uid=; Pwd=;");
    }catch(\PDOException $e){ die($e->getMessage()); }
    }else{ die("$access no es un origen de datos valido."); }
}

public function access(request $request){
    $prices = $this->prices($request->prices);
    $products = $this->products($request->products);
    return ["products" => $products, "prices" => $prices];
    // $all = $request->all();
    // return $all;
}

public function products($products){
    $keys = array_keys($products[0]);
    $update = "";
    $values = "";
    $cols = "";
    foreach($keys as $i => $key){
        if($i == 0){
            $update = $key." = ?";
            $cols = " ".$key;
            $values = " ?";
        }else{
            $update = $update.", ".$key." = ?";
            $values = $values.", ?";
            $cols = $cols.", ".$key;
        }
    }
    $query = "UPDATE F_ART SET ".$update." WHERE CODART = ?";
    $exec = $this->conn->prepare($query);
    $query_select = "SELECT count(*) FROM F_ART WHERE CODART = ?";
    $exec_select = $this->conn->prepare($query_select);
    $query_insert = "INSERT INTO F_ART (".$cols.") VALUES(".$values.")";
    $exec_insert = $this->conn->prepare($query_insert);
    $response = [];
    foreach($products as $key => $row){
        $exec_select->execute([$row["CODART"]]);
        $count = intval($exec_select->fetch(\PDO::FETCH_ASSOC)['Expr1000']);
        if($count == 1){
            $toUpdate = array_values($row);
            $toUpdate[] = $row["CODART"];
            $result = $exec->execute($toUpdate);
            if($result){
                $accion = "Actualización";
            }else{
                $accion = "No se ha podido actualizar";
            }
        }else if($count == 0){
            $result = $exec_insert->execute(array_values($row));
            $this->createStocks($row["CODART"]);
            if($result){
                $accion = "Creado";
            }else{
                $accion = "No se ha podido crear";
            }
        }else{
            $accion = "Duplicado";
        }
        $response[] = ["Modelo" => $row["CODART"], "Código" => $row["CCOART"], "Descripción" => $row["DESART"], "Acción" => $accion];
    }
    return $response;
}

public function prices($prices){
    $products = collect($prices)->groupBy('ARTLTA');
    $query_delete = "DELETE FROM F_LTA WHERE ARTLTA = ?;";
    $exec_delete = $this->conn->prepare($query_delete);
    $keys = array_keys($prices[0]);
    foreach($keys as $i => $key){
        if($i == 0){
            $cols = "".$key;
        }else{
            $cols = $cols.", ".$key;
        }
    }
    $response = [];
    foreach($products as $key => $product){
        $query_insert = "INSERT INTO F_LTA(".$cols.") VALUES (?, ?, ?, ?)";
        $res = $exec_delete->execute([$key]);
        $values = "";
        if($res){
            $prices_inserted = [];
            foreach($product as $price){
                $exec_insert = $this->conn->prepare($query_insert);
                $res_insert = $exec_insert->execute(array_values($price));
                $prices_inserted[$price["TARLTA"]] = $price["PRELTA"];
            }
            $response[] = array_merge(["Modelo" => $price["ARTLTA"]], $prices_inserted);
        }
    }
    return $response;

}

public function createStocks($product){
    $almacenes = ["GEN" => "GEN", "EXH" => "EXH", "DES" => "DES", "FDT" => "FDT", "EMP" => "EMP"];
    $query = "INSERT INTO F_STO(ARTSTO, ALMSTO, MINSTO, MAXSTO, ACTSTO, DISSTO) VALUES(?,?,?,?,?,?)";
    $exec = $this->conn->prepare($query);
    $response = [];
    foreach($almacenes as $almacen){
        if($almacen){
            $exec->execute([$product, $almacen, 0, 0, 0, 0]);
        }
    }
    return response()->json($response);
}


}
