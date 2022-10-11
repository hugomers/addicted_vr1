<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientsController extends Controller
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
    


    public function index(request $request){
        $client  = $request->clients["CODCLI"];
        $delete = "DELETE FROM F_CLI WHERE CODCLI = ?";
         $exec = $this->conn->prepare($delete);
         $exec ->execute([$request->clients["CODCLI"]]);   
         $insert = "INSERT INTO F_CLI (CODCLI,CCOCLI,NOFCLI,NOCCLI,DOMCLI,POBCLI,CPOCLI,PROCLI,TELCLI,AGECLI,FPACLI,TARCLI,TCLCLI,FALCLI,NVCCLI,PAICLI,DOCCLI,FUMCLI)
          VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
         $exec = $this->conn->prepare($insert);
         $exec ->execute([
            $request->clients["CODCLI"],
            $request->clients["CCOCLI"],
            $request->clients["NOFCLI"],
            $request->clients["NOCCLI"],
            $request->clients["DOMCLI"],
            $request->clients["POBCLI"],
            $request->clients["CPOCLI"],
            $request->clients["PROCLI"],
            $request->clients["TELCLI"],
            $request->clients["AGECLI"],
            $request->clients["FPACLI"],
            $request->clients["TARCLI"],
            $request->clients["TCLCLI"],
            $request->clients["FALCLI"],
            $request->clients["NVCCLI"],
            $request->clients["PAICLI"],
            $request->clients["DOCCLI"],
            $request->clients["FUMCLI"]    
        ]);    
        return $client;
    }
}
