<?php

namespace FwBD\Router;

use FwBD\DI\Container;

class Router
{
	private static $uri;
	private static $method;
	private static $listRotas = [];
	private static $group = [];
	private static $middleware = [];
	private static $base_path = ['home' => '/'];
	private static $pattern = [
		':id' 	=> '(\d+)',
		':page' => '(\d+)',
		':name' => '(\w+)',
		':slug' => '([a-zA-Z0-9\-\_\.]+)',
		':all' 	=> '([a-zA-Z0-9\-\_\.\ ]+)',
	];



	public static function run(array $getResponse=[])
	{

		/*pp(self::$listRotas);
		pp(self::$group);*/

		self::setInit($getResponse);

		$data = self::getRouteMethod();

		if ( is_string($data['callback']) ) {

			array_shift($data['params']);
			$params = array($data['params']);

			$controllerMethod = explode('@', $data['callback']);
			if (count($controllerMethod) !== 2)
            	throw new \Exception('Invalid format callback | EntityController@method');

            if (!class_exists($objController = '\App\Controllers\\' . $controllerMethod[0]))
                throw new \Exception('Controller not found | '. $controllerMethod[0]);

           	$objController = new $objController($params);
           	$action = strtolower(self::$method) . ucfirst($controllerMethod[1]);

           	if (!method_exists($objController, $action))
                throw new \Exception('Method Controller not found | ' . $action);

            return call_user_func([$objController, $action]);

			/*$controller = $control[0];
			$method 	= $control[1];
			$params 	= (count($data['params'])>1)? $data['params'] : null;
			pp(compact('controller', 'method', 'params'));*/

		}

		if (!isset($data['callback']))
			return self::getPageNotFound();

		return call_user_func_array( $data['callback'], [$data['params']] );
		// return call_user_func_array($data['callback'], array_values($data['params']));

	}


	public static function get($pathRota, $retorno, array $middleware=[])
	{

        $pathRota = self::procPathRoute($pathRota);

        self::setMiddleware($pathRota, $middleware);

		self::add('GET', $pathRota, $retorno);

		// pp('execute method Router::get() ');
	}
	public static function post($pathRota, $retorno, array $middleware=[])
	{
		$pathRota = self::procPathRoute($pathRota);
		self::setMiddleware($pathRota, $middleware);
		self::add('POST', $pathRota, $retorno);
	}
	public static function put($pathRota, $retorno, array $middleware=[])
	{
		$pathRota = self::procPathRoute($pathRota);
		self::setMiddleware($pathRota, $middleware);
		self::add('PUT', $pathRota, $retorno);
	}
	public static function delete($pathRota, $retorno, array $middleware=[])
	{
		$pathRota = self::procPathRoute($pathRota);
		self::setMiddleware($pathRota, $middleware);
		self::add('DELETE', $pathRota, $retorno);
	}

	public static function group(array $group, callable $callback)
	{

		/**
         * Page Helper: Pass a static function of an abstract class as a callback function
         */
		# https://stackoverflow.com/questions/24069962/pass-a-static-function-of-an-abstract-class-as-a-callback-function

		self::$group = $group;

		$callback(__CLASS__);
		// self::loadRoutes($callback);

	}

	public static function setBasePath(string $basePath)
	{

		if ( count(self::$base_path) > 0 ) {
			return self::$base_path[$basePath] = '/';
		}

		self::$base_path = $basePath;

	}


	# METHODS PRIVATES #################################################

	/**
	 * Classe Importantes
	 */

	#
	private static function setInit(array $getResponse=[])
	{

		if ( $getResponse ) {
			self::$uri 	  = $getResponse[1];
			self::$method = $getResponse[0];
		}else{
			self::$uri 	  = empty($_SERVER['PATH_INFO']) ? '/' : $_SERVER['PATH_INFO'];
			self::$method = empty($_SERVER['REQUEST_METHOD']) ? 'GET' : $_SERVER['REQUEST_METHOD'];
		}

	}

	#
	private static function add($method, $pathRota, $retorno)
	{

		if ( !isset(self::$listRotas[$method]) ) {
			self::$listRotas[$method] = array();
		}

		$nameSpace = !empty(self::$group['namespace'])?
						ucfirst(self::$group['namespace'])."\\" : null;

		$callback 	= (is_string($retorno))? $nameSpace.$retorno : $retorno;

		$dt = array($pathRota => $callback);
		array_push(self::$listRotas[$method], $dt);

		// pp(self::$listRotas);
	}

	#
	private static function procPathRoute(string $pathRota)
	{
		# verifica o prefix == base_path
        self::setGroupPrefix();

		# limpar rotas que começa com /
        $pathRota = (substr($pathRota,0,1) === '/')? substr($pathRota, 1) : $pathRota ;

        # monta rota
        if ( !empty(self::$group['prefix']) ) {
        	if (!empty($pathRota))
	        	$pathRota = '/'.self::$group['prefix'].'/'.$pathRota;
	        else
	        	$pathRota = '/'.self::$group['prefix'];
        }else{
        	if (!empty($pathRota))
	        	$pathRota = '/'.$pathRota;
	        else
	        	$pathRota = '/';
        }

        return $pathRota;

	}

