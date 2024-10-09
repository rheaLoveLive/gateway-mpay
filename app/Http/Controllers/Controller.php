<?php

namespace App\Http\Controllers;

use App\QueryBuilderDBF\DBF;
use XBase\TableReader;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected static $status = [
        'SUKSES' => '00',
        'GAGAL' => '01',
        'PENDING' => '02',
        'NOT_FOUND' => '401',
        'BAD_REQUEST' => '400'
    ];

    public function getLastFaktur($ckey, $kodeao)
    {
        $tgl = now()->format('Y-m-d');
        $ckey = $ckey . $kodeao . now()->format('Ymd');
        $urut = 0;

        $data = DBF::table('TRN_TAB', 'MpayDsn')
            ->select('COUNT(*) as urut')
            ->where('TGL_TRAN', '=', $tgl)
            ->where('BUKTI_TRX', 'like', $ckey . '%')
            ->first();

        // return $data->urut;
        if ($data) {
            $urut = $data['urut'] + 1;
            $retval = $ckey . str_pad($urut, 4, "0", STR_PAD_LEFT);
            return $retval;
        } else {
            // Gantilah dengan respons yang sesuai jika data tidak ditemukan
            return null;
        }
    }

    public function getLastFakturAngsuran($ckey, $kodeao)
    {
        $tgl = now()->format('Y-m-d');
        $ckey = $ckey . $kodeao . now()->format('Ymd');
        $urut = 0;

        $data = DBF::table('TRN_ANGSUR', 'MpayDsn')
            ->select('COUNT(*) as urut')
            ->where('TGL_ANGSUR', '=', $tgl)
            ->where('BUKTI_TRX', 'like', $ckey . '%')
            ->first();

        if ($data) {
            $urut = $data['urut'] + 1;
            $retval = $ckey . str_pad($urut, 4, "0", STR_PAD_LEFT);
            return $retval;
        } else {
            // Gantilah dengan respons yang sesuai jika data tidak ditemukan
            return null;
        }
    }
}
