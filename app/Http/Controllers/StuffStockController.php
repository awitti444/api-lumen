<?php

namespace App\Http\Controllers;

use App\Models\Stuff;
use App\Models\StuffStock;
use Illuminate\Http\Request;
use App\Helpers\ApiFormatter;
use Illuminate\Support\Facades\Validator;

class StuffStockController extends Controller
{
    public function index()
    {
        $stuffStock = StuffStock::with('stuff')->get();
        $stuff = Stuff::get();
        $stock = StuffStock::get();

        $data = ['barang' => $stuff, 'stock' => $stock];
        return ApiFormatter::sendResponse(200, true, 'lihat semua stock barang', $stuffStock);
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'stuff_id' => 'required',
                'total_available' => 'required',
                'total_defec' => 'required',
            ]);

            $stock = StuffStock::updateOrCreate([
                'stuff_id' => $request->input('stuff_id')
            ], [
                'total_available' => $request->input('total_available'),
                'total_defec' =>$request->input('total_defec'),
            ]);

            return ApiFormatter::sendResponse(201, true, 'Barang Berhasil Disimpan!', $stock);
        } catch (\Throwable $th) {
            if ($th->validator->errors()) {
                return ApiFormatter::sendResponse(400, false, 'Terdapat Kesalahan
                Input Silahkan coba lagi', $th->validator->errors());
            } else {
                return ApiFormatter::sendResponse(400, false, 'Terdapat Kesalahan
                Input Silahkan coba lagi', $th->getMessage());
            }
        }
    }

    public function show($id)
    {
        try {
            $stock = StuffStock::with('stuff')->findOrFail($id);

            return ApiFormatter::sendResponse(200, true, 
            "Lihat barang dengan id $id", $stock);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, 
            "Data dengan id $id tidak ditemukan");
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $stock = StuffStock::with('stuff')->findOrFail($id);
            $total_available = ($request->total_available) ? $request->total_available : $stock->$total_available;
            $total_defec = ($request->total_defec) ? $request->total_defec : $stock->$total_defec;

            $stock->update([
                'total_available' => $total_available,
                'total_defec' => $total_defec,
            ]);

            return ApiFormatter::sendResponse(200, true,
            "Berhasil ubah data dengan id $id");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false,
            "Proses Gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $stock = StuffStock::findOrFail($id);

            $stock->delete();

            return ApiFormatter::sendResponse(200, true,
            "Berhasil hapus data barang dengan id $id", ['id' => $id]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false,
            "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
   }

   public function deleted()
   {
    try {
        $stocks = StuffStock::onlyTrashed()->get();

        return ApiFormatter::sendResponse(200, true,
        "Lihat Data barang yang dihapus", $stocks);
    } catch (\Throwable $th) {
        return ApiFormatter::sendResponse(404, false,
        "Proses gagal! silahkan coba lagi!", $th->getMessage());
       }
   }

   public function restore($id)
    {
        try{
            $stock = StuffStock::onlyTrashed()->findOrFail($id);
            $has_stock = StuffStock::where('stuff_id', $stock->stuff_id)->get();

            if($has_stock->count() == 1) { //ngecek kalo stoknya udah ada gabisa di restore lagi
                $message = "Data stock sudah ada, tidak boleh ada duplikat data stock untuk satu barang, silahkan update data dengan id stock $stock->stuff_id";
            }else{
                $stock->restore();
                $message = "Berhasil mengembalikan data yang telat dihapus!";
            }
            return ApiFormatter::sendResponse(200, true, $message, ['id' => $id, 'stuff_id' => $stock->stuff_id]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, 'Proses gagal silahkan coba lagi', $th->getMessage());
    }
}

    public function restoreAll()
    {
        try {
            $stocks= StuffStock::onlyTrashed()->restore();

            return ApiFormatter::sendResponse(200, true,
            "Berhasil mengembalikkan semua data yang telah dihapus!");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false,
            "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function permanentDelete($id)
    {
        try {
            $stock = StuffStock::onlyTrashed()->where('id', $id)->forceDelete();

            return ApiFormatter::sendResponse(200, true,
            "Berhasil hapus permanen data yang telah di hapus", ['id' => $id]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false,
            "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function permanentDeleteAll()
    {
        try {
            $stocks = StuffStock::onlyTrashed()->forceDelete();

            return ApiFormatter::sendResponse(200, true,
            "Berhasil hapus permanen data yang telah di hapus!");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false,
            "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function __construct()
    {
        $this->middleware('auth:api');
    }
}