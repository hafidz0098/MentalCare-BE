<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Topik;
use App\Models\Post;
use App\Models\UserProgress;
use Illuminate\Http\Request;
use App\Http\Resources\TopikResource;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TopikController extends Controller
{   
    public function index()
    {
        $topiks = Topik::latest()->paginate(50);

        return response()->json([
            'success' => true,
            'message' => 'List Data Topik',
            'data' => $topiks
        ]);
    }

    public function topicWithProgress(){
        $topiks = Topik::with('post')->withCount('post')->latest()->paginate(50);

        $data = [];
        $user = auth()->guard('api')->user();

        foreach ($topiks as $topik) {
            $topikData = [
                'topik' => $topik,
                'posts' => [],
                'finished_count' => 0, // Inisialisasi total progress finished
                'notfinished_count' => 0 // Inisialisasi total progress not finished
            ];

            foreach ($topik->post as $post) {
                // Ambil kemajuan pengguna untuk post saat ini
                $userProgress = UserProgress::where('user_id', $user->id)
                    ->where('post_id', $post->id)
                    ->latest()
                    ->first();  
            
                // Tambahkan post beserta kemajuan pengguna (jika ada) ke dalam array
                $postData = [
                    'post' => $post,
                    'user_progress' => $userProgress
                ];

                $topikData['posts'][] = $postData;

                // Hitung total progress berdasarkan status
                if ($userProgress && $userProgress->status === 'Finished') {
                    $topikData['finished_count']++;
                } elseif ($userProgress && $userProgress->status !== 'Finished') {
                    $topikData['notfinished_count']++;
                }
            }

            $data[] = $topikData;
        }

        // Gabungkan data dengan $topiks
        $mergedTopiks = $topiks->map(function ($item) use ($data) {
            foreach ($data as $datum) {
                if ($datum['topik']->id === $item->id) {
                    $item->finished_count = $datum['finished_count'];
                    $item->notfinished_count = $datum['notfinished_count'];
                    unset($item->post);
                    break;
                }
            }
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Data Topik ditemukan!',
            'data' => $mergedTopiks
        ]);
    }

    public function store(Request $request)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'image'     => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'name'     => 'required',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/topiks', $image->hashName());

        //create topik
        $topik = Topik::create([
            'image'     => $image->hashName(),
            'name'     => $request->name,
        ]);

        //return response
        return new TopikResource(true, 'Data Topik Berhasil Ditambahkan!', $topik);
    }

    public function update(Request $request, Topik $topik)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //check if image is not empty
        if ($request->hasFile('image')) {

            //upload image
            $image = $request->file('image');
            $image->storeAs('public/topiks', $image->hashName());

            //delete old image
            Storage::delete('public/topiks/'.$topik->image);

            //update topik with new image
            $topik->update([
                'image'     => $image->hashName(),
                'name'     => $request->name,
            ]);

        } else {

            //update topik without image
            $topik->update([
                'name'     => $request->name,
            ]);
        }

        //return response
        return new TopikResource(true, 'Data topik Berhasil Diubah!', $topik);
    }

    public function destroy(Topik $topik)
    {
        //delete image
        Storage::delete('public/topiks/'.$topik->image);

        //delete topik
        $topik->delete();

        //return response
        return new TopikResource(true, 'Data Topik Berhasil Dihapus!', null);
    }

    public function show(Topik $topik)
    {
        $materiList = Post::where('topik_id', $topik->id)->latest()->get();
        
        $user = auth()->guard('api')->user();
        $data = [];

        foreach ($materiList as $post) {
            $userProgress = UserProgress::where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->latest()->first();

            if ($userProgress) {
                $data[] = [
                    'post' => $post,
                    'user_progress' => $userProgress
                ];
            } else {
                $data[] = [
                    'post' => $post,
                    'user_progress' => null
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Data Post by topik ditemukan!',
            'topik_title'=> $topik->name,
            'data' => $data
        ]);
    }
}
