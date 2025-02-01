<?php
/** @noinspection SpellCheckingInspection */

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Departments\DepartmentController;
use App\Http\Controllers\Departments\SubDepartmentController;
use App\Http\Controllers\Forms\HostedPaymentsController;
use App\Http\Controllers\Gateway\CardConnectController;
use App\Http\Controllers\Gateway\DirectStatementController;
use App\Http\Controllers\Gateway\UrlQueryPayController;
use App\Http\Controllers\Gateway\GatewayController;
use App\Http\Controllers\Gateway\PayaController;
use App\Http\Controllers\Gateway\TylerController;
use App\Http\Controllers\Icons\IconController;
use App\Http\Controllers\Merchants\MerchantController;
use App\Http\Controllers\Notifications\NotificationController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Users\UsersController;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json(['message' => 'API is working !']);
});

Route::get('/tyler/search', function () {
    $gateway = (object)[
        'custom_url' => 'https://clarkcnty.iasworld.tylerhost.net/clark_mt/Maintain/Services/PaymentInterface/2021/05/Pymt.svc',
        'username' => 'recocashier',
        'password' => 'Tyler2024',
    ];

    $params = [
        'strJur' => '000',
        'nTaxyr' => '2023',
        //'strOwner' => 'PORTER',
        //'strParid' => '3400700035208011',
        'address' => '943 WHALEY RD',
    ];
    $data = \App\Adapters\Billings\Services\BillingService::make(\App\Enums\Billings\BillingProvidersEnums::TYLER_XML, $gateway)->searchParcel(
        $params
    );
    //dd('end api');
    return response()->json($data);
});
Route::get('/tyler/item', function () {
    $gateway = (object)[
        'custom_url' => 'https://clarkcnty.iasworld.tylerhost.net/clark_mt/Maintain/Services/PaymentInterface/2021/05/Pymt.svc',
        'username' => 'recocashier',
        'password' => 'Tyler2024',
    ];


    $data = \App\Adapters\Billings\Services\BillingService::make(\App\Enums\Billings\BillingProvidersEnums::TYLER_XML, $gateway)->getParcelItemInfo(
        '000', '3400700035208011', []
    );
    //dd('end api');
    return response()->json($data);
});

Route::get('/tyler-json/flag-labels', function () {
    $gateway = (object)[
        'custom_url' => 'https://clermontcountyoh-test.atentcloud.tylerapp.com/',
        'username' => '0oa1ftf5ftlJxxgyl358',
        'password' => 'eAYZlXRUJbxqvxgQw0weLVrbNEWeHvhTc13C3HzBjZoOmH9TWFEL2KDz89OyoiPW',
    ];

    $data = \App\Adapters\Billings\Services\BillingService::make(\App\Enums\Billings\BillingProvidersEnums::TYLER_JSON, $gateway)->getCycleLabels();
    //dd('end api');
    return response()->json($data);
});


Route::get('/password-rules', [AuthController::class, 'passwordRules']);

Route::get('/logout', [AuthController::class, 'logout']);

Route::middleware(['subdomain'])->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});


// Platform-specific routes
Route::domain('{domain}.{tld}')
    ->middleware(['platform'])
    ->group(base_path('routes/platform.php'));

// Merchant-specific routes
Route::domain('{subdomain}.{domain}.{tld}')
    ->middleware(['merchant'])
    ->group(base_path('routes/merchant.php'));


