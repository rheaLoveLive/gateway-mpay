<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\QueryBuilderDBF\DBF;
use Illuminate\Http\Request;

class AnggotaController extends Controller
{
    public function getAllAnggota(Request $request)
    {
        $datetime = date("YmdHis");
        try {
            $data = DBF::table('anggota', 'MpayDsn')
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

    public function checkAnggota(Request $request)
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
                    "nik",
                    "nohp",
                ];

                $data = $decodedPayload['payload'];
                $dataCount = count($data);
                $bodyCount = count($bodyValid);
                if ($dataCount > $bodyCount || $dataCount < $bodyCount) {
                    return response()->json(['status' => self::$status['BAD_REQUEST'], 'message' => 'INVALID BODY', "response_time" => $datetime], 400);
                }
            }

            $userAnggota = DBF::table('anggota', 'MpayDsn')
                ->where('no_telp', '=', $data['nohp'])
                ->where('no_id', '=', $data['nik'])
                ->get();

            if (!empty($userAnggota)) {
                return response()->json([
                    "status" => Controller::$status['SUKSES'],
                    "message" => "SUCCESS",
                    "response_time" => $datetime,
                    "data" => $userAnggota,
                ]);
            } else {
                return response()->json([
                    'status' => self::$status['BAD_REQUEST'],
                    'message' => 'DATA TIDAK ADA',
                    "data" => [],
                    "response_time" => $datetime
                ], 400);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => self::$status['BAD_REQUEST'],
                'message' => 'REQUEST TIDAK VALID' . $th->getMessage(),
                "response_time" => $datetime
            ], 400);
        }
    }

    public function checkAsCif(Request $request)
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
                    "nik",
                    "cif",
                ];

                $data = $decodedPayload['payload'];
                $dataCount = count($data);
                $bodyCount = count($bodyValid);
                if ($dataCount > $bodyCount || $dataCount < $bodyCount) {
                    return response()->json([
                        'status' => self::$status['BAD_REQUEST'],
                        'message' => 'INVALID BODY',
                        "response_time" => $datetime
                    ], 400);
                }
            }

            $userAnggota = DBF::table('anggota', 'MpayDsn')
                ->where('no_agt', '=', $data['cif'])
                ->where('no_id', '=', $data['nik'])
                ->get();

            return response()->json([
                "status" => Controller::$status['SUKSES'],
                "message" => "SUCCESS",
                "response_time" => $datetime,
                "data" => $userAnggota,
            ]);

            if (!empty($userAnggota)) {
            } else {
                return response()->json([
                    'status' => self::$status['BAD_REQUEST'],
                    'message' => 'DATA TIDAK ADA',
                    "response_time" => $datetime
                ], 400);
            }
        } catch (\Throwable $th) {
            // dd($th);
            return response()->json([
                'status' => self::$status['BAD_REQUEST'],
                'message' => 'REQUEST TIDAK VALID ' . $th->getMessage(),
                "response_time" => $datetime
            ], 400);
        }
    }
}
