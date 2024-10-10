<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\QueryBuilderDBF\DBF;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LaporanController extends Controller
{
    public function rekapDataTransaksi(Request $request)
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
                    ->raw("SELECT COUNT(*) AS TOTAL_SETOR, IIF(SUM(`JUMLAH`) IS NULL, 0, SUM(`JUMLAH`)) AS JUMLAH_MUTASI_SETOR FROM TRN_TAB WHERE BUKTI_TRX LIKE 'MT%'")
                    ->where('D_K', '=', 'K')
                    ->where('OPRT', '=', $data['username'])
                    ->where('TGL_TRAN', '>=', $tglawal)
                    ->where('TGL_TRAN', '<=', $tglakhir)
                    ->first();

                $vaPenarikan = DBF::table('', 'MpayDsn')
                    ->raw("SELECT COUNT(*) AS TOTAL_PENARIKAN, IIF(SUM(`JUMLAH`) IS NULL, 0, SUM(`JUMLAH`)) AS JUMLAH_MUTASI_PENARIKAN FROM TRN_TAB WHERE BUKTI_TRX LIKE 'MP%'")
                    ->where('D_K', '=', 'D')
                    ->where('TGL_TRAN', '>=', $tglawal)
                    ->where('TGL_TRAN', '<=', $tglakhir)
                    ->where('OPRT', '=', $data['username'])
                    ->first();

                $vaAngsuran = DBF::table('TRN_ANGS', 'MpayDsn')
                    ->select(
                        'COUNT(*) as TOTAL_ANGSURAN',
                        'IIF(SUM(`ANGS_POKOK`) IS NOT NULL, SUM(`ANGS_POKOK`), 0) as JUMLAH_POKOK',
                        'IIF(SUM(`ANGS_MRGIN`) IS NOT NULL, SUM(`ANGS_MRGIN`), 0) as JUMLAH_BUNGA',
                        'IIF(SUM(`DENDA`) IS NOT NULL, SUM(`DENDA`), 0) as JUMLAH_DENDA'
                    )
                    ->where('TGL_ANGSUR', '>=', $tglawal)
                    ->where('TGL_ANGSUR', '<=', $tglakhir)
                    ->where('OPR', '=', $data['username'])
                    ->where('BUKTI_TRX', 'LIKE', 'MA%')
                    ->first();

                $totalSetoran = $vaSetoran['TOTAL_SETOR'];
                $jumlahMutasiSetoran = $vaSetoran['JUMLAH_MUTASI_SETOR'];

                $totalPenarikan = $vaPenarikan['TOTAL_PENARIKAN'];
                $jumlahMutasiPenarikan = $vaPenarikan['JUMLAH_MUTASI_PENARIKAN'];

                $totalAngsuran = $vaAngsuran['TOTAL_ANGSURAN'];
                $jumlahPokok = $vaAngsuran['JUMLAH_POKOK'];
                $jumlahBunga = $vaAngsuran['JUMLAH_BUNGA'];
                $jumlahDenda = $vaAngsuran['JUMLAH_DENDA'];
                $jumlahAngsuran = $jumlahPokok + $jumlahBunga + $jumlahDenda;

                $totalTrx = $totalSetoran + $totalPenarikan + $totalAngsuran;
                $totalTrxRp = $jumlahMutasiSetoran + $jumlahMutasiPenarikan + $jumlahAngsuran;

                $vaData = [
                    [
                        "TOTAL_SETOR" => strval(intval($totalSetoran)),
                        "JUMLAH_MUTASI_SETOR" => strval(intval($jumlahMutasiSetoran)),
                        "TOTAL_PENARIKAN" => strval(intval($totalPenarikan)),
                        "JUMLAH_MUTASI_PENARIKAN" => strval(intval($jumlahMutasiPenarikan)),
                        "TOTAL_ANGSURAN" => strval(intval($totalAngsuran)),
                        "JUMLAH_POKOK" => strval(intval($jumlahPokok)),
                        "JUMLAH_BUNGA" => strval(intval($jumlahBunga)),
                        "JUMLAH_DENDA" => strval(intval($jumlahDenda)),
                        "JUMLAH_ANGSURAN" => strval(intval($jumlahAngsuran)),
                        "TOTAL_TRX" => strval(intval($totalTrx)),
                        "TOTAL_TRXRP" => strval(intval($totalTrxRp)),
                        //"jumlahTransaksi" => strval(intval($val->jumlahMutasiSetoran - $val->jumlahMutasiPenarikan)),
                    ]
                ];

                // dd($vaData);

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
    public function laporanAngsuranKredit(Request $request)
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

                $vaLend = DBF::table('mst_lend as d', 'MpayDsn')
                    ->select(
                        'a.TGL_ANGSUR',
                        'a.BUKTI_TRX',
                        'd.NO_AKAD',
                        'd.NAMA',
                        'd.ALAMAT',
                        'd.ANG_POKOK',
                        'd.ANG_MARGIN',
                        'IIF(a.DENDA IS NOT NULL, a.DENDA, 0) AS DENDA',
                        'd.PLAFON'
                    )
                    ->leftJoin('trn_angs as a', 'd.NO_AKAD', 'a.NO_AKAD')
                    ->where('a.OPR', '=', $data['username'])
                    ->where('a.TGL_ANGSUR', '>=', $tglawal)
                    ->where('a.TGL_ANGSUR', '<=', $tglakhir)
                    ->limit(100)
                    ->get();

                if (!empty($vaLend)) {
                    foreach ($vaLend as $val) {
                        // dd();
                        $cRekening = $val['NO_AKAD'];
                        $nBakiDebet = 0;

                        $data
                            = DBF::table('MST_LEND as d', 'MpayDsn')
                            ->select(
                                'd.POKOK - (SUM(a.ANGS_POKOK)) AS bakidebet',
                            )
                            ->leftJoin('TRN_ANGS as a', 'd.NO_AKAD', 'a.NO_AKAD')
                            ->where('d.NO_AKAD', '=', $cRekening)
                            ->groupBy('d.POKOK', 'd.NO_AKAD')
                            ->first();

                        if ($data) {
                            $nBakiDebet = $data['bakidebet'];
                        }

                        // dd($vaLend);

                        $vaData = [
                            [
                                "tgl" => Carbon::parse($val['TGL_ANGSUR'])->format('d-m-Y'),
                                "faktur" => $val['BUKTI_TRX'],
                                "rekening" => $cRekening,
                                "nama" => $val['NAMA'],
                                "alamat" => $val['ALAMAT'],
                                "keterangan" => '',
                                // "keterangan" => $val->keterangan,
                                "pokok" => strval(intval($val['ANG_POKOK'])),
                                "bunga" => strval(intval($val['ANG_MARGIN'])),
                                "denda" => strval(intval($val['DENDA'])),
                                "totalangsuran" => strval(intval($val['ANG_POKOK'] + $val['ANG_MARGIN'])),
                                "plafond" => strval(intval($val['PLAFON'])),
                                "bakidebet" => strval(intval($nBakiDebet))
                                //"jumlahTransaksi" => strval(intval($val->jumlahMutasiSetoran - $val->jumlahMutasiPenarikan)),
                            ]
                        ];
                    }

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
    public function totalAngsuranKredit(Request $request)
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

                $vaLend = DBF::table('trn_angs as d', 'MpayDsn')
                    ->select(
                        'COUNT(*) AS totalAngsuran',
                        'IIF(SUM(ANGS_POKOK) IS NOT NULL, SUM(ANGS_POKOK), 0) as jumlahPokok',
                        'IIF(SUM(ANGS_MRGIN) IS NOT NULL, SUM(ANGS_MRGIN), 0) as jumlahBunga',
                        'IIF(SUM(DENDA) IS NOT NULL, SUM(DENDA), 0) as jumlahDenda',
                    )
                    ->where('OPR', '=', $data['username'])
                    ->where('TGL_ANGSUR', '>=', $tglawal)
                    ->where('TGL_ANGSUR', '<=', $tglakhir)
                    ->where('BUKTI_TRX', 'LIKE', 'MA%')
                    ->get();

                if (!empty($vaLend)) {
                    $vaData = [];
                    foreach ($vaLend as $val) {
                        $data = [
                            "TOTAL_ANGSURAN" => strval(intval($val['totalAngsuran'])),
                            "JUMLAH_POKOK" => strval(intval($val['jumlahPokok'])),
                            "JUMLAH_BUNGA" => strval(intval($val['jumlahBunga'])),
                            "JUMLAH_DENDA" => strval(intval($val['jumlahDenda'])),
                            "JUMLAH_ANGSURAN" => strval(intval($val['jumlahPokok'] + $val['jumlahBunga'] + $val['jumlahDenda'])),
                        ];
                        array_push($vaData, $data);
                    }

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
