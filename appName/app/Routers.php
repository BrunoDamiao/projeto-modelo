<?php
/**
 *  gestão de cadastro de rotas do AppBrFw;
 */
use FwBD\Router\Router as Router;


# HOME


Router::get('', 'HomeController@index');
Router::get('contact', 'HomeController@contact');
Router::get('about', 'HomeController@about');

// Router::get('admin/{:name}', function($e) { pp($e); });
/*Router::get('about', 'HomeController@about', ['middleware']);
	Router::get('about', 'HomeController@about', ['middleware'=>'teste01']);
	Router::get('about', 'HomeController@about', ['middleware'=>['prm01', 'prm02', 'prm03']]);
	Router::get('about', 'HomeController@about',
		['middleware'=>'teste01', 'middleware2'=>'teste02']
	);*/

# SETUP
Router::group(['prefix'=>'setup'], function() {
	Router::get('', 'SetupController@index');
	Router::post('', 'SetupController@setup');
});

# AUTH
Router::group(['prefix'=>'auth'], function() {

	Router::get('', 'AuthController@index');
	Router::post('login', 'AuthController@auth');
	Router::post('forgotin', 'AuthController@forgotIn');
	Router::get('logout', 'AuthController@logout');

	/*Router::get('create', 'AuthController@create');
	Router::post('create', 'AuthController@create');

	Router::get('forgot', 'AuthController@forgot');
	Router::post('forgot', 'AuthController@forgot');*/

});

# ADMIN
Router::group(['prefix'=>'admin', 'namespace'=>'admin'], function() {
	Router::get('', 'AdminController@index');

	Router::get('settings', 'AdminController@settings');
	Router::get('profile/{:id}', 'AdminController@profile');
	Router::post('profile/{:id}', 'AdminController@profile');
});

# USER
Router::group(['prefix'=>'admin/user', 'namespace'=>'admin'], function() {

	Router::get('', 'UserController@index');
	Router::get('jstatus', 'UserController@jstatus');
	Router::get('status/{:id}', 'UserController@status');

	Router::get('create', 'UserController@create');
	Router::post('create', 'UserController@create');

	Router::get('edit/{:id}', 'UserController@edit');
	Router::post('edit/{:id}', 'UserController@edit');

	Router::get('delete/{:id}', 'UserController@delete');
	Router::get('destroy/{:id}', 'UserController@destroy');

});


# CATEGORY SYSTEMS
Router::group(['prefix'=>'admin/category', 'namespace'=>'admin'], function() {

	Router::get('', 'CategorySysController@index');
	Router::get('jstatus', 'CategorySysController@jstatus');

	Router::get('create', 'CategorySysController@create');
	Router::post('create', 'CategorySysController@create');

	Router::get('edit/{:id}', 'CategorySysController@edit');
	Router::post('edit/{:id}', 'CategorySysController@edit');

	Router::get('delete/{:id}', 'CategorySysController@delete');
	Router::get('destroy/{:id}', 'CategorySysController@destroy');

});

# LEVEL SYSTEMS
Router::group(['prefix'=>'admin/level', 'namespace'=>'admin'], function() {

	Router::get('', 'LevelController@index');
	Router::get('jstatus', 'LevelController@jstatus');

	Router::get('create', 'LevelController@create');
	Router::post('create', 'LevelController@create');

	Router::get('edit/{:id}', 'LevelController@edit');
	Router::post('edit/{:id}', 'LevelController@edit');

	Router::get('delete/{:id}', 'LevelController@delete');
	Router::get('destroy/{:id}', 'LevelController@destroy');

	# MODALS list/new Category
	Router::post('listcategory', 'CategorySystemController@listcategory');
	Router::post('newcategory', 'CategorySystemController@newcategory');

});












Router::run();
