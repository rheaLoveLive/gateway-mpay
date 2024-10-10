<?php

namespace App\Http\Controllers;

use App\QueryBuilderDBF\DBF;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

class DebiturController extends Controller
{
    public function dataDebitur(Request $request)
    {
        $datetime = date('YmdHis');
        try {
            $vaData = DBF::table('mst_lend as d', 'MpayDsn')
                ->select(
                    "d.NO_AKAD",
                    "d.NAMA",
                    "d.PLAFON",
                    "d.ANG_POKOK",
                    "d.ANG_MARGIN",
                    "d.ALAMAT",
                    "d.POKOK - SUM(IIF(a.ANGS_POKOK IS NOT NULL, a.ANGS_POKOK,0)) as BAKIDEBET",
                    "d.MRGIN - SUM(IIF(a.ANGS_MRGIN IS NOT NULL, a.ANGS_MRGIN, 0)) as BAKIDEBETMARGIN"
                )
                ->leftJoin('trn_angs as a', 'a.NO_AKAD', 'd.NO_AKAD')
                ->groupBy("d.NO_AKAD, d.NAMA, d.PLAFON, d.ANG_POKOK, d.ANG_MARGIN, d.ALAMAT, d.POKOK, d.MRGIN")
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
        } catch (\Throwable $th) {
            return response()->json([
                'status' => self::$status['BAD_REQUEST'],
                'message' => 'REQUEST TIDAK VALID' . $th->getMessage(),
                "response_time" => $datetime
            ], 400);
        }
    }

    public function cariDataDebitur(Request $request)
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
                    ->raw("SELECT d.NO_AKAD, d.NAMA, d.PLAFON, d.ANG_POKOK, d.ANG_MARGIN, d.ALAMAT, d.POKOK - SUM(IIF(a.ANGS_POKOK IS NOT NULL, a.ANGS_POKOK,0)) as BAKIDEBET, d.MRGIN - SUM(IIF(a.ANGS_MRGIN IS NOT NULL, a.ANGS_MRGIN, 0)) as BAKIDEBETMARGIN FROM mst_lend as d LEFT JOIN trn_angs as a ON a.NO_AKAD = d.NO_AKAD WHERE d.NO_AKAD LIKE '%$nama%' OR d.NAMA LIKE '%$nama%' GROUP BY d.NO_AKAD, d.NAMA, d.PLAFON, d.ANG_POKOK, d.ANG_MARGIN, d.ALAMAT, d.POKOK, d.MRGIN")
                    // ->select(
                    //     "d.NO_AKAD",
                    //     "d.NAMA",
                    //     "d.PLAFON",
                    //     "d.ANG_POKOK",
                    //     "d.ANG_MARGIN",
                    //     "d.ALAMAT",
                    //     "d.POKOK - SUM(IIF(a.ANGS_POKOK IS NOT NULL, a.ANGS_POKOK,0)) as BAKIDEBET",
                    //     "d.MRGIN - SUM(IIF(a.ANGS_MRGIN IS NOT NULL, a.ANGS_MRGIN, 0)) as BAKIDEBETMARGIN"
                    // )
                    // ->leftJoin('trn_angs as a', 'a.NO_AKAD', 'd.NO_AKAD')
                    // ->where("d.NO_AKAD", "LIKE", "%$nama%")
                    // ->groupBy("d.NO_AKAD, d.NAMA, d.PLAFON, d.ANG_POKOK, d.ANG_MARGIN, d.ALAMAT, d.POKOK, d.MRGIN")
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

