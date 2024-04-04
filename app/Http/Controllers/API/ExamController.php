<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Exam;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    public function getOneExam($id)
    {
        $exam =  Exam::with('questions')->find($id);
        if ($exam) {
            return response()->json($exam);
        } else {
            return response()->json(['error' => 'Exam not found'], 404);
        }
    }

    public function searchExams ( Request $request , $search , $sortBy , $sortDir ) {
        $page = 6;
        if ($search == "all") {
            $courses = $request->user()->exams()
            ->with('course')
            ->orderBy($sortBy, $sortDir)
            ->paginate($page);
        }else{
            $courses = $request->user()->exams()
            ->with('course')
            ->where(
             function($query) use ($search) {
                 $query->where('exam_title','LIKE',"%$search%")
                 ->orWhere('course_id','LIKE',"%$search%")
                 ->orWhereHas('course', function ($query) use ($search) {
                    $query->where('course_code', 'LIKE', "%$search%");
                });
             }
            )
            ->orderBy($sortBy, $sortDir)
            ->paginate($page);

        }
        return response()->json($courses, 200);
    }


    public function getAllExam()
    {
        $exams = Exam::with('questions')->get();
        return response()->json($exams);
    }

    public function createExam ( Request $request ) {
        
        $validator = Validator::make($request->all(), [
            'exam_title' => 'required|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'exam_percentage' => 'required|integer|min:0|max:100',
            'exam_score' => 'required|integer|min:0',
            'exam_duration' => 'required|integer|min:1',
            'exam_description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        DB::beginTransaction();
    
        try {
            
            $exam = Exam::create([
                'exam_title' =>  $request->input('exam_title'),
                'course_id' => $request->input('course_id'),
                'exam_percentage' => $request->input('exam_percentage'),
                'exam_score' => $request->input('exam_score') ,
                'exam_duration' => $request->input('exam_duration') ,
                'exam_description' => $request->input('exam_description') ,
                'author' => $request->input('author')
            ]);
     
            $questions = $request->questions;

            foreach ($questions as $questionId) {
                $exam->questions()->attach($questionId);
            }
            DB::commit();
            return response()->json(['message' => 'Exam and question created successfully', 'exam' => $exam, 'questions' => $questions], 201);
        } catch (\Exception $e) {
            
            DB::rollback();
            return response()->json(['error' => 'Failed to create exam and question'], 500);
        }
    
    }

    public function updateExam(Request $request, $id)
{
    // Validate the request data
    $validator = Validator::make($request->all(), [
        'exam_title' => 'required|string|max:255',
        'course_id' => 'required|exists:courses,id',
        'exam_percentage' => 'required|integer|min:0|max:100',
        'exam_score' => 'required|integer|min:0',
        'exam_duration' => 'required|integer|min:1',
        'exam_description' => 'nullable|string',
        'questions' => 'array' // Ensure questions is an array
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    DB::beginTransaction();

    try {
        // Find the exam by ID
        $exam = Exam::find($id);
        
        if (!$exam) {
            return response()->json(['error' => 'Exam not found'], 404);
        }

        // Update the exam attributes
        $exam->update([
            'exam_title' => $request->input('exam_title'),
            'course_id' => $request->input('course_id'),
            'exam_percentage' => $request->input('exam_percentage'),
            'exam_score' => $request->input('exam_score'),
            'exam_duration' => $request->input('exam_duration'),
            'exam_description' => $request->input('exam_description'),
        ]);

        // Sync the questions
        $questions = $request->input('questions', []);
        $exam->questions()->sync($questions);

        // Commit the transaction
        DB::commit();

        // Return a success response with the updated exam
        return response()->json(['message' => 'Exam updated successfully', 'exam' => $exam , 'questions' => $questions  ], 200);
    } catch (\Exception $e) {
        // If an error occurs, rollback the transaction
        DB::rollback();

        // Return an error response
        return response()->json(['error' => 'Failed to update exam'], 500);
    }
}



}
