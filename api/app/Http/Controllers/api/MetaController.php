<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Meta;

class MetaController extends Controller
{
    public function index()
    {
        $meta = Meta::findOrFail(0);

        return response()->json($meta);
    }

    public function update(Request $request)
    {
        // code to handle the update request

        $maintenance_on = filter_var($request->get('maintenance_on'), FILTER_VALIDATE_BOOLEAN);
        $maintenance_enddate = $request->get('maintenance_enddate');
        $maintenance_message = $request->get('maintenance_message');
        $meta = Meta::findOrFail(0);
        $meta->maintenance_on = $maintenance_on;
        $meta->maintenance_enddate = $maintenance_enddate;
        $meta->maintenance_message = $maintenance_message;
        $meta->save();

        $xml = simplexml_load_file("D:\inetpub\MPortal\web.config");
        $rule = $xml->xpath("//rule[@name='Maintenance']")[0];
        $rule['enabled'] = $maintenance_on?"true":"false";
        $rule2 = $xml->xpath("//rule[@name='ReverseMaintenance']")[0];
        $rule2['enabled'] = $maintenance_on?"false":"true";
        file_put_contents("D:\inetpub\MPortal\web.config", $xml->asXML());
        
        return response()->json($meta);
    }

}
