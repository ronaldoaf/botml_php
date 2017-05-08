<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$time_i=microtime(true);

chdir(__DIR__);

include 'Bot.php';


$valor_stake=round(BOT::$Balance*0.03);

//print_r(API::GetAccountSummary() );

$Feeds=BOT::FeedsComStats();

LOG::info("# de Feeds: " . count($Feeds) );

foreach($Feeds  as $f ){
	if ($f->IsActive){
		//echo $f->GameId. ' ';
		//echo $f->stats->ind. ' ';
		if ( ($f->stats->ind>=1.75) && ($f->stats->ind2>=1.00) && ($f->stats->gH==0) ){
			if(($f->AH>=0) && ($f->stats->time>=25)  &&  ($f->stats->time<=42) ){
				if (!BOT::JaFoiApostadoAH($f->Home, $f->Away, 'HT')) print_r( BOT::ApostaEmAH($f->GameId , 0, 'HomeOdds',  $valor_stake ) );
			}
			if(($f->AH>=0) && ($f->stats->time>=70)  &&  ($f->stats->time<=90) ){
				if (!BOT::JaFoiApostadoAH($f->Home, $f->Away, 'FT')) print_r( BOT::ApostaEmAH($f->GameId , 1, 'HomeOdds', $valor_stake  ) );
			}
		
		}
		if ( ($f->stats->ind<=-1.75) && ($f->stats->ind2<=-1.00) && ($f->stats->gA==0) ){
			if(($f->AH>=0) && ($f->stats->time>=25)  &&  ($f->stats->time<=42) ){
				if (!BOT::JaFoiApostadoAH($f->Home, $f->Away, 'HT')) print_r( BOT::ApostaEmAH($f->GameId , 0, 'AwayOdds',  $valor_stake ) );
			}
			if(($f->AH>=0) && ($f->stats->time>=70)  &&  ($f->stats->time<=90) ){
				if (!BOT::JaFoiApostadoAH($f->Home, $f->Away, 'FT')) print_r( BOT::ApostaEmAH($f->GameId , 1, 'AwayOdds', $valor_stake  ) );
			}
		
		}
		echo join(' ',[ $f->GameId, $f->Home, $f->AH, $f->FullTimeHdp->BookieOdds, $f->stats->ind, $f->stats->ind2, $f->stats->gH,$f->stats->gA  ]);
		echo '<br>';
		
		
		
	}

};












//print_r( API::GetPlacementInfo(921100811, 1 ,'HomeOdds') );







$msg="Tempo Gasto: ".round(microtime(true) - $time_i,2).' segs';
echo "<br>".$msg;

LOG::info($msg);
//mail ('ronaldo.a.f@gmail.com' , 'Erro de login Bot365.ml' , 'Erro de login Bot365.ml');