	# validaRotaRegex - validação regex com substituição de params;
	private static function validaRotaRegex($rota)
	{
		// pp('>>>>>>>>>>>>> '.$rota);
		preg_match_all('/\{([^\}]*)\}/', $rota, $rsParams);
		// pp($rsParams); # array params
		// pp($rsParams[1]);
		$rota = str_replace('/', '\/', $rota);
		// pp('--> '.$rota);

		if (!empty($rsParams)) {
			foreach ($rsParams[1] as $k => $v):
				// pp('* '.$k.' ** '.$v);
				switch ($v) {
					case ':id'	: $rgxFilter = self::$pattern[':id']; 	break;
					case ':page': $rgxFilter = self::$pattern[':page']; break;
					case ':name': $rgxFilter = self::$pattern[':name']; break;
					case ':slug': $rgxFilter = self::$pattern[':slug']; break;
					case ':*'	: $rgxFilter = self::$pattern[':all']; 	break;

					default:
						$rgxFilter = $v; break; //$this->pattern[':id']; break;
				}

				// pp($rsParams[1][$k]);
				$rota = str_replace($rsParams[0][$k], $rgxFilter, $rota);
				// pp('> '.$k.' >> '.$v.' >>> '.$rgxFilter.' >>>> '.$rota.'<hr>');
				// pp($rota);

			endforeach;

			// pp('>>> '.$rota);
		}


		$rotaValida = preg_match('/^' .$rota. '$/', self::$uri, $params);
		// pp(compact('rotaValida', 'params'));
		// array_shift($params);

		return compact('rotaValida', 'params');

	}

	# Rotorna um Array [params, callback] das rotas
    private static function getRouteMethod()
	{

		$data = self::getMethod(self::$method);
		// pp($data);

		foreach ( $data as $rota => $fn ):

			$callback 	= $fn;
			# func validaRotaRegex => retorna um Array['rotaValida', 'params']
			# > rotaValida: (Bool, 0 ou 1)
			# > params: (array, n.. params)
			$rotaValida = self::validaRotaRegex($rota);

			if ($rotaValida['rotaValida']){
				/**
				* Aplicando os filters
				*/
        		if ( array_key_exists( $rota, self::$middleware ) ) {
        			// pp(self::$middleware[$rota]);
        			$middleware = self::$middleware[$rota];
        			\FwBD\Filter\BaseFilter::getFilter($middleware);
        		}
				break;
			}

		endforeach;

		if (!$rotaValida['rotaValida'])
			$callback = null;

		return [
            'params' => $rotaValida['params'],
            'callback'=> $callback,
        ];

	}

	/**
	 * Classe ed apoia a outras classes / Helpers
	 */

	private static function setGroupPrefix()
	{
		# verifica o prefix == base_path
        if ( !empty(self::$group['prefix']) ) {
			foreach (self::$base_path as $name => $path) :
				if ( self::$group['prefix'] === $name)
					self::$group['prefix'] = null;
			endforeach;
		}
	}

	# Executa os metodos (Get, Post e etc) dentro self::$Group()
	private static function loadRoutes($routes)
    {

        if ($routes instanceof Closure) {
        	# $routes($this);
            $routes(__CLASS__);
        	// echo "string";
        }else {
        	echo "not loadRoutes > instanceof";
            # $router = $this;
            $router = __CLASS__;
            // pp($router);

            require $router;
            // $router = Closure::bind($callback, $callback(__CLASS__));
            // return $routes(__CLASS__);
        }
    }

	# Retorna rotas por Método exigido
	private static function getMethod($metodo)
	{

		if ( !isset(self::$listRotas[$metodo]) ) {
			self::$listRotas[$metodo] = array();
		}

		foreach ( self::$listRotas[$metodo] as $k => $v) {
			foreach ($v as $key => $value) {
				$data[$key] = $value;
			}
		}
		// pp($data);
		return $data;
	}

	private static function setMiddleware($pathRota, array $filters=[])
	{

		// pp(self::$listRotas);

		if ( !isset(self::$middleware[$pathRota]) )
			self::$middleware[$pathRota] = $filters;
		else
			self::$middleware[$pathRota] += $filters;
		// pp($filters);
		/*if ( !isset(self::$middleware[$pathRota]) )
			self::$middleware[$pathRota] = array();*/

		// $dt = array($rota => $callback);
		// pp($pathRota);
		// array_push(self::$middleware[$pathRota], $filters);
		// pp(self::$middleware);

	}

	private static function getPageNotFound(array $page=[])
	{

		if (empty($page)) {
			$pathPage = 'site/404';
			$dataPage = [
				'title' => 'Error 404',
				'header' => 'Error 404 - Page Not Found',
			];
		}else{
			$pathPage = $page['pathPage'];
			unset($page['pathPage']);
			$dataPage = $page;
		}

		\FwBD\View\View::directView($pathPage, $dataPage);
	}


}