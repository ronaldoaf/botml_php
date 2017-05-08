<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);



const SPORTS_TYPE_SOCCER = 1; // 1 = Futebol. 2 = Basquete, etc...
const MARKETTYPE_LIVE = 0;  //0 : Live Market. É esse!
const MARKETTYPE_TODAY = 1; //1 : Today Market
const MARKETTYPE_EARLY = 2; //2 : Early Market
const ODDSFORMAT_DECIMAL ='00';
const IS_FULL_TIME = 1;
const IS_NOT_FULL_TIME = 0;
const GAME_TYPE_HANDCAP = "H"; //AsianHandicap
const GAME_TYPE_OVERUNDER = "O"; //OverUnder
const GAME_TYPE_1X2GAME = "X"; //1X2game
const CHANGE_ODDS_YES = 1; //meaning that lower odds will be automatically accepted
const CHANGE_ODDS_NO = 0; //reject if the odds became lower
const FIRST_ITEM = 0;


//include 'Log.php';
//include 'Requests.php';


function Storage($k,$v=null){

	if(!file_exists('storage.json') ) file_put_contents('storage.json','{}');
	$storage=json_decode(file_get_contents('storage.json'));
	if ($v!==null){		
		$storage->{$k}=$v;	
		file_put_contents('storage.json', json_encode($storage) );		
	}
	else{
		if (!property_exists($storage, $k)) return null;
		return $storage->{$k};
	}
}


class API{
	static protected $user='ronaldoaf13';
	static protected $pass='191771579e6babda91c11953a583e3a8';
	static $URL;
	static $AOKey;
	static $AOToken;
	static $LoginErrorCounter;
	
	static public function Command($command, $params=[], $headers=[] , $method='GET'){
           $client = new SimpleHTTPClient();   
	   
	   $headers['AOToken']=static::$AOToken;
	   
	   $h=[];
	   foreach($headers as $k=>$v){
	   	$h[]="$k : $v";
	   }
	   
	   //$api_url="https://webapi.asianodds88.com/AsianOddsService/";
	   $api_url=static::$URL.'/';  
	   
	   if ($method=='GET'){
	      $params_string='';
	         foreach($params as $_key=>$value){	   
		    $params_string.="$_key=$value&";
		 }
		 
		 
		 
		 $request_result=$client->makeRequest($api_url . $command.'?'.$params_string, 'GET', null, $h)->json();	
	     	 //$request_result=Request($api_url . $command.'?'.$params_string, 'GET', $headers)->json();
	     	 
	     	 
	     	 
	     	 
	    }  
	    if ($method=='POST'){
	       $h[]='Content-Type: application/json';
	       $h[]='Accept: application/json';
	       
	       $request_result=$client->makeRequest($api_url . $command, 'POST', json_encode($params), $h)->json();
	       
	    }  
	    print_r($request_result);
	    return property_exists( $request_result , 'Result' ) ? $request_result->Result : $request_result;
	
	     
	}
	
	static public function Login(){
	    static::$URL='https://webapi.asianodds88.com/AsianOddsService';
	    $login=static::Command('Login', ['Username'=> static::$user, 'Password'=> static::$pass] );
	    Storage('AOKey',  $login->{'Key'}   );
	    Storage('AOToken',$login->{'Token'} );
	    Storage('URL',$login->{'Url'} );
	    static::$AOKey=$login->{'Key'};
	    static::$AOToken=$login->{'Token'};
	    static::$URL=$login->{'Url'};
	    return $login;	    
	}
	
	
	static public function Register(){
		return static::Command('Register', ['Username' =>static::$user], ['AOKey' =>static::$AOKey, 'AOToken'=> static::$AOToken] );

	   
	}
	
