<?php

namespace App\Http\Controllers\Api;

use App\Models\Konsultasi;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\KonsultasiResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class KonsultasiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //get konsuls
        $konsuls = Konsultasi::latest()->paginate(500);

        if($konsuls->isEmpty()){
            return new KonsultasiResource(false, 'Data Konsultasi Tidak Ditemukan', null);
        }else{
            return new KonsultasiResource(true, 'List Data Konsultasi', $konsuls);
        }
        
    }

    public function konsulByUser(User $user) {

        $user = auth()->guard('api')->user();

        $konsuls = Konsultasi::where('user_id', $user->id)
                    ->latest()
                    ->paginate(500);

        if($konsuls->isEmpty()){
            return new KonsultasiResource(false, 'Data Konsultasi Tidak Ditemukan', null);
        } else {
            return new KonsultasiResource(true, 'List Data Konsultasi', $konsuls);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'message' => 'required',
            // 'user_id'   => 'required',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = auth()->guard('api')->user();

        if (!$user) {
            // Pengguna belum diautentikasi kirim respons kesalahan.
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $konsultasi = Konsultasi::create([
            'name' => $request->name,
            'message' => $request->message,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        return new KonsultasiResource(true, 'Konsultasi berhasil dikirim!', $konsultasi);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Konsultasi  $konsultasi
     * @return \Illuminate\Http\Response
     */
    public function show(Konsultasi $konsultasi)
    {
        $user = auth()->guard('api')->user();
        $konsulWithMessage = Konsultasi::with('reply')->find($konsultasi->id);

        if ($konsulWithMessage) {
            return new KonsultasiResource(true, 'Data konsultasi berhasil ditemukan!', $konsulWithMessage);
        } else {
            return new KonsultasiResource(false, 'Data konsultasi tidak ditemukan!');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Konsultasi  $konsultasi
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Konsultasi $konsultasi)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Konsultasi  $konsultasi
     * @return \Illuminate\Http\Response
     */
    public function destroy(Konsultasi $konsultasi)
    {
        //
    }
}
