<?php

include 'Bot.php';


foreach( BOT::$Bets as $bet){
	if ($bet->Status != 'Rejected') print_r($bet);
}

echo round(BOT::$Balance);



//print_r(API::GetFeeds() );


/*

print_r( API::GetBets() );

function jaFoiApostadoAH($home, $away,$HT_or_FT){
      foreach(API::GetBets() as $e){
         if (([$e->GameType,$e->HomeName,$e->AwayName,$e->Term]==['Handicap',$home,$away,$HT_or_FT] ) && in_array( $e->Status, ['Running','Pending','Sending']) ) return True;
      }
      return False;
}


*/

//print_r( jaFoiApostadoAH('Deportivo Tachira', 'Zamora' , 'FT')  );


//print_r(API::GetPlacementInfo(1385836110 , 1 ,'HomeOdds'));
//print_r( BOT::ApostaEmAH(-364343609 , 1, 'HomeOdds',15) );
//$opcoes_de_aposta=API::GetPlacementInfo(1746007413, 1, 'HomeOdds')->OddsPlacementData;

//BookieOdds=','.join([e['Bookie']+':'+str(e['Odds']) for e in opcoes_de_apostas])

//aposta_maxima=sum(e['MaximumAmount'] for e in opcoes_de_apostas)
//if valor>aposta_maxima: valor=aposta_maxima
