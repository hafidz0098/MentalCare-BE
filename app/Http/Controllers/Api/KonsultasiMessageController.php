<?php

namespace App\Http\Controllers\Api;

use App\Models\KonsultasiMessage;
use App\Models\Konsultasi;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\KonsultasiMessageResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class KonsultasiMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Konsultasi $konsultasi)
    {
        $validator = Validator::make($request->all(), [
            'konsultasi_id' => 'required',
            'message'     => 'required',

        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $konsultasi = Konsultasi::find($request->konsultasi_id);
        $user = auth()->guard('api')->user();
        
        if($user->role === 'psikolog'){
            $konsultasi->status = 'responded';
        }else{
            $konsultasi->status = 'pending';
        }
        
        $konsultasi->save();
        
        $konsultasiMessage = KonsultasiMessage::create([
            'konsultasi_id' => $request->konsultasi_id,
            'message' => $request->message,
            'role' => $user->role,
            'sender' => $user->name,
        ]);

        

        return new KonsultasiMessageResource(true, 'balasan Konsultasi berhasil dikirim!', $konsultasiMessage);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\KonsultasiMessage  $konsultasiMessage
     * @return \Illuminate\Http\Response
     */
    public function show(KonsultasiMessage $konsultasiMessage)
    {
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\KonsultasiMessage  $konsultasiMessage
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, KonsultasiMessage $konsultasiMessage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\KonsultasiMessage  $konsultasiMessage
     * @return \Illuminate\Http\Response
     */
    public function destroy(KonsultasiMessage $konsultasiMessage)
    {
        //
    }
}
