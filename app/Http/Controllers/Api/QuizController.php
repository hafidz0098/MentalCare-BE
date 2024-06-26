<?php

namespace App\Http\Controllers\Api;

use App\Models\Quiz;
use App\Models\User;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\QuizResource;
use Illuminate\Support\Facades\Validator;

class QuizController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $quiz = Quiz::latest()->paginate(500);

        if($quiz->isEmpty()){
            return new QuizResource(false, 'Data Quiz Tidak Ditemukan', null);
        }else{
            return new QuizResource(true, 'List Data Quiz', $quiz);
        }
    }

    // public function getQuizByPostId(Post $post){
    //     $quiz = Quiz::where('post_id', $post->id)->latest()->get();
    //     return new PostResource(true, 'Data Quiz ditemukan!', $quiz);
    // }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

     public function store(Request $request)
     {
         $validator = Validator::make($request->all(), [
             'post_id' => 'required|exists:posts,id',
             'materi' => 'required|string',
             'question' => 'required|string',
             'option_a' => 'required|string',
             'option_b' => 'required|string',
             'option_c' => 'required|string',
             'option_d' => 'required|string',
             'correct_answer' => 'required|string',
         ]);
 
         if ($validator->fails()) {
             return response()->json($validator->errors(), 422);
         }
 
         $quiz = Quiz::create([
             'post_id' => $request->post_id,
             'materi' => $request->materi,
             'question' => $request->question,
             'option_a' => $request->option_a,
             'option_b' => $request->option_b,
             'option_c' => $request->option_c,
             'option_d' => $request->option_d,
             'correct_answer' => $request->correct_answer,
         ]);
 
         return new QuizResource(true, 'Berhasil menambahkan quiz baru!', $quiz);
     }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Quiz  $quiz
     * @return \Illuminate\Http\Response
     */
    public function show(Quiz $quiz)
    {
        $quiz = Quiz::where('id', $quiz->id)->latest()->get();
        return new QuizResource(true, 'Data Quiz ditemukan!', $quiz);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Quiz  $quiz
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Quiz $quiz)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Quiz  $quiz
     * @return \Illuminate\Http\Response
     */
    public function destroy(Quiz $quiz)
    {
        $quiz->delete();

        //return response
        return new QuizResource(true, 'Quiz Berhasil Dihapus!', null);
    }
}
