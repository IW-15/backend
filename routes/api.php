<?php

use App\Helpers\BaseResponse;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CreditScoreController;
use App\Http\Controllers\EoEventController;
use App\Http\Controllers\EventInvitationController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LoanProfileController;
use App\Http\Controllers\MerchantController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RepaymentController;
use App\Http\Controllers\SmeEventController;
use App\Http\Controllers\SmeInvitationController;
use App\Http\Controllers\TransactionController;
use App\Models\CreditScore;
use App\Models\OutletRevenue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


/** AUTH ROUTE */
Route::prefix("/auth")->group(function () {
    Route::post("/signin", [AuthController::class, 'login']);
    Route::post("/signup", [AuthController::class, 'signup']);
    Route::post("/signup-eo", [AuthController::class, 'signupEo']);
    Route::post("/phone", [AuthController::class, 'checkPhone']);
    Route::post("/email", [AuthController::class, 'checkEmail']);

    Route::get("/user", [AuthController::class, 'user']);
});

Route::middleware("auth")->group(function () {
    Route::get("/profile", [ProfileController::class, 'index']);

    Route::prefix("/transaction")->group(function () {
        Route::get("", [TransactionController::class, 'index']);
    });

    Route::prefix("/merchant")->group(function () {
        Route::post("/verify", [MerchantController::class, 'verify']);
        Route::get("/", [MerchantController::class, 'show']);
    });

    Route::prefix("/outlet")->group(function () {
        Route::get("/revenue", [OutletController::class, 'revenue']);

        Route::get("/", [OutletController::class, 'index']);
        Route::get("/{idOutlet}", [OutletController::class, 'show']);
        Route::post("/", [OutletController::class, 'create']);
        Route::post("/{idOutlet}/update", [OutletController::class, 'update']);
        Route::delete("/{idOutlet}", [OutletController::class, 'delete']);
        Route::post("/{idOutlet}/toggle-invitation", [OutletController::class, 'toggleInvitation']);
    });

    Route::prefix("/loan")->group(function () {
        Route::get("/", [LoanController::class, 'index']);
        Route::post("/apply", [LoanController::class, 'apply']);
        Route::prefix("/{idLoan}")->group(function () {
            Route::get("/", [LoanController::class, 'show']);
            Route::get("/repayment", [RepaymentController::class, 'index']);
            Route::post("/repayment/pay", [RepaymentController::class, 'pay']);
            Route::get("/repayment/{idRepayment}", [RepaymentController::class, 'show']);
        });
    });

    Route::get("/loan-profile", [LoanProfileController::class, 'index']);
    Route::get("/credit-score", [CreditScoreController::class, 'index']);

    Route::prefix("/events")->group(function () {
        Route::patch("/", [SmeEventController::class, 'getAll']);
        Route::patch("/{idEvent}", [SmeEventController::class, 'getDetail']);
        Route::post("/{idEvent}/regist", [SmeEventController::class, 'regist']);
    });

    Route::prefix("/registered-event")->group(function () {
        Route::patch("/", [SmeEventController::class, 'getAllRegis']);
        Route::patch("/{idRegisteredEvent}", [SmeEventController::class, 'getDetailRegis']);
        Route::post("/{idRegisteredEvent}/pay", [SmeEventController::class, 'pay']);
    });

    Route::prefix("/invitation")->group(function () {
        Route::patch("/", [SmeInvitationController::class, 'all']);
        Route::patch("/{idInvitation}", [SmeInvitationController::class, 'detail']);
        Route::post("/{idInvitation}/accept", [SmeInvitationController::class, 'accept']);
        Route::post("/{idInvitation}/reject", [SmeInvitationController::class, 'reject']);
    });
});

Route::middleware("auth.eo")->prefix("/eo")->group(function () {
    Route::prefix("/profile")->group(function () {
        Route::patch("/", [ProfileController::class, 'eo']);
    });

    Route::prefix("/events")->group(function () {
        Route::patch("/", [EoEventController::class, 'getAll']);
        Route::post("/", [EoEventController::class, 'create']);
        Route::delete("/{idEvent}", [EoEventController::class, 'delete']);
        Route::patch("/{idEvent}", [EoEventController::class, 'getDetail']);
        Route::post("/{idEvent}/update", [EoEventController::class, 'update']);
        Route::post("/{idEvent}/publish", [EoEventController::class, 'publish']);

        Route::prefix("/{idEvent}")->group(function () {
            Route::patch("/outlet-registered", [EoEventController::class, 'outletRegistered']);
            Route::post("/outlet-registered/accept", [EoEventController::class, 'acceptOutlet']);
            Route::patch("/outlet-registered/{idRegistered}", [EoEventController::class, 'detailRegistered']);
            Route::post("/outlet-registered/reject", [EoEventController::class, 'reject']);

            Route::prefix("/tenants")->group(function () {
                Route::patch("/", [EventInvitationController::class, 'findAvailableOutlets']);
                Route::patch("/{idTenant}", [EventInvitationController::class, 'getDetail']);
                Route::post("/{idTenant}/invite", [EventInvitationController::class, 'invite']);
            });
        });
    });

    Route::patch("/tenants", [EventInvitationController::class, 'getAll']);
});

Route::get("/", function () {
    $dummyTransactions = json_decode(file_get_contents(storage_path('app/public/dummy_transactions.json')), true);
    $selectedTransactions = $dummyTransactions[array_rand($dummyTransactions)];

    // Replace placeholder with the actual user's ID
    foreach ($selectedTransactions as &$transaction) {
        $transaction['id_user'] = 5;
        $randomDaysAgo = random_int(0, 2); // Random number from 0 to 3
        $transaction['date'] = now()->subDays($randomDaysAgo);
    }

    return $selectedTransactions;
});

Route::get("/jancok", function () {
    return BaseResponse::success("anjir", null);
});

Route::get('/storage/{filePath}', [ImageController::class, 'getImage']);
