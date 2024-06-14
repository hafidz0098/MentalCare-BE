<?php

namespace App\Http\Controllers\Api;

use App\Models\Bantuan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\BantuanResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BantuanController extends Controller
{
    public function index()
    {
        //get bantuans
        $bantuans = Bantuan::latest()->paginate(5);

        return new BantuanResource(true, 'List Data Bantuan', $bantuans);
    }

    public function store(Request $request)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'image'     => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title'     => 'required',
            'tipe' => 'required',
            'link_url'   => 'required',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/bantuans', $image->hashName());

        //create bantuan
        $bantuan = Bantuan::create([
            'image'     => $image->hashName(),
            'title'     => $request->title,
            'tipe' => $request->tipe,
            'link_url'   => $request->link_url,
        ]);

        //return response
        return new BantuanResource(true, 'Data Bantuan Berhasil Ditambahkan!', $bantuan);
    }

    public function show(Bantuan $bantuan)
    {
        //return single ahli as a resource
        return new BantuanResource(true, 'Data Bantuan Ditemukan!', $bantuan);
    }

    public function update(Request $request, Bantuan $bantuan)
    {
        $validator = Validator::make($request->all(), [
            'title'     => 'required',
            'tipe'   => 'required',
            'link_url' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image->storeAs('public/bantuans', $image->hashName());


            Storage::delete('public/bantuans/'.$bantuan->image);

            $bantuan->update([
                'image'     => $image->hashName(),
                'title'     => $request->title,
                'tipe'      => $request->tipe,
                'link_url'  => $request->link_url,
            ]);

        } else {
            $bantuan->update([
                'title'     => $request->title,
                'tipe'      => $request->tipe,
                'link_url'  => $request->link_url,
            ]);
        }

        //return response
        return new BantuanResource(true, 'Data Bantuan Berhasil Diubah!', $bantuan);
    }

    public function destroy(Bantuan $bantuan)
    {
        //delete image
        Storage::delete('public/bantuans/'.$bantuan->image);

        //delete bantuan
        $bantuan->delete();

        //return response
        return new BantuanResource(true, 'Data bantuan Berhasil Dihapus!', null);
    }
}
