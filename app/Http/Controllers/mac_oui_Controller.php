<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\mac_oui;
use Illuminate\Support\Facades\Validator;


class mac_oui_Controller extends Controller
{
    public function GetAllVendors()
    {
        return mac_oui::all();
    }

    public function GetVendorByMacAddress()
    {
        if($this->IsValidMacAddress(request('macAddress'))) {
            $macAddressOUI = $this->GetOUIFromMacAddress(request('macAddress'));
            $vendor = mac_oui::where('oui', $macAddressOUI)->get();
            return [
                'mac address' => request('macAddress'),
                'vendor' => $vendor,
                'message' => null
            ];
        }
        else
        {
            return [
                'mac address' => request('macAddress'),
                'vendor' => null,
                'message' => 'Please supply a valid mac address'

            ];
        }
    }

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
        return $vendorArray;
    }

    private function IsValidMacAddress($macAddress): bool|int
    {
        $patternToMatch1 = '/^([0-9a-f]{2}[-:]){5}[0-9a-f]{2}$/i';
        $patternToMatch2 = '/^([0-9a-f]{4}[.]){2}[0-9a-f]{4}$/i';
        $patternToMatch3 = '/^([0-9a-f]){12}$/i';
        return preg_match($patternToMatch1, $macAddress) || preg_match($patternToMatch2, $macAddress) || preg_match($patternToMatch3, $macAddress);
    }

    private function GetOUIFromMacAddress($macAddress): string
    {
        $separators = array(".", ":", "-");
        $macAddressNoSeparators = str_replace($separators, "",$macAddress);
        return substr($macAddressNoSeparators, 0, 6);
    }
}
