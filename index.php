<?php
require 'vendor/autoload.php';
//save request to a file for inspection
$req_dump = print_r($_REQUEST, true);
$fp = file_put_contents('api_requests.log', $req_dump, FILE_APPEND);

/**
 * Entry file
 * A central entry point to the API. Nice and simple.
 *
 * @author    James Ngugi <ngugi823@gmail.com>
 */

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set("display_errors", 1);

// ==================================================================
//
// This file is the entry point to the API.
// We use a custom router to route requests to our controllers.
// That way we have short clean urls like members/create rather rath membership_controller.php blahblah
// The router will the  forward the calls to the respective controllers under src/Controllers
//
// ------------------------------------------------------------------

use Chama\Http\ChamaController;
use Chama\Http\ContributionController;
use Chama\Http\ExpenseController;
use Chama\Http\FineController;
use Chama\Http\MembershipController;
use Chama\Http\NoticeController;
use Chama\Http\UserController;
use NoahBuscher\Macaw\Macaw;

$chama = new ChamaController();
$contributions = new ContributionController();
$expenses = new ExpenseController();
$membership = new MembershipController();
$notices = new NoticeController();
$user = new UserController();
$fines = new FineController();

// ==================================================================
//
// Route binding begins here.
//
// ------------------------------------------------------------------

# 1. Chama
Macaw::any('/chamas/one', function () use ($chama) {
	$chama->single();
});
Macaw::any('/chamas/create', function () use ($chama) {
	$chama->create();
});
Macaw::any('/chamas/update', function () use ($chama) {
	$chama->update();
});
Macaw::any('/chamas/for_user', function () use ($chama) {
	$chama->forUser();
});
Macaw::any('/chamas/delete', function () use ($chama) {
	$chama->delete();
});

# 2. Contributions
Macaw::any('/contributions/all', function () use ($contributions) {
	$contributions->all();
});
Macaw::any('/contributions/one', function () use ($contributions) {
	$contributions->single();
});
Macaw::any('/contributions/create', function () use ($contributions) {
	$contributions->create();
});
Macaw::any('/contributions/by_user', function () use ($contributions) {
	$contributions->byUser();
});
Macaw::any('/contributions/user_years', function () use ($contributions) {
	$contributions->userContributionYears();
});
Macaw::any('/contributions/update', function () use ($contributions) {
	$contributions->update();
});
Macaw::any('/contributions/delete', function () use ($contributions) {
	$contributions->delete();
});

# 3. Expenses
Macaw::any('/expenses/all', function () use ($expenses) {
	$expenses->all();
});
Macaw::any('/expenses/one', function () use ($expenses) {
	$expenses->single();
});
Macaw::any('/expenses/update', function () use ($expenses) {
	$expenses->update();
});
Macaw::any('/expenses/create', function () use ($expenses) {
	$expenses->create();
});
Macaw::any('/expenses/delete', function () use ($expenses) {
	$expenses->delete();
});

# 4. Membership
Macaw::any('/memberships/all', function () use ($membership) {
	$membership->all();
});
Macaw::any('/memberships/add', function () use ($membership) {
	$membership->add();
});
Macaw::any('/memberships/delete', function () use ($membership) {
	$membership->delete();
});
Macaw::any('/memberships/edit_role', function () use ($membership) {
	$membership->changeRole();
});

# 5. Notice
Macaw::any('/notices/all', function () use ($notices) {
	$notices->all();
});
Macaw::any('/notices/one', function () use ($notices) {
	$notices->single();
});
Macaw::any('/notices/create', function () use ($notices) {
	$notices->create();
});
Macaw::any('/notices/update', function () use ($notices) {
	$notices->update();
});
Macaw::any('/notices/delete', function () use ($notices) {
	$notices->delete();
});

# 5. User
Macaw::any('/users/login', function () use ($user) {
	$user->login();
});
Macaw::any('/users/register', function () use ($user) {

	$user->create();

});
Macaw::any('/users/update', function () use ($user) {

	$user->update();

});
Macaw::any('/users/delete', function () use ($user) {

	$user->delete();

});
# 6 Fine Controller
Macaw::any('/fines/for_user', function () use ($fines) {

	$fines->forUser();

});

Macaw::any('/fines/for_chama', function () use ($fines) {

	$fines->forChama();

});
Macaw::any('/fines/pay', function () use ($fines) {

	$fines->pay();

});

# 7. 404 Error
Macaw::error(function () use ($contributions) {
	$contributions->show404();
});

# 8. Home.
Macaw::any('/', function () {
	echo "ChamaApp API Version 1.1. Built on Open Source ;)";
});

# Dispatch the routes to the controller
Macaw::dispatch();