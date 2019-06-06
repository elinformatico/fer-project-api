<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;

class FinancialLog extends Controller
{
    public function saveFinancialLog(){

        try {

            DB::table('financial_log')->insert(
                [
                    'log_user_id'       => $_REQUEST['log_user_id'],
                    'log_description' => $_REQUEST['log_description'],
                    'log_amount'        => $_REQUEST['log_amount'],

                    # Foreign Keys
                    'log_cat_id_fk'  => $_REQUEST['log_cat_id_fk'],
                    'log_pmt_id_kf'  => $_REQUEST['log_pmt_id_kf'],
                    'log_bank_id_fk'  => $_REQUEST['log_bank_id_fk'],

                    # Date field
                    'log_date'        => DB::raw('NOW()'),
                ]
            );

            return Response()->json(array('status' => 'success', 'msg' => 'El registro fue almacenado exitosamente'));

        } catch(\Illuminate\Database\QueryException $e){
            return Response()->json(array('status' => 'error', 'msg'=>'Hubo un error al registrar el Log de Finanzas','error'=>$e));
        }
    }
}
