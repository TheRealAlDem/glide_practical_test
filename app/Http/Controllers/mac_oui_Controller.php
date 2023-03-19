<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\mac_oui;
use Illuminate\Support\Facades\Validator;

class mac_oui_Controller extends Controller
{
    public function GetAllVendors()
    {
        return response(
            mac_oui::all(),
            200
        );

    }

    public function GetVendorByMacAddress()
    {
        $macAddress = request('macAddress');
        if($this->IsValidMacAddress($macAddress)) {
            $macAddressOUI = $this->GetOUIFromMacAddress($macAddress);
            $vendor = mac_oui::where('oui', $macAddressOUI)->get();
            return response([
                'mac address' => $macAddress,
                'vendor' => $vendor,
                'message' => null,
            ], 200);
        }
        else
        {
            return response([
                'mac address' => $macAddress,
                'vendor' => null,
                'message' => 'Please supply a valid mac address'

            ], 400);
        }
    }

    //  second character 2, 6, A or E random
    //  validate the macAddresses object
    //  check each one as we could well have a mix of valid / invalid
    //      and also, so we keep the order of the vendors as they are requested
    public function GetVendorsByMacAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "macAddresses" => "required"
        ]);

        if ($validator->fails()) {
            return response(
                $validator->errors(),
                400
            );
        }

        $vendorArray =[];
        $macAddresses = $request->input('macAddresses');
        foreach ($macAddresses as $macAddress)
        {
            if($this->IsValidMacAddress($macAddress)) {
                $macAddressOUI = $this->GetOUIFromMacAddress($macAddress);
                $vendor = mac_oui::where('oui', $macAddressOUI)->get();
                $vendorArray[] = [
                    'mac address' => $macAddress,
                    'vendor' => $vendor,
                    'message' => null
                ];
            }
            else
            {
                $vendorArray[] = [
                    'mac address' => $macAddress,
                    'vendor' => null,
                    'message' => 'Invalid mac address'
                ];
            }
        }
        return response($vendorArray, 200);
    }

    //  didn't fit nicely into one pattern, so I separated them - easy to read
    private function IsValidMacAddress($macAddress): bool|int
    {
        //  eg, 00-11-22-00-11-A2 or 00:11:22:33:44:55
        $patternToMatch1 = '/^([0-9a-f]{2}[-:]){5}[0-9a-f]{2}$/i';
        //  eg, 0001.1122.2333
        $patternToMatch2 = '/^([0-9a-f]{4}[.]){2}[0-9a-f]{4}$/i';
        //  eg, 061122334455
        $patternToMatch3 = '/^([0-9a-f]){12}$/i';
        return preg_match($patternToMatch1, $macAddress) || preg_match($patternToMatch2, $macAddress) || preg_match($patternToMatch3, $macAddress);
    }

    //  strip out the separators if there are any
    //  return only the portion of the mac address which refers to the Organisationally Unique Identifier
    //      to query
    private function GetOUIFromMacAddress($macAddress): string
    {
        $separators = array(".", ":", "-");
        $macAddressNoSeparators = str_replace($separators, "",$macAddress);
        return substr($macAddressNoSeparators, 0, 6);
    }
}