/*
//Platform Routes
Route::domain('{domain}.{tld}')->middleware(['platform'])->group(function () {
    Route::get('/site-data', [MerchantController::class, 'layout'])->name('merchants.layout');
    Route::middleware(['auth:sanctum'])->group(function () {
        //All users list
        Route::get('/users', [UsersController::class, 'index'])->name('all-users-list');
        Route::get('/users/{user}', [UserController::class, 'edit'])->name('all-users-edit');
        Route::post('/users/{user?}', [UserController::class, 'save'])->name('all-users-save');
        //Blind Payments Form
        Route::get('/hosted-payments', [HostedPaymentsController::class, 'editDefaults'])->name('hosted-payments-default.edit');
        Route::post('/hosted-payments', [HostedPaymentsController::class, 'saveDefaults'])->name('hosted-payments-default.save');
        //Notification Billings Default
        Route::get('/notification-billings', [NotificationController::class, 'editBillingsDefaults'])->name('notification-billings-default.edit');
        Route::post('/notification-billings', [NotificationController::class, 'saveBillingsDefaults'])->name('notification-billings-default.save');
        //Gateways Default
        Route::prefix('gateways')->group(function () {
            Route::get('/edit/{gateway?}', [GatewayController::class, 'edit'])->name('gateways-default.edit');
            Route::get('/card-connect', [GatewayController::class, 'indexCardConnect'])->name('gateways-card-connect-default.index');
            Route::post('/card-connect/{gateway?}', [GatewayController::class, 'saveCardConnect'])->name('gateways-card-connect-default.save');
            Route::get('/paya', [GatewayController::class, 'indexPaya'])->name('gateways-paya-default.edit');
            Route::post('/paya/{gateway?}', [GatewayController::class, 'savePaya'])->name('gateways-paya-default.save');
        });
        //Icons for Departmets
        Route::get('/icons', [IconController::class, 'index'])->name('all-icons-list');
        Route::get('/icons/{icon}', [IconController::class, 'edit'])->name('all-icons-edit');
        Route::post('/icons/{icon?}', [IconController::class, 'store'])->name('all-icons-save');
        Route::delete('/icons/{icon}', [IconController::class, 'destroy'])->name('all-icons-delete');
        //Merchants
        Route::prefix('merchants')->group(function () {
            Route::get('/', [MerchantController::class, 'index'])->name('merchant.index');
            Route::get('/{merchant}', [MerchantController::class, 'edit'])->name('merchant.edit');
            Route::post('/{merchant?}', [MerchantController::class, 'save'])->name('merchant.save');
            //departments
            Route::prefix('{merchant}/departments')->group(function () {
                Route::get('/', [DepartmentController::class, 'list'])->name('department.list');
                Route::get('/{department}', [DepartmentController::class, 'edit'])->name('department.edit');
                Route::get('/parents/{department?}', [DepartmentController::class, 'listForAssessment']);
                Route::post('/{department?}', [DepartmentController::class, 'save'])->name('department.save');
                Route::prefix('{department}')->group(function () {
                    //card-connect
                    Route::get('/card-connect', [CardConnectController::class, 'edit'])->name('card-connect.edit');
                    Route::post('/card-connect', [CardConnectController::class, 'save'])->name('card-connect.save');
                    //paya
                    Route::get('/paya', [PayaController::class, 'edit'])->name('paya.edit');
                    Route::post('/paya', [PayaController::class, 'save'])->name('paya.save');
                    //Tyler
                    Route::get('/tyler', [TylerController::class, 'edit'])->name('tyler.edit');
                    Route::post('/tyler', [TylerController::class, 'save'])->name('tyler.save');
                    //EzSecurePay
                    Route::get('/url-query-pay', [UrlQueryPayController::class, 'edit'])->name('url-query-pay.edit');
                    Route::post('/url-query-pay', [UrlQueryPayController::class, 'save'])->name('url-query-pay.save');
                    //SmartPay
                    Route::get('/smart-pay', [DirectStatementController::class, 'edit'])->name('smart-pay.edit');
                    Route::post('/smart-pay', [DirectStatementController::class, 'save'])->name('smart-pay.save');
                    //Blind Payments Form
                    Route::get('/hosted-payments', [HostedPaymentsController::class, 'edit'])->name('hosted-payments.edit');
                    Route::post('/hosted-payments', [HostedPaymentsController::class, 'save'])->name('hosted-payments.save');
                    //Sub Departments
                    Route::get('/sub-departments', [SubDepartmentController::class, 'edit'])->name('sub-departments.edit');
                    Route::post('/sub-departments', [SubDepartmentController::class, 'save'])->name('sub-departments.save');
                });
            });

        });
        Route::post('/icon', [IconController::class, 'store'])->name('icon.store');
    });
});
*/
/*
//Merchant Routes
Route::domain('{subdomain}.{domain}.{tld}')->middleware(['merchant'])->group(function () {
    Route::get('/site-data', [MerchantController::class, 'layout'])->name('merchants.layout');
    Route::get('/token/{token}', [AuthController::class, 'token'])->name('auth.token');

    Route::post('/register', [AuthController::class, 'register'])->name('register');

    Route::prefix('merchants')->group(function () {

        //Auth Users Only
        //Route::middleware(['auth:sanctum'])->group(function () {
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/', [MerchantController::class, 'get'])->name('merchant.get');
            Route::put('/{merchant}', [MerchantController::class, 'update'])->name('merchants.update');

            //All users list
            Route::get('/users', [UsersController::class, 'index'])->name('users-list');
            Route::get('/users/{user}', [UserController::class, 'edit'])->name('users-edit');

        });
    });

});
*/
