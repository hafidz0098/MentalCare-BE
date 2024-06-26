<?php

namespace App\Http\Controllers\Api;

use App\Models\QuizAttempt;
use App\Models\Quiz;
use App\Models\User;
use App\Models\UserProgress;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\QuizAttemptResource;
use Illuminate\Support\Facades\Validator;

class QuizAttemptController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $quizAttempt = QuizAttempt::latest()->paginate(500);

        if($quizAttempt->isEmpty()){
            return new QuizAttemptResource(false, 'Data Pengerjaan Quiz Tidak Ditemukan', null);
        }else{
            return new QuizAttemptResource(true, 'List Pengerjaan Quiz', $quizAttempt);
        }
    }

    public function getRiwayatQuizUser(User $user){
        $user = auth()->guard('api')->user();
    
        // Eager load relationship with quizzes to get the material name
        $riwayat = QuizAttempt::where('user_id', $user->id)
                    ->with('quiz') // Assuming the relationship method is named 'quiz'
                    ->latest()
                    ->paginate(500);
    
        // Check if the paginated result is empty
        if($riwayat->isEmpty()){
            return new QuizAttemptResource(false, 'Data Riwayat Kuis Tidak Ditemukan', null);
        } else {
            // Transform the data to include quiz name
            $riwayatData = $riwayat->map(function($attempt) {
                return [
                    'quiz_attempt_id' => $attempt->id,
                    'materi' => optional($attempt->quiz)->materi ?? 'Materi tidak ditemukan',
                    'score' => $attempt->skor,
                    'status' => $attempt->status,
                    'attempted_at' => $attempt->created_at->toDateTimeString(),
                ];
            });
    
            // Create a custom response with pagination data
            $response = [
                'data' => $riwayatData,
                'pagination' => [
                    'total' => $riwayat->total(),
                    'count' => $riwayat->count(),
                    'per_page' => $riwayat->perPage(),
                    'current_page' => $riwayat->currentPage(),
                    'total_pages' => $riwayat->lastPage()
                ],
            ];
    
            return new QuizAttemptResource(true, 'List Data Riwayat Kuis', $response);
        }
    }
    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            'user_id'   => 'required',
            'quiz_id' => 'required',
            'user_answer' => 'required',
            'post_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $quizAttempt = new QuizAttempt();
        $quizAttempt->quiz_id = $request->quiz_id;
        $quizAttempt->user_id = $request->user_id;
        $quizAttempt->user_answer = $request->user_answer;

        // Retrieve the quiz details to get the correct answer
        $quiz = Quiz::findOrFail($request->quiz_id);
        
        $userAnswer = $quizAttempt->user_answer;
        $correctAnswer = $quiz->correct_answer;

        $progress = new UserProgress();
        $progress->user_id = $request->user_id;
        $progress->post_id = $request->post_id;

        if ($userAnswer === $correctAnswer) {
            $quizAttempt->skor = 100;
            $quizAttempt->status = 'Lulus';
            $progress->status = "Finished";
        } else {
            $quizAttempt->skor = 0;
            $quizAttempt->status = 'Tidak Lulus';
            $progress->status = "Not Finished";
        }

        // Save quiz attempt and progress
        $quizAttempt->save();
        $progress->save();

        return response()->json([
            'success' => true,
            'message' => 'Quiz berhasil dikerjakan!',
            'data' => $quizAttempt
        ], 200);
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\QuizAttempt  $quizAttempt
     * @return \Illuminate\Http\Response
     */
    public function show(QuizAttempt $quizAttempt)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\QuizAttempt  $quizAttempt
     * @return \Illuminate\Http\Response
     */
    public function edit(QuizAttempt $quizAttempt)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\QuizAttempt  $quizAttempt
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, QuizAttempt $quizAttempt)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\QuizAttempt  $quizAttempt
     * @return \Illuminate\Http\Response
     */
    public function destroy(QuizAttempt $quizAttempt)
    {
        //
    }
}
