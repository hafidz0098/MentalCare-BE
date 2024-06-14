<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Models\Quiz;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Http\Resources\QuizResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{    

    public function __construct()
    {
        // $this->middleware('auth:api')->except(['index', 'show']);
    }
    /**
     * index
     *
     * @return void
     */
    public function index()
    {
        //get posts
        $posts = Post::latest()->paginate(5);

        //return collection of posts as a resource
        return new PostResource(true, 'List Data Posts', $posts);
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'image'     => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'title'     => 'required',
            'topik_id' => 'required',
            'content'   => 'required',
            'video'   => 'required',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        //create post
        $post = Post::create([
            'image'     => $image->hashName(),
            'title'     => $request->title,
            'topik_id' => $request->topik_id,
            'content'   => $request->content,
            'video'     => $request->video,
        ]);

        //return response
        return new PostResource(true, 'Data Post Berhasil Ditambahkan!', $post);
    }



    public function show(Post $post)
    {
        //return single post as a resource
        return new PostResource(true, 'Data Post Ditemukan!', $post);
    }

    public function getQuizByPostId(Post $post){
        $quizzes = Quiz::where('post_id', $post->id)->latest()->get(); // Mencari data Quiz berdasarkan post_id
        return new QuizResource(true, 'Data Quiz by post ditemukan!', $quizzes); // Mengembalikan data dengan QuizResource
    }

    public function update(Request $request, Post $post)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'title'     => 'required',
            'content'   => 'required',
            'topik_id' => 'required',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //check if image is not empty
        if ($request->hasFile('image')) {

            //upload image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            //delete old image
            Storage::delete('public/posts/'.$post->image);

            //update post with new image
            $post->update([
                'image'     => $image->hashName(),
                'title'     => $request->title,
                'topik_id' => $request->topik_id,
                'content'   => $request->content,
                'video'     => $request->video,
            ]);

        } else {
            $post->update([
                'title'     => $request->title,
                'topik_id' => $request->topik_id,
                'content'   => $request->content,
                'video'   => $request->video,
            ]);
        }
        return new PostResource(true, 'Data Post Berhasil Diubah!', $post);
    }

    public function destroy(Post $post)
    {
        Storage::delete('public/posts/'.$post->image);
        $post->delete();
        return new PostResource(true, 'Data Post Berhasil Dihapus!', null);
    }
}