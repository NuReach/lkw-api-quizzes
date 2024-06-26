<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    public function getOneCourse($id)
    {
        $course = Course::find($id);
        if ($course) {
            return response()->json($course);
        } else {
            return response()->json(['error' => 'Course not found'], 404);
        }
    }

    public function getAllCourseBelongToUser(Request $request)
    {
        $courses = $request->user()->courses()->get(); 
        return response()->json($courses, 200);
    }

    public function getAllCourse()
    {
        $courses = Course::all();
        return response()->json($courses);
    }

    public function searchCourse ( Request $request , $search , $sortBy , $sortDir ) {
        $page = 6;
        if ($search == "all") {
            $courses = $request->user()->courses()
            ->orderBy($sortBy, $sortDir)
            ->paginate($page);
        }else{
            $courses = $request->user()->courses()
            ->where(
             function($query) use ($search) {
                 $query->where('course_code','LIKE',"%$search%")
                 ->orWhere('course_title','LIKE',"%$search%");
             }
            )
            ->orderBy($sortBy, $sortDir)
            ->paginate($page);

        }
        return response()->json($courses, 200);
    }

    public function createCourse(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_code' => 'required|unique:courses',
            'course_title' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $course = Course::create([
            'course_code' => $request->input('course_code'),
            'course_title' => $request->input('course_title'),
            'author'=> $request->input('author')
        ]);

        return response()->json($course, 201);
    }

    public function updateCourse(Request $request, $id)
    {
        $course = Course::find($id);
        if (!$course) {
            return response()->json(['error' => 'Course not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'course_code' => 'required|unique:courses,course_code,' . $id,
            'course_title' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $course->update([
            'course_code' => $request->input('course_code'),
            'course_title' => $request->input('course_title'),
        ]);

        return response()->json($course);
    }

    public function deleteCourse($id)
    {
        $course = Course::find($id);
        if (!$course) {
            return response()->json(['error' => 'Course not found'], 404);
        }

        $course->delete();
        return response()->json(['message'=>"Delete Course  Successfully"]);
    }

    
    public function searchCourseForStudent ( Request $request , $search , $sortBy , $sortDir ) {
        $page = 6;
        if ($search == "all") {
            $courses = Course::
            orderBy($sortBy, $sortDir)
            ->paginate($page);
        }else{
            $courses = Course::
            where(
             function($query) use ($search) {
                 $query->where('course_code','LIKE',"%$search%")
                 ->orWhere('course_title','LIKE',"%$search%");
             }
            )
            ->orderBy($sortBy, $sortDir)
            ->paginate($page);

        }
        return response()->json($courses, 200);
    }

    public function createCourseForStudent ( $course_id , $user_id ) {
        $user = User::findOrFail($user_id);
        $course = Course::findOrFail($course_id);
        $enroll = Enrollment::create([
            "user_id" => $user_id,
            "course_id" => $course_id
        ]);
        return response()->json($enroll, 200);
    }   

    public function getStudentCourse ( $user_id ) {
        $user = User::findOrFail($user_id);
        $enrollments = Enrollment::with('user','course','course.user')->where('user_id',$user_id)->get();
        return response()->json($enrollments, 200);
    }

    public function deleteStudentCourse ($course_id,$user_id){
        $user = User::findOrFail($user_id);
        $course = Course::findOrFail($course_id);
        $enroll = Enrollment::where('user_id',$user_id)
        ->where('course_id',$course_id)
        ->first();
        $enroll->delete();
        return response()->json(["message"=>"Item Deleted Successfully"], 200);
    }

}