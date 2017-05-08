<?php
function handicapStrToFloat($h_str, $favorito){
	 $arr=explode('-',$h_str); 
	 if ($arr[0]=='') return '';
	 if (count($arr)==1) $arr[1]=$arr[0];   
	 $arr[0]=(float) $arr[0];
	 $arr[1]=(float) $arr[1];
	 return ($favorito ? -1 : 1)*array_sum($arr)/2.0;
}



class Jogo{
   function __construct($feed) {
      $this->Update($feed);      
    } 
   
   function Update($feed){
      #Converte o handicap para decimaol 0.25. Exemplo:   0-0.5 para -0.25


       $this->GameId=$feed->GameId;
      
      if ($feed->InGameMinutes==60) $this->etapa='HT';
      elseif ($feed->InGameMinutes>120) $this->etapa='2H';
      elseif ($feed->InGameMinutes>60) $this->etapa='1H';
      else $this->etapa='0';
      
      //$this->feed=feed
      
      if (($feed->stats==[]) || ($this->etapa=='HT') || ($this->etapa=='0')) return null;
      
      $this->tempo=$this->etapa=='1H' ? $feed->InGameMinutes-60 : ($this->etapa=='2H' ? 45+$feed->InGameMinutes-120 : -1);
      $this->ativo=$feed->IsActive;
      $this->home=$feed->HomeTeam->Name;
      $this->away=$feed->AwayTeam->Name; 
      
      $this->AH_home=$this->etapa=='1H' ? handicapStrToFloat($feed->HalfTimeHdp->Handicap, $feed->Favoured==1) : handicapStrToFloat($feed->FullTimeHdp->Handicap, $feed->Favoured==1); 
      $this->AH_away=$this->etapa=='1H' ? handicapStrToFloat($feed->HalfTimeHdp->Handicap, $feed->Favoured==2) : handicapStrToFloat($feed->FullTimeHdp->Handicap, $feed->Favoured==2); 
      
      //$this->BookieOdds_BEST=($feed->HalfTimeHdp->BookieOdds ? $this->etapa=='1H' : $feed->FullTimeHdp->BookieOdds).split(';')[-1].replace(' ',':').replace('BEST=','')
 
      $this->BookieOdds_BEST=str_replace([' ','BEST='],[':',''],end((explode(';',$feed->{$this->etapa=='1H'?'HalfTimeHdp':'FullTimeHdp'}->BookieOdds))));
      //$this->BookieOdds_home=
      if ($this->AH_home=='') return null;
      
      $this->ind=$feed->stats->ind;
      $this->ind2=$feed->stats->ind2;
      $this->gH=$feed->stats->gH;
      $this->gA=$feed->stats->gA;
      
      $this->primeroTempo=($this->etapa=='1H');
      $this->segundoTempo=($this->etapa=='2H');

   }

   function EvaluateGame(){
      //Apostar no Home
      //if ( ( $this->ind>=3.50 ) and  ( $this->ind2>=2.5) and ( $this->AH_home==-0.5) and ( $this->gH<=1) and ( (($this->etapa=='1H') and ($this->tempo>=25)) or (($this->etapa=='2H') and ($this->tempo>=70)) ) ): return 1
      //if ( ( $this->ind>=2.50 ) and  ( $this->ind2>=1.50) and ( $this->AH_home==-0.25)  and  ( $this->gH==0.0) and  ( (($this->etapa=='1H') and ($this->tempo>=25)) or  (($this->etapa=='2H') and ($this->tempo>=70))) ): return 1
      if ( ( $this->ind>=2.0) &&  ( $this->ind2>=1.0) && ( $this->AH_home>=0)  &&  ( $this->gH==0.0) &&  ( (($this->etapa=='1H') && ($this->tempo>=25)) ||  (($this->etapa=='2H') && ($this->tempo>=70))    ) ) return 1;
      
      //Apostar em away
      //if ( ( $this->ind<=-3.50 ) and  ( $this->ind2<=-2.5) and ( $this->AH_away==-0.5)  and  ( $this->gA<=1)  and  ( (($this->etapa=='1H') and ($this->tempo>=25)) or  (($this->etapa=='2H') and ($this->tempo>=70))    ) ): return -1
      //if ( ( $this->ind<=-2.50 ) and  ( $this->ind2<=-1.50) and ( $this->AH_away==-0.25)  and  ( $this->gA==0.0)  and  ( (($this->etapa=='1H') and ($this->tempo>=25)) or  (($this->etapa=='2H') and ($this->tempo>=70))    ) ): return -1
      if ( ( $this->ind<=-2.0 ) &&  ( $this->ind2<=-1.0) && ( $this->AH_away>=0)  &&  ( $this->gA==0.0) &&  ( (($this->etapa=='1H') && ($this->tempo>=25)) ||  (($this->etapa=='2H') && ($this->tempo>=70))    ) ) return -1;
      
      return 0;
   }

}