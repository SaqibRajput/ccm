<?php

/**
 * Developed by Saqib Rajput.
 * Email: rajput.saqib@hotmail.com
 * Mobile: 00-92-300-6710419
 * Date: 20/01/2022
 * Time: 6:41 PM
 */

namespace CCM\Leads\Controllers;

use CCM\Leads\Controllers\Controller as LeadsController; 
use Illuminate\Support\Facades\DB; 
use Throwable;

class RiversandController extends LeadsController { 
  

    function __construct() {
         
    }


    /**
     * Get Addons data from Riversand MSSQL
     * @param (string) $tableName
     * @return array
     */
    function getDataFromRiverSand($tableName)
    {
        //set default respone
        $result = [ 'status' => 1, 'data' => [], 'message' => 'success'];
        try{
            //get data of request table from RiverSand
            \Log::info('start query on RIVERSAND_DATABASE:'.$tableName.' - '.date('d-m-Y H:i:s'));
            $result['data'] = DB::connection(env('DB_RIVERSAND_CONNECTION'))->table(env('DB_RIVERSAND_DATABASE').'.dbo.'.$tableName)->get();
            \Log::info('end query on RIVERSAND_DATABASE:'.$tableName.' - '.date('d-m-Y H:i:s'));
        }
        catch (\Illuminate\Database\QueryException $qe)
        {
            $result['status']  = 0;
            $result['message'] = $qe->getMessage();
        }
        catch (\Exception $e)
        {
            $result['status']  = 0;
            $result['message'] = $e->getMessage();
        }
        catch (\Throwable $t)
        {
            $result['status']  = 0;
            $result['message'] = $t->getMessage();
        }

        return $result;
    }
    

    
     

}
