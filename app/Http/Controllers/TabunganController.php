<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\QueryBuilderDBF\DBF;
use DateTime;
use Illuminate\Http\Request;

use function PHPUnit\Framework\isEmpty;

class TabunganController extends Controller
{
    public function dataNasabah(Request $request)
    {
        $datetime = date('YmdHis');

        try {
            $vaData = DBF::table('mst_tab as t', 'MpayDsn')
                ->select('t.NO_REK', 't.NAMA', 't.ALAMAT', 't.SALDO_AKHR')
                ->get();

            if (!empty($vaData)) {
                return response()->json([
                    "status" => Controller::$status['SUKSES'],
                    "message" => "SUCCESS",
                    "response_time" => $datetime,
                    "data" => array_values($vaData),
                ]);
            } else {
                return response()->json([
                    'status' => self::$status['BAD_REQUEST'],
                    'message' => 'DATA TIDAK ADA',
                    "response_time" => $datetime,
                    "data" => [],
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

    public function cariDataNasabah(Request $request)
    {
        $orderdata = $request->orderdata;
        // $orderdata =  $request->get('orderdata');
        // return $request;
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
                    "nama",
                ];

                $data = $decodedPayload['payload'];
                $dataCount = count($data);
                $bodyCount = count($bodyValid);
                if ($dataCount > $bodyCount || $dataCount < $bodyCount) {
                    return response()->json(['status' => self::$status['BAD_REQUEST'], 'message' => 'INVALID BODY', "response_time" => $datetime], 400);
                }

                $nama = $data['nama'];

                $vaData = DBF::table('', 'MpayDsn')
                    ->raw("SELECT t.NO_REK, t.NAMA, t.ALAMAT, t.SALDO_AKHR FROM mst_tab AS t WHERE t.NO_REK LIKE '%$nama%' OR t.NAMA LIKE '%$nama%'")
                    ->limit(100)
                    ->get();


                if (!empty($vaData)) {
                    return response()->json([
                        "status" => Controller::$status['SUKSES'],
                        "message" => "SUCCESS",
                        "response_time" => $datetime,
                        "data" => array_values($vaData),
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
                'message' => 'REQUEST TIDAK VALID' . $th->getMessage(),
                "response_time" => $datetime
            ], 400);
        }
    }
    public function cekSaldoNasabah(Request $request)
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
                    "rekening",
                ];

                $data = $decodedPayload['payload'];
                $dataCount = count($data);
                $bodyCount = count($bodyValid);
                if ($dataCount > $bodyCount || $dataCount < $bodyCount) {
                    return response()->json(['status' => self::$status['BAD_REQUEST'], 'message' => 'INVALID BODY', "response_time" => $datetime], 400);
                }

                $rekening = $data['rekening'];

                $vaData = DBF::table('mst_tab as t', 'MpayDsn')
                    ->select('t.NO_REK', 't.NAMA', 't.ALAMAT', 't.SALDO_AKHR')
                    ->where('t.NO_REK', '=', $rekening)
                    ->limit(100)
                    ->get();

                if (!empty($vaData)) {
                    return response()->json([
                        "status" => Controller::$status['SUKSES'],
                        "message" => "SUCCESS",
                        "response_time" => $datetime,
                        "data" => array_values($vaData),
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
                'message' => 'REQUEST TIDAK VALID' . $th->getMessage(),
                "response_time" => $datetime
            ], 400);
        }
    }

    public function insertMutasiTab(Request $request)
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
                    "username",
                    "cabang",
                    "kodeao",
                    "rekening",
                    "keterangan",
                    "nominal",
                    "dk",
                    "tgltrx",
                    "tgltran",
                    "sandi"
                ];

                $data = $decodedPayload['payload'];
                $dataCount = count($data);
                $bodyCount = count($bodyValid);
                if ($dataCount > $bodyCount || $dataCount < $bodyCount) {
                    return response()->json(['status' => self::$status['BAD_REQUEST'], 'message' => 'INVALID BODY', "response_time" => $datetime], 400);
                }

                $rekening = DBF::table('mst_tab', 'MpayDsn')
                    ->where('no_rek', '=', $data['rekening'])
                    ->first();

                if (empty($rekening)) {
                    return response()->json([
                        'status' => self::$status['BAD_REQUEST'],
                        'message' => 'NO REKENING TIDAK ADA',
                        "response_time" => $datetime
                    ], 400);
                }

                // Mengambil data saldo  dan tujuan
                $rec = DBF::table('TRN_TAB', 'MpayDsn')
                    ->select(
                        "SUM(IIF(D_K = 'K', JUMLAH, 0)) AS KREDIT",
                        "SUM(IIF(D_K = 'D', JUMLAH, 0)) AS DEBET",
                        "NO_REK"
                    )
                    ->where('no_rek', '=', $data['rekening'])
                    ->groupBy('no_rek')
                    ->first();

                $saldoAwal = 0;
                if (!empty($rec)) {

                    //mendapatkan saldo awal
                    $saldoAwal = $rec['KREDIT'] - $rec['DEBET'];
                    // Mendapatkan saldo akhir
                    $saldoAkhir = $saldoAwal - $data['nominal'];

                    $faktur = self::getLastFaktur("MT", $data['kodeao']);
                    // Data yang akan diinsert
                    $arrDataTRN =
                        [
                            "tgl_tran" => $data['tgltran'],
                            "bukti_trx" => $faktur,
                            'no_rek' => $data['rekening'],
                            "jumlah" => $data['nominal'],
                            "saldo" => $saldoAkhir,
                            "oprt" => $data['username'],
                            "ao" => $data['kodeao'],
                            "d_k" => $data['dk'],
                            "sandi" => $data['sandi'],
                            "tgl_trx" => $data['tgltrx'],
                        ];

                    $arrDataMST = [
                        'no_rek' => $rec['NO_REK'],
                        'saldo_awal' => $saldoAwal,
                        'saldo_akhr' => $saldoAkhir,
                        'jumlah' => $saldoAkhir,
                    ];
                }


                $create = DBF::table('TRN_TAB', 'MpayDsn')->create($arrDataTRN);

                // dd($create);

                if ($create == "CREATED") {
                    $update = DBF::table('MST_TAB', 'MpayDsn')
                        ->where('no_rek', '=', $rec['NO_REK'])
                        ->update($arrDataMST);

                    $vaRes = [
                        "FAKTUR" => $faktur,
                        "REKENING" => $data['rekening'],
                        "KODETRANSAKSI" => "01",
                        "DK" => $data['dk'],
                        "JUMLAH" => strval($data["nominal"]),
                        "DEBET" => strval($data['nominal']),
                        "KREDIT" => "0",
                        "KETERANGAN" => "Setoran an. " . $rekening['NAMA'] . " [" . $data['rekening'] . "]",
                        "USERNAME" => $data["username"],
                        "DATETIME" => date('YmdHis')
                    ];

                    if ($update == "UPDATED") {
                        return response()->json([
                            "status" => Controller::$status['SUKSES'],
                            "message" => "SUCCESS",
                            "response_time" => $datetime,
                            "data" => $vaRes
                        ]);
                    } else {
                        return response()->json([
                            'status' => self::$status['BAD_REQUEST'],
                            'message' => 'GAGAL UPDATE DATA',
                            "response_time" => $datetime
                        ], 400);
                    }
                } else {
                    return response()->json([
                        'status' => self::$status['BAD_REQUEST'],
                        'message' => 'GAGAL INSERT DATA',
                        "response_time" => $datetime
                    ], 400);
                }
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => self::$status['BAD_REQUEST'],
                'message' => 'REQUEST TIDAK VALID' . $th->getMessage(),
                "response_time" => $datetime
            ], 400);
        }
    }
    public function insertMutasiPenarikanTab(Request $request)
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
                    "username",
                    "cabang",
                    "kodeao",
                    "rekening",
                    "keterangan",
                    "nominal",
                    "dk",
                    "tgltrx",
                    "tgltran",
                    "sandi"
                ];

                $data = $decodedPayload['payload'];
                $dataCount = count($data);
                $bodyCount = count($bodyValid);
                if ($dataCount > $bodyCount || $dataCount < $bodyCount) {
                    return response()->json(['status' => self::$status['BAD_REQUEST'], 'message' => 'INVALID BODY', "response_time" => $datetime], 400);
                }

                $rekening = DBF::table('mst_tab', 'MpayDsn')
                    ->where('no_rek', '=', $data['rekening'])
                    ->first();

                if (empty($rekening)) {
                    return response()->json([
                        'status' => self::$status['BAD_REQUEST'],
                        'message' => 'NO REKENING TIDAK ADA',
                        "response_time" => $datetime
                    ], 400);
                }

                // Mengambil data saldo  dan tujuan
                $rec = DBF::table('TRN_TAB', 'MpayDsn')
                    ->select(
                        "SUM(IIF(D_K = 'K', JUMLAH, 0)) AS KREDIT",
                        "SUM(IIF(D_K = 'D', JUMLAH, 0)) AS DEBET",
                        "NO_REK"
                    )
                    ->where('no_rek', '=', $data['rekening'])
                    ->groupBy('no_rek')
                    ->first();

                $saldoAwal = 0;
                if (!empty($rec)) {

                    //mendapatkan saldo awal
                    $saldoAwal = $rec['KREDIT'] - $rec['DEBET'];
                    // Mendapatkan saldo akhir
                    $saldoAkhir = $saldoAwal - $data['nominal'];

                    $faktur = self::getLastFaktur("MP", $data['kodeao']);
                    // Data yang akan diinsert
                    $arrDataTRN =
                        [
                            "tgl_tran" => $data['tgltran'],
                            "bukti_trx" => $faktur,
                            'no_rek' => $data['rekening'],
                            "jumlah" => $data['nominal'],
                            "saldo" => $saldoAkhir,
                            "oprt" => $data['username'],
                            "ao" => $data['kodeao'],
                            "d_k" => $data['dk'],
                            "sandi" => $data['sandi'],
                            "tgl_trx" => $data['tgltrx'],
                        ];

                    $arrDataMST = [
                        'no_rek' => $rec['NO_REK'],
                        'saldo_awal' => $saldoAwal,
                        'saldo_akhr' => $saldoAkhir,
                        'jumlah' => $saldoAkhir,
                    ];
                }


                $create = DBF::table('TRN_TAB', 'MpayDsn')->create($arrDataTRN);

                // dd($create);

                if ($create == "CREATED") {
                    $update = DBF::table('MST_TAB', 'MpayDsn')
                        ->where('no_rek', '=', $rec['NO_REK'])
                        ->update($arrDataMST);

                    $vaRes = [
                        "FAKTUR" => $faktur,
                        "REKENING" => $data['rekening'],
                        "KODETRANSAKSI" => "01",
                        "DK" => $data['dk'],
                        "JUMLAH" => strval($data["nominal"]),
                        "DEBET" => strval($data['nominal']),
                        "KREDIT" => "0",
                        "KETERANGAN" => "Setoran an. " . $rekening['NAMA'] . " [" . $data['rekening'] . "]",
                        "USERNAME" => $data["username"],
                        "DATETIME" => date('YmdHis')
                    ];


                    if ($update == "UPDATED") {
                        return response()->json([
                            "status" => Controller::$status['SUKSES'],
                            "message" => "SUCCESS",
                            "response_time" => $datetime,
                            "data" => $vaRes
                        ]);
                    } else {
                        return response()->json([
                            'status' => self::$status['BAD_REQUEST'],
                            'message' => 'GAGAL UPDATE DATA',
                            "response_time" => $datetime
                        ], 400);
                    }
                } else {
                    return response()->json([
                        'status' => self::$status['BAD_REQUEST'],
                        'message' => 'GAGAL INSERT DATA',
                        "response_time" => $datetime
                    ], 400);
                }
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => self::$status['BAD_REQUEST'],
                'message' => 'REQUEST TIDAK VALID' . $th->getMessage(),
                "response_time" => $datetime
            ], 400);
        }
    }

    public function laporanSetoranTab(Request $request)
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
                    "tglawal",
                    "tglakhir",
                ];

                $data = $decodedPayload['payload'];
                $dataCount = count($data);
                $bodyCount = count($bodyValid);
                if ($dataCount > $bodyCount || $dataCount < $bodyCount) {
                    return response()->json(['status' => self::$status['BAD_REQUEST'], 'message' => 'INVALID BODY', "response_time" => $datetime], 400);
                }


                $tglawal = $data['tglawal'];
                $tglakhir = $data['tglakhir'];


                $vaData = DBF::table('', 'MpayDsn')
                    ->raw("SELECT m.TGL_TRAN, m.BUKTI_TRX, t.NO_REK, t.NAMA, t.ALAMAT, m.JUMLAH, t.SALDO_AWAL, t.SALDO_AKHR FROM TRN_TAB m LEFT JOIN MST_TAB t ON t.NO_REK = m.NO_REK WHERE (m.BUKTI_TRX LIKE 'MT%' OR m.BUKTI_TRX LIKE 'MP%')")
                    ->where('m.TGL_TRAN', '>=', $tglawal)
                    ->where('m.TGL_TRAN', '<=', $tglakhir)
                    ->get();

                if (!empty($vaData)) {
                    return response()->json([
                        "status" => Controller::$status['SUKSES'],
                        "message" => "SUCCESS",
                        "response_time" => $datetime,
                        "data" => array_values($vaData),
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
                'message' => 'REQUEST TIDAK VALID' . $th->getMessage(),
                "response_time" => $datetime
            ], 400);
        }
    }

    public function totalSetoranTab(Request $request)
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
                    "tglawal",
                    "tglakhir",
                    "username"
                ];

                $data = $decodedPayload['payload'];
                $dataCount = count($data);
                $bodyCount = count($bodyValid);
                if ($dataCount > $bodyCount || $dataCount < $bodyCount) {
                    return response()->json(['status' => self::$status['BAD_REQUEST'], 'message' => 'INVALID BODY', "response_time" => $datetime], 400);
                }


                $tglawal = $data['tglawal'];
                $tglakhir = $data['tglakhir'];


                $vaSetoran = DBF::table('', 'MpayDsn')
                    ->raw("SELECT COUNT(*) AS TOTAL_SETOR, IIF(SUM(`JUMLAH`) IS NULL, 0, SUM(`JUMLAH`)) AS JUMLAH_MUTASI_SETOR FROM TRN_TAB WHERE (BUKTI_TRX LIKE 'MT%' OR BUKTI_TRX LIKE 'MP%')")
                    ->where('D_K', '=', 'K')
                    ->where('OPRT', '=', $data['username'])
                    ->where('TGL_TRAN', '>=', $tglawal)
                    ->where('TGL_TRAN', '<=', $tglakhir)
                    ->get();

                $vaPenarikan = DBF::table('', 'MpayDsn')
                    ->raw("SELECT COUNT(*) AS TOTAL_SETOR, IIF(SUM(`JUMLAH`) IS NULL, 0, SUM(`JUMLAH`)) AS JUMLAH_MUTASI_SETOR FROM TRN_TAB WHERE (BUKTI_TRX LIKE 'MT%' OR BUKTI_TRX LIKE 'MP%')")
                    ->where('D_K', '=', 'D')
                    ->where('OPRT', '=', $data['username'])
                    ->where('TGL_TRAN', '>=', $tglawal)
                    ->where('TGL_TRAN', '<=', $tglakhir)
                    ->get();


                $vaData = [
                    'SETORAN' => $vaSetoran,
                    'PENARIKAN' => $vaPenarikan,
                ];

                if (!empty($vaData)) {
                    return response()->json([
                        "status" => Controller::$status['SUKSES'],
                        "message" => "SUCCESS",
                        "response_time" => $datetime,
                        "data" => $vaData,
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
                'message' => 'REQUEST TIDAK VALID' . $th->getMessage(),
                "response_time" => $datetime
            ], 400);
        }
    }
}
