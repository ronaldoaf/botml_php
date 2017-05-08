<?php
include 'Log.php';
include 'Requests.php';
include 'API.php';

Class BOT {
	static $Bets;
	static $Balance;
	
	static public function FeedsComStats(){
		function handicapStrToFloat($h_str, $favorito){
			 $arr=explode('-',$h_str); 
			 if ($arr[0]=='') return '';
			 if (count($arr)==1) $arr[1]=$arr[0];   
			 $arr[0]=(float) $arr[0];
			 $arr[1]=(float) $arr[1];
			 return ($favorito==1 ? -1 : 1)*array_sum($arr)/2.0;
		}
	
	
		function distance($a, $b){
			similar_text($a, $b, $percent);
			return 1-$percent/100;
		}
		
		function normalizaNome($nome){ 
			return str_replace(['(N)','(W)','(R)'],['','Women','Reserves'],$nome);
		}
		     
		function jogoMaisProximo($jogo, $lista_de_jogos){
		         $jogo->Home_totalcorner='';
		         $jogo->Away_totalcorner='';
		         $min_distance=0.7;
		         $j_atual=[];
		         foreach($lista_de_jogos as $j){
		            $dis_atual=distance( $j->home . $j->away, $jogo->Home . $jogo->Away );	      
		            if ($dis_atual<$min_distance){
		               $j_atual=$j;
		               $min_distance=$dis_atual;
		            }
		            
		            
		         }
		         if ($j_atual != []){ 
		            $jogo->Home_totalcorner=$j_atual->home;	
		            $jogo->Away_totalcorner=$j_atual->away;
		         }
		         return $jogo;     	
		
		}
		
		
		
		
		
		
		$client = new SimpleHTTPClient();  
		
		 $jogos_totalcorner=$client->makeRequest('http://aposte.me/live/jogos_totalcorner.json')->json();
		 $jogos_totalcorner=$jogos_totalcorner->jogos;
		 
		 /*
		 $foreach($jogos_totalcorner as $i=>$jogo){
		 	$jogo->timestamp=$jogo->timestamp
		 	$jogos_totalcorner_tmp=$jogo
		 }
		 */
		//$jogos_totalcorner=$client->makeRequest('http://1formi.ga/jogos_totalcorner.json')->json();		
		//$jogos_totalcorner=$client->makeRequest('http://bot365.ml/jogos_totalcorner.json')->json();
		//$jogos_totalcorner=Request('http://bot365.ml/jogos_totalcorner.json')->json();
		
		$stats=$client->makeRequest('http://aposte.me/live/stats.php')->json();
		
		
		
		
		$jogos_por_timestamp=[];
		foreach( $jogos_totalcorner as $jogo) $jogos_por_timestamp[$jogo->timestamp][]=$jogo;
		
		
		
		$feeds=array_filter( (array)API::GetFeeds(), function($feed){
			if (strpos($feed->HomeTeam->Name , 'No. of Corners')!==false) return false;
			if (strpos($feed->LeagueName , 'FANTASY MATCH')!==false) return false;
			return true;
		});
		
		
		
		foreach($feeds as $i=>$feed){
			 //Normaliza nome para ficar mais proximo dos nomes da bet365
		         $feeds[$i]->Home=normalizaNome($feed->HomeTeam->Name);
		         $feeds[$i]->Away=normalizaNome($feed->AwayTeam->Name);
		         
		        //Procura o jogo com o nome mais proximo do totalcorner
		         $feeds[$i]=jogoMaisProximo($feed,array_key_exists($feed->StartTime,$jogos_por_timestamp) ? $jogos_por_timestamp[ $feed->StartTime ]  : [] );
		         if ($feeds[$i]->Home_totalcorner=='') $feeds[$i]=jogoMaisProximo($feed,array_key_exists($feed->StartTime+1*60*60*1000,$jogos_por_timestamp) ? $jogos_por_timestamp[ $feed->StartTime+1*60*60*1000 ]  : [] );
		         //Adiciona as stats apartir do nome no totalcornet
		         $feeds[$i]->stats=[];
		         foreach($stats as $stat) if (($stat->home==$feeds[$i]->Home_totalcorner) && ($stat->away==$feeds[$i]->Away_totalcorner)) $feeds[$i]->stats=$stat;  
		         
		         $feeds[$i]->AH  = property_exists( $feed->FullTimeHdp , 'Handicap' ) ? handicapStrToFloat($feed->FullTimeHdp->Handicap, $feed->Favoured ) :'';
			 $feeds[$i]->AH_HT=property_exists( $feed->HalfTimeHdp , 'Handicap' ) ? handicapStrToFloat($feed->HalfTimeHdp->Handicap, $feed->Favoured ) :'';
		         
		       
		}
		
		$feeds=array_filter( $feeds, function($feed){
			if ($feed->stats==[]) return false;
			return true;
		});
		
		return $feeds;
		
	}
	
	static public function ApostaEmAH($GameId, $IsFullTime,$OddsName,$Amount){
		$opcoes_de_aposta=API::GetPlacementInfo($GameId, $IsFullTime, $OddsName)->OddsPlacementData;
		//print_r( API::PlaceBet(1691048302,  1, 'HomeOdds', 'SBO:1.890,ISN:1.850 ', 13));
		print_r($opcoes_de_aposta);
		foreach($opcoes_de_aposta as $e) {
			$BookieOdds[]=$e->Bookie.':'.$e->Odds;
			$aposta_maxima[]=$e->MaximumAmount;
				
		}
		$BookieOdds=join(',', $BookieOdds );
		$aposta_maxima=array_sum($aposta_maxima);
		
		if($Amount>$aposta_maxima) $Amount=$aposta_maxima;
		
		LOG::notice( "ApostaEmAH($GameId, $IsFullTime,$OddsName,$Amount)");
		
		return API::PlaceBet($GameId, $IsFullTime, $OddsName, $BookieOdds, $Amount);
	}
	
	
	static public function JaFoiApostadoAH($home, $away,$HT_or_FT){
	      foreach(static::$Bets as $e){
	         if (([$e->GameType,$e->HomeName,$e->AwayName,$e->Term]==['Handicap',$home,$away,$HT_or_FT] ) && in_array( $e->Status, ['Running','Pending','Sending']) ) return True;
	      }
	      return False;
	}
	
        static public function GetBalance(){
            $sumario=API::GetAccountSummary();
            return $sumario->Credit +$sumario->Outstanding;
        }
	
}

BOT::$Bets=API::GetBets();
BOT::$Balance=BOT::GetBalance();