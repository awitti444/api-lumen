<?php

namespace App\Http\Controllers;

use App\Models\Stuff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiFormatter;

class StuffController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }
    
    public function index()
    {
        try {
            $stuff = Stuff::with('stock')->get();
            return ApiFormatter::sendResponse(200, true, $stuff);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, false, $err->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required', 
                'category' => 'required'
            ]);
            $stuff = Stuff::create([
                'name' => $request->input('name'),
                'category' => $request->input('category')
            ]);
            return ApiFormatter::sendResponse(201, true, 'Barang Berhasil
            Disimpan!', $stuff);
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
            $stuff = Stuff::with('stock')->findOrFail($id);

            return ApiFormatter::sendResponse(200, true, 
            "Lihat barang dengan id $id", $stuff);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, 
            "Data dengan id $id tidak ditemukan");
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $stuff = Stuff::findOrFail($id);
            $name = ($request->name) ? $request->name : $stuff->name;
            $category = ($request->category) ? $request->category : $stuff->category;

            $stuff->update([
                'name'     => $name,
                'category' => $category
            ]);

            return ApiFormatter::sendResponse(200, true,
            "Berhasil ubah data dengan id $id");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false,
            "Proses Gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    // public function destroy($id)
    // {
    //     try {
    //         $stuff = Stuff::findOrFail($id);

    //         $stuff->delete();

    //         return ApiFormatter::sendResponse(200, true,
    //         "Berhasil hapus data barang dengan id $id", ['id' => $id]);
    //     } catch (\Throwable $th) {
    //         return ApiFormatter::sendResponse(404, false,
    //         "Proses gagal! silahkan coba lagi!", $th->getMessage());
    //     }
    // }

    public function destroy($id)
    {
        try {
            $stuff = Stuff::findOrFail($id);

            // Check if stuff has related data in inbound stuffs, stuff stock, or lendings
            if ($stuff->inboundStuffs()->exists() || $stuff->stock()->exists() || $stuff->lendings()->exists()) {
                return ApiFormatter::sendResponse(400, false, "Tidak dapat menghapus data stuff, karena sudah terdapat data inbound!");
            }

            $stuff->delete();

            return ApiFormatter::sendResponse(200, true, "Berhasil hapus data dengan ID $id", ['id' => $id]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal silahkan coba lagi", $th->getMessage());
        }
    }

    public function deleted()
    {
        try {
            $stuffs = Stuff::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, true,
            "Lihat Data barang yang dihapus", $stuffs);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false,
            "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $stuff = Stuff::onlyTrashed()->where('id', $id);

            $stuff->restore();

            return ApiFormatter::sendResponse(200, true,
            "Berhasil mengembalikkan data yang telah dihapus!", ['id' => $id]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false,
            "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function restoreAll()
    {
        try {
            $stuffs= Stuff::onlyTrashed();

            $stuffs->restore();

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
            $stuff = Stuff::onlyTrashed()->where('id', $id)
            ->forceDelete();

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
            $stuffs = Stuff::onlyTrashed();

            $stuffs->forceDelete();

            return ApiFormatter::sendResponse(200, true,
            "Berhasil hapus permanen data yang telah di hapus!");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false,
            "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }
}
