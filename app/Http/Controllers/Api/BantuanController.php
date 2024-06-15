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

        if($request->file('image')){
            $path = $request->file('image')->store('bantuan', 's3');
        }

        $path = Storage::disk('s3')->url($path);

        //create bantuan
        $bantuan = Bantuan::create([
            'image'     => $path,
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

    public function update(Request $request, $id)
    {
        // Define validation rules
        $validator = Validator::make($request->all(), [
            'title'     => 'required',
            'tipe'      => 'required',
            'link_url'  => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $bantuan = Bantuan::find($id);

        if (!$bantuan) {
            return response()->json(['error' => 'Bantuan not found'], 404);
        }

        if ($request->file('image')) {
            if ($bantuan->image) {
                $oldImageId = pathinfo(basename(parse_url($bantuan->image, PHP_URL_PATH)), PATHINFO_FILENAME);
                $oldExtension = pathinfo($bantuan->image, PATHINFO_EXTENSION);
                $oldS3ImagePath = 'bantuan/' . $oldImageId . '.' . $oldExtension;
                Storage::disk('s3')->delete($oldS3ImagePath);
            }
            $path = $request->file('image')->store('bantuan', 's3');
            $path = Storage::disk('s3')->url($path);
            $bantuan->image = $path;
        }

        $bantuan->title = $request->title;
        $bantuan->tipe = $request->tipe;
        $bantuan->link_url = $request->link_url;
        $bantuan->save();

        return new BantuanResource(true, 'Data Bantuan Berhasil Diubah!', $bantuan);
    }

    public function destroy($id)
    {
        $bantuan = Bantuan::find($id);

        if (!$bantuan) {
            return response()->json(['error' => 'Bantuan not found'], 404);
        }

        if ($bantuan->image) {

            $imageId = pathinfo(basename(parse_url($bantuan->image, PHP_URL_PATH)), PATHINFO_FILENAME);
            $extension = pathinfo($bantuan->image, PATHINFO_EXTENSION);
            $s3ImagePath = 'bantuan/' . $imageId . '.' . $extension;

            Storage::disk('s3')->delete($s3ImagePath);
        }

        $bantuan->delete();

        return response()->json(['success' => 'Bantuan deleted successfully'], 200);
    }

}
