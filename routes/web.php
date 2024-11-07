<?php

use App\Http\Controllers\ConferenceController;
use App\Http\Controllers\MinutesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ReferenceController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FileSearchController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\ConferenceAttendanceController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\FileVersionController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileCommentController;
use App\Http\Controllers\FileViewController;
use App\Http\Controllers\ManualAttendanceController;
use App\Http\Controllers\MultipleFileUploadController;
use App\Http\Middleware\IsAdmin;
use App\Models\Message;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
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

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified', 'auth.session'])->name('dashboard');

Route::middleware(['auth', 'auth.session'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile', [ProfileController::class, 'assignRole'])->name('profile.assignRole');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/update-password', [UserController::class, 'updatePassword'])->name('user.update-password');
    Route::resource('conferences', ConferenceController::class);
    Route::get('attachment/{attachment}/content', [AttachmentController::class, 'content'])->name('attachment.content');
    Route::get('attachment/{attachment}/download', [AttachmentController::class, 'download'])->name('attachment.download');
    Route::resource('attachment', AttachmentController::class);
    Route::resource('minutes', MinutesController::class);
    Route::resource('files', FileController::class);
    Route::resource('/settings/storage', StorageController::class);
    Route::resource('/settings/category', CategoryController::class);
    Route::resource('/settings/reference', ReferenceController::class);
    Route::get('/storage_relation/{id}', [StorageController::class, 'checkStorageRelation'])->name('storage.check');
    Route::get('/category_check/{id}', [CategoryController::class, 'checkCategoryRelation'])->name('category.check');

    Route::patch('/file_rename/{id}', [FileController::class, 'renameFile'])->name('file.rename');
    Route::patch('/file_review/{id}', [FileController::class, 'setFileForReview'])->name('file.review');
    Route::get('/file_check/{id}', [FileController::class, 'checkFile'])->name('file.check');

    Route::resource('multipleFileUpload', MultipleFileUploadController::class);
    Route::post('/checkDuplicateFileName', [MultipleFileUploadController::class, 'checkDuplicateFileName'])->name('upload.check');

    Route::get('/fileList', [FileSearchController::class, 'index'])->name('file.index');
    Route::get('/fileSearch', [FileSearchController::class, 'searchFile'])->name('file.search');
    Route::get('/downloadFile/{file}', [FileSearchController::class, 'downloadFile'])->name('file.download');
    Route::post('/attachmentSearch', [FileSearchController::class, 'searchFileAsAttachment'])->name('file.attachment');

    Route::get('/messages/{id}', [ChatController::class, 'show'])->name('messages.show');
    Route::post('/messages', [ChatController::class, 'store'])->name('messages.store');
    Route::get('/userChatList', [ChatController::class, 'userChatList'])->name('messages.users');
    Route::get('/usersToChat', [ChatController::class, 'getUsersToChat']);
    Route::post('/setMessageStatus', [ChatController::class, 'setMessageStatus'])->name('message.set');
    Route::get('/newMessageCount', [ChatController::class, 'newMessageCount']);

    Route::resource('poll', PollController::class);
    Route::post('/setPollActive', [PollController::class, 'setPollActive']);
    Route::get('/getPoll/{id}', [PollController::class, 'getPoll']);
    Route::post('/submitPollResponse', [PollController::class, 'submitPollResponse']);
    Route::get('/countPollVotes/{id}', [PollController::class, 'countPollVotes']);
    Route::post('/endPoll', [PollController::class, 'endPoll']);
    Route::get('/getUserPoll/{poll_id}/{user_id}', [PollController::class, 'getUserPoll']);
    Route::get('/getIndividualVotes/{poll_id}', [PollController::class, 'getIndividualVotes']);

    Route::resource('agenda', AgendaController::class);
    Route::resource('attendance', ConferenceAttendanceController::class);
    Route::post('/deleteAttendance', [ConferenceAttendanceController::class, 'delete'])->name('attendance.delete');
    Route::get('/searchUserBoardMember', [ConferenceAttendanceController::class, 'searchBM'])->name('search.bm');
    Route::get('/getVideoConfURL/{id}', [ConferenceController::class, 'getVideoConfURL'])->name('conf.url');

    Route::get('/allChat', function(){
        return Message::all();
    })->name('chat.all');

    Route::resource('fileVersion', FileVersionController::class);
    Route::resource('note', NoteController::class);

    Route::post('/referenceSearch', [ReferenceController::class, 'searchReference'])->name('reference.search');
    Route::resource('/fileComments', FileCommentController::class);

    Route::post('/userRole', [UserController::class, 'attachRole'])->name('user.role');
    Route::resource('users', UserController::class)->middleware([IsAdmin::class]);

    Route::resource('manualAttendance', ManualAttendanceController::class);

    Route::get('/viewBlobPDF/{id}', [FileViewController::class, 'fileView'])->name('pdf.view');
    Route::get('/viewReferenceBlobPDF/{id}', [FileViewController::class, 'referenceView']);
});


require __DIR__.'/auth.php';
