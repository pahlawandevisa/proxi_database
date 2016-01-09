<?php



class IDB{
	public static function connection(){
		

		define("HOST_USER", "proximar_michael");
		define("HOST_PSWD", "liuxinyu951122");
		define("HOST_DB","proximar_proxi");
		
		try {
			$handler = new PDO("mysql:host=localhost;dbname=".HOST_DB,HOST_USER,HOST_PSWD);
			$handler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return $handler;
		}catch (PDOException $e){
			echo $e->getMessage();
			echo"fail";
			die();
		}
		
	}
	
}

?>