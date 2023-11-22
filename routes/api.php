<?php

use App\Http\Controllers\Api\AlJouaiRequests;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

function desiredFormat($docEntry)
{
    // Get the DocEntry and Return the Desired Format Needed For the Invoice 
    $dateAsKey  = null;   // ^ This is the Date We Get from the Invoice and use it as key
    $finalCombination  = [];
    $invoiceNumber = $docEntry;
    $newArrayForDate = [];
    $datesWholeObject  = AlJouaiRequests::getInvoiceDatesOnly($invoiceNumber);
    $newArrayForDate[] = [
        'Total' => AlJouaiRequests::getInvoiceDocTotal($invoiceNumber),
        'NumberOfItems' => AlJouaiRequests::getCountOfNumbers($invoiceNumber),
        'Dates' => $datesWholeObject,
    ];
    $dateAsKey = $datesWholeObject['DocDate'];
    $finalCombination[$dateAsKey][$invoiceNumber] = $newArrayForDate;
    return $finalCombination;
} // ! Done and Generic For any Invoice 

Route::post('/login', function (Request $request) {
    $jsonData = $request->json()->all();
    $userPhone = $jsonData['phone'];
    $userPass = $jsonData['password'];
    if (Auth::attempt(['phone' => $userPhone, 'password' => $userPass])) {
        $user = Auth::user();
        return response()->json(1);
    }
    return response()->json(0, 200);
});

Route::post('/register-user', function (Request $request) {
    $jsonData = $request->json()->all();
    $userPass = $jsonData['password'];
    $userPhone = $jsonData['phone'];
    $existUser  = User::where('phone', $userPhone)->first();
    if ($existUser) {
        return response()->json(0);
    } else {
        $registeredUser  = new User();
        $registeredUser->phone = $userPhone;
        $registeredUser->password = Hash::make($userPass);
        $registeredUser->save();
        return response()->json(1);
    }
});

Route::post('/verify', function (Request $request) {
    $jsonData = $request->json()->all();
    $userPhone = $jsonData['phone'];
    $userInvoice  = $jsonData['invoice'];
    $userInvoice = (string) $userInvoice;
    $entriesArray  = AlJouaiRequests::getAllCustomerDocEntries($userPhone);
    if (in_array($userInvoice, $entriesArray)) {
        return response()->json([
            "res" => 1
        ]);
    } else {
        return response()->json([
            "res" => 0
        ]);
    }
});

// TODO change doc entry to doc number 
Route::get('/invoice/{docEntry}', function (Request $request) {
    $invoiceNumber = $request->docEntry;
    $invoiceData  = AlJouaiRequests::getSingleInvoiceTotalData($invoiceNumber);
    return response()->json($invoiceData);
});

Route::get('/f/invoice/{docEntry}', function (Request $request) {
    $result  = desiredFormat($request->docEntry);
    return response()->json($result);
});

Route::get('/get-last-five/{phoneNumber}', function (Request $request) {
    $inputPhoneNumber = $request->phoneNumber;
    $result  = AlJouaiRequests::getLastFiveInvoices($inputPhoneNumber);
    return response()->json($result);
});

Route::post("/sql-within-range", function (Request $request) {
    $jsonData = $request->json()->all();
    $start = $jsonData['startdate'];
    $phone = $jsonData['phone'];
    $end = $jsonData['enddate'];
    $invoiceData  = AlJouaiRequests::SQL_get_data_range($phone, $start, $end);
    return response()->json($invoiceData);
});

Route::post("/sql-specific-date", function (Request $request) {
    $jsonData = $request->json()->all();
    $specificDate  = $jsonData['date'];
    $userPhone  = $jsonData['phone'];
    $invoiceData  = AlJouaiRequests::SQL_get_data_specific_date($userPhone, $specificDate);
    return response()->json($invoiceData);
});

Route::get('/get-version', function (Request $request) {
    $asps = UserSetting::first();
    if ($asps) {
        return response()->json($asps->app_version);
    } else {
        return response()->json(null); // Return null as JSON
    }
});

Route::post('/post-version', function (Request $request) {
    $jsonData = $request->json()->all();
    $version = $jsonData['version'];
    $asps = UserSetting::first();
    if ($asps) {
        $asps->app_version = $version;
        $asps->save();
    } else {
        $asps = new UserSetting();
        $asps->app_version = $version;
        $asps->save();
    }
    return response()->json($asps);
});


/**
 * TODO : 
 * Get the missing Invoices
 * App Will send list Of Doc Entries It has 
 * and API respond with missing Invoices 
 */


Route::post('/get-missing', function (Request $request) {
    $jsonData = $request->json()->all();
    $existingData = $jsonData['exist'];
    $userPhone = $jsonData['Phone'];
    // $userPhone  = "0505131036";
    $listAsString = implode(',', $existingData);
    $result  = AlJouaiRequests::SQL_get_missing_data($userPhone, $listAsString);
    return response()->json($result);
});
