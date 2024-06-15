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

        if($request->file('image')){
            $path = $request->file('image')->store('topik', 's3');
        }

        $path = Storage::disk('s3')->url($path);

        //create post
        $post = Post::create([
            'image'     => $path,
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

    public function update(Request $request, $id)
    {
        // Define validation rules
        $validator = Validator::make($request->all(), [
            'title'     => 'required',
            'topik_id'  => 'required',
            'content'   => 'required',
            'video'     => 'required',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Find the Post by ID
        $post = Post::find($id);

        // Check if Post exists
        if (!$post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        // Handle image update
        if ($request->file('image')) {
            // Extract the old image ID from the stored image path and delete it from S3
            if ($post->image) {
                $oldImageId = pathinfo(basename(parse_url($post->image, PHP_URL_PATH)), PATHINFO_FILENAME);
                $oldExtension = pathinfo($post->image, PATHINFO_EXTENSION);
                $oldS3ImagePath = 'topik/' . $oldImageId . '.' . $oldExtension;
                Storage::disk('s3')->delete($oldS3ImagePath);
            }

            // Store the new image
            $path = $request->file('image')->store('topik', 's3');
            $path = Storage::disk('s3')->url($path);
            $post->image = $path;
        }

        // Update Post details
        $post->title = $request->title;
        $post->topik_id = $request->topik_id;
        $post->content = $request->content;
        $post->video = $request->video;
        $post->save();

        // Return response
        return new PostResource(true, 'Data Post Berhasil Diubah!', $post);
    }


    public function destroy($id)
    {
        // Find the Post by ID
        $post = Post::find($id);

        // Check if Post exists
        if (!$post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        // Check if Post has an associated image
        if ($post->image) {
            // Extract the image ID from the stored image path
            $imageId = pathinfo(basename(parse_url($post->image, PHP_URL_PATH)), PATHINFO_FILENAME);
            $extension = pathinfo($post->image, PATHINFO_EXTENSION);
            $s3ImagePath = 'topik/' . $imageId . '.' . $extension;

            // Delete the image from S3
            Storage::disk('s3')->delete($s3ImagePath);
        }

        // Delete the Post record from the database
        $post->delete();

        // Return response
        return response()->json(['success' => 'Post deleted successfully'], 200);
    }

}