    public function cekTagihanNasabah(Request $request)
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
                    "nama",
                ];

                $data = $decodedPayload['payload'];
                $dataCount = count($data);
                $bodyCount = count($bodyValid);
                if ($dataCount > $bodyCount || $dataCount < $bodyCount) {
                    return response()->json(['status' => self::$status['BAD_REQUEST'], 'message' => 'INVALID BODY', "response_time" => $datetime], 400);
                }

                $rekening = $data['rekening'];
                $currentMonth = Carbon::now()->month;
                $currentYear = Carbon::now()->year;

                $vaData = DBF::table('mst_lend', 'MpayDsn')
                    ->select(
                        'NO_AKAD',
                        'NAMA',
                        'ALAMAT',
                        'ANG_POKOK',
                        'ANG_MARGIN'
                    )
                    ->where('NO_AKAD', '=', $rekening)
                    ->first();

                if (!empty($vaData)) {
                    $nAngsuranPokok = $vaData['ANG_POKOK'];
                    $nAngsuranBunga = $vaData['ANG_MARGIN'];
                    $dTglAngsur = null;
                    $vaTagihan = DBF::table('', 'MpayDsn')
                        ->raw("SELECT t.TGL_ANGSUR, t.ANGS_POKOK, t.ANGS_MRGIN FROM TRN_ANGS AS t WHERE t.NO_AKAD = '$rekening' AND MONTH(t.TGL_ANGSUR) = '$currentMonth' AND YEAR(t.TGL_ANGSUR) = '$currentYear'")
                        ->get();
                    foreach ($vaTagihan as $data) {
                        $dTglAngsur = $data['TGL_ANGSUR'];
                        $nTunggakanPokok = $data['ANGS_POKOK'];
                        $nTunggakanBunga = $data['ANGS_MRGIN'];
                    }

                    $nTagihanPokok = $nAngsuranPokok - $nTunggakanPokok;
                    $nTagihanBunga = $nAngsuranBunga - $nTunggakanBunga;

                    $vaResult = [
                        "REKENING" => $vaData['NO_AKAD'],
                        "NAMA" => $vaData['NAMA'],
                        "ALAMAT" => $vaData['ALAMAT'],
                        "TGL_ANGSUR" => $dTglAngsur,
                        "TUNGGAKAN_POKOK" => strval(intval($nTagihanPokok)),
                        "TUNGGAKAN_BUNGA" => strval(intval($nTagihanBunga))
                    ];

                    return response()->json([
                        "status" => Controller::$status['SUKSES'],
                        "message" => "SUCCESS",
                        "response_time" => $datetime,
                        "data" => [$vaResult]
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

    public function insertMutasiAngsuran(Request $request)
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
                    "pokok",
                    "bunga",
                    "denda",
                ];

                $data = $decodedPayload['payload'];
                $dataCount = count($data);
                $bodyCount = count($bodyValid);
                if ($dataCount > $bodyCount || $dataCount < $bodyCount) {
                    return response()->json(['status' => self::$status['BAD_REQUEST'], 'message' => 'INVALID BODY', "response_time" => $datetime], 400);
                }

                $rekening = DBF::table('trn_angs', 'MpayDsn')
                    ->where('NO_AKAD', '=', $data['rekening'])
                    ->first();

                if (empty($rekening)) {
                    return response()->json([
                        'status' => self::$status['BAD_REQUEST'],
                        'message' => 'NO. AKAD TIDAK ADA',
                        "response_time" => $datetime
                    ], 400);
                }
                $faktur = self::getLastFaktur("MA", $data['kodeao']);
                $vaArray = [
                    'TGL_ANGSUR' => Carbon::now()->format('d/m/Y'),
                    'AO' => $data['kodeao'],
                    'BUKTI_TRX' => $faktur,
                    'NO_AKAD' => $data['rekening'],
                    'ANGS_POKOK' => $data['pokok'],
                    'ANGS_MRGIN' => $data['bunga'],
                    'DENDA' => $data['denda'],
                    'OPR' => $data['username'],
                    'TGL_TRX' => Carbon::now()->format('d/m/Y H:i:s')
                ];
            }
            $vaCreate = DBF::table('trn_angs', 'MpayDsn')->create($vaArray);

            if ($vaCreate == "CREATED") {
                $vaRes = [
                    'TGL_ANGSUR' => Carbon::now()->format('Y-m-d'),
                    'AO' => $data['kodeao'],
                    'BUKTI_TRX' => $faktur,
                    'NO_AKAD' => $data['rekening'],
                    'ANGS_POKOK' => $data['pokok'],
                    'ANGS_MRGIN' => $data['bunga'],
                    'DENDA' => $data['denda'],
                    'OPR' => $data['username'],
                    'TGL_TRX' => Carbon::now()->format('Y-m-d H:i:s')
                ];
                return response()->json([
                    "status" => Controller::$status['SUKSES'],
                    "message" => "SUCCESS",
                    "response_time" => $datetime,
                    "data" => $vaRes
                ]);
            } else {
                return response()->json([
                    'status' => self::$status['BAD_REQUEST'],
                    'message' => 'GAGAL INSERT DATA',
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
}