	static public function EfetuaLogin(){
	    if(static::$LoginErrorCounter<=3) {	
	            LOG::info('Efetuando o Login user:'.static::$user.' pass:'.static::$pass . ' LoginErrorCounter:'.static::$LoginErrorCounter);
	            static::Login();
	            $register=static::Register();           
		    if ($register->Success==1) {
		        LOG::success('Login efetuado com sucesso');
		    	static::$LoginErrorCounter=0;
		    	Storage('LoginErrorCounter',static::$LoginErrorCounter);
		    }
	            else {
	                static::$LoginErrorCounter+=1;
	            	Storage('LoginErrorCounter',static::$LoginErrorCounter);
	            	
	            	static::EfetuaLogin();
	            }	            
	            return $register;
            }
            else{
            	LOG::error('Erro de Login user:'.static::$user.' pass:'.static::$pass . ' LoginErrorCounter:'.static::$LoginErrorCounter);
            	mail ('ronaldo.a.f@gmail.com' , 'Erro de login Bot365.ml' , 'Erro de login Bot365.ml');
            	echo '<pre>ERRO de Login</pre>';
            	
            	
            }     
	}
	
	
	static public function IsLoggedIn(){
		return static::Command('IsLoggedIn');
	}
	
	static public function GetFeeds(){
		return static::Command('GetFeeds', ['sportsType'=>SPORTS_TYPE_SOCCER, 'marketTypeId'=>MARKETTYPE_LIVE] )->Sports[FIRST_ITEM]->MatchGames;
	}
	
        static public function GetBets(){
          return static::Command('GetBets')->Data;
        }
   
        static public function GetHistoryStatement($from_, $to_){
          return static::Command('GetHistoryStatement',['from'=>$from_, 'to'=>$to_, 'bookies'=>'IBC,SBO,SIN,PIN,ISN,GA', 'shouldHideTransactionData'=>false]);
        }
        static public function GetPlacementInfo($GameId, $IsFullTime,$OddsName, $GameType=GAME_TYPE_HANDCAP ){
          return static::Command('GetPlacementInfo',  ['GameId'=>$GameId, 'GameType'=>$GameType, 'IsFullTime'=>$IsFullTime, 'Bookies'=> 'IBC,SBO,SIN,PIN,ISN,GA', 'OddsName'=> $OddsName, 'OddsFormat'=> ODDSFORMAT_DECIMAL, 'SportsType'=> SPORTS_TYPE_SOCCER ], [], 'POST' );
          //return static::Command('GetPlacementInfo',  ['GameId'=>$GameId, 'GameType'=>$GameType, 'IsFullTime'=>$IsFullTime, 'Bookies'=> 'IBC,SBO,PIN,ISN,GA', 'OddsName'=> $OddsName, 'OddsFormat'=> ODDSFORMAT_DECIMAL, 'SportsType'=> SPORTS_TYPE_SOCCER ], [], 'POST' );
        }
        static public function PlaceBet($GameId,  $IsFullTime, $OddsName, $BookieOdds, $Amount, $GameType=GAME_TYPE_HANDCAP, $MarketTypeId=MARKETTYPE_LIVE){
 	    $parms=['GameId'=> $GameId, 'GameType'=> $GameType,'IsFullTime'=> $IsFullTime, 'MarketTypeId'=> $MarketTypeId, 'OddsFormat'=> ODDSFORMAT_DECIMAL, 'OddsName'=>$OddsName,'SportsType'=>SPORTS_TYPE_SOCCER,'BookieOdds'=>$BookieOdds, 'AcceptChangedOdds'=>CHANGE_ODDS_YES, 'Amount'=>$Amount];     
	    return static::Command('PlaceBet', $parms , [], 'POST');
        }
   
        static public function GetAccountSummary(){
            return static::Command('GetAccountSummary');  	
        }
	
	
}





API::$AOKey=Storage('AOKey');
API::$AOToken=Storage('AOToken');
API::$LoginErrorCounter=Storage('LoginErrorCounter');
API::$URL=Storage('URL');


if (!API::IsLoggedIn()->CurrentlyLoggedIn) API::EfetuaLogin(); 
