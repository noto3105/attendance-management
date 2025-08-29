<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\VerificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/login', [UserController::class, 'showLogin'])->name('login');
Route::post('/login', [UserController::class, 'login']);
Route::get('/register', [UserController::class, 'showRegister']);
Route::post('/register', [UserController::class, 'register']);
Route::get('/admin_login', [UserController::class, 'showAdminLogin']);
Route::post('/admin_login', [UserController::class, 'adminLogin']);


Route::middleware('auth')->group(function() {
    Route::get('/email/verify', [VerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [VerificationController::class, 'send'])
        ->middleware(['throttle:6,1'])
        ->name('verification.send');
});
Route::middleware('auth', 'verified')->group(function() {
    Route::get('/attendance', [AttendanceController::class, 'showAttendance']);
    Route::post('/attendance/start', [AttendanceController::class, 'start']);
    Route::post('/attendance/end', [AttendanceController::class, 'end']);
    Route::post('/attendance/break_in', [AttendanceController::class, 'breakIn']);
    Route::post('/attendance/break_out', [AttendanceController::class, 'breakOut']);
    Route::get('/list', [AttendanceController::class, 'list']);
    Route::get('/attendance/{id}', [AttendanceController::class, 'showDetail']);
    Route::post('/application', [AttendanceController::class, 'submitCorrection']);
    Route::get('/application_list', [AttendanceController::class, 'applicationList']);
    Route::get('/admin/attendance_list', [AdminController::class,'adminList'])->name('admin.attendance.list');
    Route::get('/admin/attendance/{id}', [AdminController::class,'showAdminDetail']);
    Route::post('/admin/attendance/{id}', [AdminController::class,'updateAdminDetail']);
    Route::get('/staff_list', [AdminController::class, 'staffList']);
    Route::get('/admin/attendance/staff/{id}', [AdminController::class, 'adminAttendanceList']);
    Route::get('/admin/attendance/staff/{id}/export', [AdminController::class, 'adminAttendanceCsv']);
    Route::get('/admin/application_list', [AdminController::class, 'adminApplicationList']);
    Route::get('/application/approval/{id}', [AdminController::class, 'showApproval'])->name('admin.applications.approval.show');
    Route::post('/application/approval/{id}', [AdminController::class, 'approval']);
});