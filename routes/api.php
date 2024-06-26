<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Authentication;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\ResultController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\Api\SubmitExamController;
use App\Http\Controllers\API\NotificationController;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return response()->json($request->user(), 200);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user/{id}', [AuthController::class, 'getUserById']);
    Route::controller(NotificationController::class)->group(function () {
        Route::post('/notifications/create','createNotification');
        Route::get('/notifications/{search}/{sortBy}/{sortDir}', 'searchNotification');
        Route::delete('/notifications/delete/{id}', 'deleteNotification');
    }); 

    Route::controller(AuthController::class)->group(function () {
        Route::get('/get/all/users/{search}/{sortBy}/{sortDir}', 'getAllUser');
        Route::delete('/users/delete/{id}', 'deleteUser');
    });
});

Route::middleware(['auth:sanctum','teacher'])->group(function () {

    Route::controller(CourseController::class)->group(function () {
        Route::get('/courses/{id}', 'getOneCourse');
        Route::get('/courses', 'getAllCourse');
        Route::get('/users/courses', 'getAllCourseBelongToUser');
        Route::get('/users/courses/search/{search}/{sortBy}/{sortDir}', 'searchCourse');
        Route::post('/courses/create', 'createCourse');
        Route::put('/courses/update/{id}', 'updateCourse');
        Route::delete('/courses/delete/{id}', 'deleteCourse');
    }); 

    Route::controller(QuestionController::class)->group(function () {
        Route::get('/questions/{id}', 'getOneQuestion');
        Route::get('/questions', 'getAllQuestion');
        Route::get('/users/questions/search/{search}/{sortBy}/{sortDir}', 'searchQuestions');
        Route::post('/questions/create', 'createQuestion');
        Route::put('/questions/update/{id}', 'updateQuestion');
        Route::delete('/questions/delete/{id}', 'deleteQuestion');
    }); 

    Route::controller(ExamController::class)->group(function () {
        Route::get('/exams/{id}', 'getOneExam');
        Route::get('/users/exams', 'getAllExam');
        Route::get('/users/exams/search/{search}/{sortBy}/{sortDir}', 'searchExams');
        Route::post('/exams/create', 'createExam');
        Route::put('/exams/update/{id}', 'updateExam');
        Route::delete('/exams/delete/{id}', 'deleteExam');
    }); 

    Route::controller(ResultController::class)->group(function () {
        Route::get('/get/user/result/{user_id}/{exam_id}','getUserResult');
        Route::get('/getResult', 'getResult');
        Route::get('/getResult/studentScore/{id}', 'getResultStudentScore');
    });

    Route::controller(SubmitExamController::class)->group(function () {
        Route::post('/submitExam/create', 'createSubmitExam');
    });
});

Route::middleware(['auth:sanctum'])->group(function () {

    Route::controller(ExamController::class)->group(function () {
        Route::get('/exams/{id}', 'getOneExam');
        Route::get('/get/user/result/{user_id}/{exam_id}','getUserResult');
        Route::get('/get/user/exams/{user_id}','getUserExamList');
    }); 


    Route::controller(ResultController::class)->group(function () {
        Route::get('/get/user/result/{user_id}/{exam_id}','getUserResult');
        Route::get('/get/user/result/{user_id}','getUserResultList');
        Route::get('/getResult', 'getResult');
        Route::get('/getResult/studentScore/{exam_id}', 'getResultStudentScore');
        
    });

    Route::controller(SubmitExamController::class)->group(function () {
        Route::post('/submitExam/create', 'createSubmitExam');
    });

    Route::controller(DashboardController::class)->group(function () {
        Route::get('/teacher/dashboard', 'getDashboardDetail');
        Route::get('/user/dashboard/{id}', 'getUserDashboardDetail');
    });

    
    Route::controller(AuthController::class)->group(function () {
        Route::post('/update/user/{id}', 'updateUser');
        Route::post('/update/password/{id}', 'updatePassword');
    });

    Route::controller(CourseController::class)->group(function () {
        Route::get('/users/student/courses/search/{search}/{sortBy}/{sortDir}', 'searchCourseForStudent');
        Route::get('/users/student/courses/{user_id}', 'getStudentCourse');
    }); 

    
});

Route::controller(CourseController::class)->group(function () {
    Route::post('/users/student/add/course/{course_id}/{user_id}', 'createCourseForStudent');
    Route::delete('/users/student/delete/course/{course_id}/{user_id}', 'deleteStudentCourse');
}); 

Route::get('/helllo', function () {
    return response()->json("hello", 200);
});