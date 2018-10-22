<?php
/**
 *  gestão de cadastro de rotas do AppBrFw;
 */
use FwBD\Router\Router as Router;


# HOME
// Router::get('admin/{:name}', function($e) { pp($e); });


Router::get('', 'HomeController@index');
Router::get('contact', 'HomeController@contact');
Router::get('about', 'HomeController@about');

/*Router::get('about', 'HomeController@about', ['middleware']);
	Router::get('about', 'HomeController@about', ['middleware'=>'teste01']);
	Router::get('about', 'HomeController@about', ['middleware'=>['prm01', 'prm02', 'prm03']]);
	Router::get('about', 'HomeController@about',
		['middleware'=>'teste01', 'middleware2'=>'teste02']
	);*/

# SETUP
Router::group(['prefix'=>'setup'], function() {

	Router::get('', 'SetupController@index');
	Router::post('create-conexao', 'SetupController@createconexao');
	Router::get('create-master', 'SetupController@createmaster');
	Router::post('create-master', 'SetupController@createmaster');


	/*Router::post('create-sqlite', 'SetupController@createSqlite');
	Router::post('create-mysql', 'SetupController@createMysql');*/

	/*Router::post('login', 'AuthController@auth');
	Router::get('create', 'AuthController@create');
	Router::post('create', 'AuthController@create');
	Router::get('forgot', 'AuthController@forgot');
	Router::post('forgot', 'AuthController@forgot');
	Router::get('logout', 'AuthController@logout');*/

});

# AUTH
Router::group(['prefix'=>'auth'], function() {

	Router::get('', 'AuthController@index');
	Router::post('login', 'AuthController@auth');

	Router::get('create', 'AuthController@create');
	Router::post('create', 'AuthController@create');

	Router::get('forgot', 'AuthController@forgot');
	Router::post('forgot', 'AuthController@forgot');

	Router::get('logout', 'AuthController@logout');

});









# ADMIN
Router::group(['prefix'=>'admin', 'namespace'=>'admin'], function() {

	Router::get('', 'AdminController@index');

});

# CATEGORY SYSTEMS
Router::group(['prefix'=>'admin/category', 'namespace'=>'admin'], function() {

	Router::get('', 'CategorySystemController@index');
	Router::get('jstatus', 'CategorySystemController@jstatus');

	Router::get('create', 'CategorySystemController@create');
	Router::post('create', 'CategorySystemController@create');

	Router::get('edit/{:id}', 'CategorySystemController@edit');
	Router::post('edit/{:id}', 'CategorySystemController@edit');

	Router::get('delete/{:id}', 'CategorySystemController@delete');
	Router::get('destroy/{:id}', 'CategorySystemController@destroy');

});
# LEVEL SYSTEMS
Router::group(['prefix'=>'admin/level', 'namespace'=>'admin'], function() {

	Router::get('', 'LevelAuthController@index');
	Router::get('jstatus', 'LevelAuthController@jstatus');

	Router::get('create', 'LevelAuthController@create');
	Router::post('create', 'LevelAuthController@create');

	Router::get('edit/{:id}', 'LevelAuthController@edit');
	Router::post('edit/{:id}', 'LevelAuthController@edit');

	Router::get('delete/{:id}', 'LevelAuthController@delete');
	Router::get('destroy/{:id}', 'LevelAuthController@destroy');

	# MODALS list/new Category
	Router::post('listcategory', 'CategorySystemController@listcategory');
	Router::post('newcategory', 'CategorySystemController@newcategory');

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

	# Profile
	Router::get('profile/{:id}', 'UserController@profile');
	Router::post('profile/{:id}', 'UserController@profile');

	Router::get('delete/{:id}', 'UserController@delete');
	Router::get('destroy/{:id}', 'UserController@destroy');

	# MODALS list/new Level
	// Router::post('listlevel', 'LevelAuthController@listlevel');
	// Router::post('newlevel', 'LevelAuthController@newlevel');
	Router::post('listlevel', 'LevelAuthController@listlevel');
	Router::post('newlevel', 'LevelAuthController@newlevel');

});








Router::run();
