<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\QueryBuilderDBF\DBF;
use Illuminate\Http\Request;

class DepositoController extends Controller
{
    public function getAllDepo(Request $request)
    {
        $orderdata = $request->orderdata;
        $datetime = date("YmdHis");

        try {

            $data = DBF::table('dmasd', 'MpayDsn')
                ->select(['*'])->get();

            if (!empty($data)) {
                $response = [
                    "status" => Controller::$status['SUKSES'],
                    "message" => "SUCCESS",
                    "response_time" => $datetime,
                    "data" => $data,
                ];
                return $response;
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => self::$status['BAD_REQUEST'], 'message' => 'REQUEST TIDAK VALID' . $th->getMessage(), "response_time" => $datetime], 400);
        }
    }

    public function rekeningDepo(Request $request)
    {
        $orderdata = $request->orderdata;
        $datetime = date('YmdHis');
        try {

            $decodedPayloadJwt = JWT::decode($orderdata, new Key(env('TOKEN_KEY'), 'HS512'));
            $decodedPayload = [
                'status' => '00',
                'error' => null,
                'payload' => (array) $decodedPayloadJwt,
            ];


            if ($decodedPayload['status'] == '00') {
                $bodyValid = [
                    "kodeanggota",
                ];

                $data = $decodedPayload['payload'];
                $dataCount = count($data);
                $bodyCount = count($bodyValid);
                if ($dataCount > $bodyCount || $dataCount < $bodyCount) {
                    return response()->json(['status' => self::$status['BAD_REQUEST'], 'message' => 'INVALID BODY', "response_time" => $datetime], 400);
                }

                $rekening = DBF::table('dmasd', 'MpayDsn')
                    ->where('no_agt', '=', $data['kodeanggota'])
                    ->get();

                if (!empty($rekening)) {

                    return response()->json([
                        "status" => Controller::$status['SUKSES'],
                        "message" => "SUCCESS",
                        "response_time" => $datetime,
                        "data" => array_values($rekening),
                    ]);
                } else {
                    return response()->json([
                        'status' => self::$status['BAD_REQUEST'],
                        'message' => 'DATA TIDAK ADA',
                        "response_time" => $datetime,
                        "data" => [],
                    ], 400);
                }
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => self::$status['BAD_REQUEST'],
                'message' => $data,
                "response_time" => $datetime
            ], 400);
        }
    }
}
