<?php
//////////////////////////セッション管理ここから/////////////////////////////////////
session_start();
//if (!isset($_GET['reset'])){
//unset($_SESSION['count']);
//}else{}
/////////////////////////セッション管理ここまで//////////////////////////////////////
?>
<?php
//////////////////////////変数・配列説明ここから/////////////////////////////
//$hands=array();				//手札の二次元配列（以下２つの連想配列を格納）
//$handssuits=array();		//手札のマークを格納
//$handsfaces=array();		//手札の数字を格納

//$field=array();
//$fieldsuits=array();
//$fieldfaces=array();

$flowed=array();//場から流れたものを格納する配列

//$goalmark=揃えたいマーク
//$dashkey=手札の要らないカードのキー

///////////////////////////変数・配列説明ここまで////////////////////////////



/////////////////////////////自作関数ここから/////////////////////////////////

//デッキを組む
function makedeck(){
			$suits = array (
			    "S", "H", "C", "D"
			);
			$faces = array (
			    "1", "2", "3", "4", "5", "6", "7",
			    "7", "8", "9", "10", "11", "12", "13"
			);

			$deck = array();

			foreach ($suits as $suit) {
			    foreach ($faces as $face) {
			        $deck[] = array ("face"=>$face, "suit"=>$suit);
			    }
			}
			return $deck;
}

//デッキから５枚引く
function drow5($deck){
			$set=array();
			for($i = 1; $i<6; $i++){
				$card = array_shift($deck);
				array_push($set,$card);
			}
			$set_deck=array($set,$deck);
			return $set_deck;
}

//揃えようとするマークを決める
function decidegoalmark($hands){
		$handssuits=array();
		foreach ($hands as $value) { 
		  array_push($handssuits,$value['suit']);
		}	
		$result = array_count_values($handssuits);
		if(count($result)==1){//[5]のとき
		 $goalmark=$hands[0]['face'];
		}elseif(count($result)==2){ //[1.4]or[2.3]のパターンのとき
		 foreach ($result as $key => $value){
		   $suits_num[$key] = $value;
		 }
		 array_multisort ( $suits_num , SORT_ASC , $result);
		 $goalmark=key(array_slice($result, 1, 1, true));
		}elseif(count($result)==3){ //[1.2.2]or[1.1.3]のパターンのとき
		 foreach ($result as $key => $value){
		   $suits_num[$key] = $value;
		 }
		 array_multisort ( $suits_num , SORT_ASC , $result);
		 $key0 = array_search('3', $result);	//[1.1.3]かどうか
			 if($key0){			//[1.1.3]のとき
			   $goalmark=$key0;}
			 else{				//[1.2.2]のとき
				 $mark1=key(array_slice($result, 2, 1, true));
				 $mark2=key(array_slice($result, 1, 1, true));
				   
				 $key1=array();
				 $sum1=0;
				 $key1=array_keys($handssuits, $mark1);
				 foreach($key1 as $co){
				   $sum1+= pointconvert($hands[$co]['face']);
			 }
				 $key2=array();
				 $sum2=0;
				 $key2=array_keys($handssuits, $mark2);
				 foreach($key2 as $co){
				   $sum2 += pointconvert($hands[$co]['face']);
				 }
				 if($sum1>$sum2){
				    $goalmark=$mark1; ;
				 }else{$goalmark=$mark2;}
		  }
		  }elseif(count($result)==4){ //[1.1.1.2]のパターンのとき
			 foreach ($result as $key => $value){
			   $suits_num[$key] = $value;
			 }
			 array_multisort ( $suits_num , SORT_ASC , $result);
			 $goalmark=key(array_slice($result, 3, 1, true));
		  }
		return $goalmark;
}

//手札に何枚マークがそろってるかカウントする
function markcount($hands){
			$goalmark=decidegoalmark($hands);
			$handssuits=array();
			foreach ($hands as $value) { 
		  		array_push($handssuits,$value['suit']);
			}
			$result = array_count_values($handssuits);
			$markcount=count($result);
			return $markcount;
}


///場を流す
function flow($field,$deck,$flowed){
		foreach($field as $value){
			array_push($flowed,$value);
		}
		$field=array();	//field初期化忘れずに
		$fieldsuits=array();	//fieldsuits,fieldfacesの初期化も忘れずに！！！！！
		$fieldfaces=array();
		$set_deck=drow5($deck);
		$field=$set_deck[0];
		$deck=$set_deck[1];
		$flowset=array($field,$deck,$flowed);
		return $flowset;
}

//手札の要らないカードのキー取得
function dashkey ($hands){
		$goalmark=decidegoalmark($hands);
		$handssuits=array();
		foreach($hands as $value){
			array_push($handssuits,$value['suit']);
		}
		foreach($handssuits as $key =>$value){
			if($value!=$goalmark){
			  $dashkey= $key;  //更新されて要らないカードの最後のものが格納されている
			}else{}
		}
		return $dashkey;
}

//場の欲しいカードのキー（複数）取得
function wantkeys($field,$hands){
		$goalmark=decidegoalmark($hands);
		$wantkeys=array();//場の欲しいカードのキーの配列、初期化
		$fieldsuits=array();
		foreach($field as $value){
			array_push($fieldsuits,$value['suit']);
		}
		foreach($fieldsuits as $key=>$value){
			if($value==$goalmark){
				 $wantkeys[] = $key;
			}else{}
		}
		return $wantkeys;
}

//場の欲しいカードのキー（一つ）取得
function mostwantkey($field,$hands){
		$wantkeys=wantkeys($field,$hands);
		$fieldfaces=array();
		foreach($field as $value){
			array_push($fieldfaces,$value['face']);
		}
		$wantfaces=array();//場の欲しいカードの数字の配列
		foreach($wantkeys as $wantkey){
			array_push($wantfaces,$fieldfaces[$wantkey]);
		}
		$mostwantface= max($wantfaces);//場の一番欲しいカードの数字の変数
		foreach($wantkeys as $wantkey){
			if($field[$wantkey]["face"]==$mostwantface){
				$mostwantkey=$wantkey;//場の一番欲しいカードのキー
			}else{}
		}
		return $mostwantkey;
}

//手札と場の交換
function replace($field,$hands,$mostwantkey,$dashkey){
		$temp=array();		//カード交換の際に３点交換を行う為の一時的な配列
		array_push($temp,$field[$mostwantkey]);
		$field[$mostwantkey] = array_replace($field[$mostwantkey], $hands[$dashkey]);
		$hands[$dashkey] = array_replace($hands[$dashkey], $temp[0]);//$tempに添字忘れずに
		$handssuits=array();		//交換した後はsuits,faces配列の初期化、再格納を忘れずに！！
		$handsfaces=array();
		$fieldsuits=array();		//交換した後はsuits,faces配列の初期化、再格納を忘れずに！！
		$fieldfaces=array();
		foreach ($hands as $value) { 
			array_push($handssuits,$value['suit']);
        	array_push($handsfaces,$value['face']);
		}
		foreach ($field as $value) { 
  			array_push($fieldsuits,$value['suit']);
  			array_push($fieldfaces,$value['face']);
		}
		$replaced_set=array($field,$hands);
		return $replaced_set;
}

//数字をポイントに変換
function pointconvert ($face){
		if($face=="1"){
			$point=11;
		}elseif($face=="11" or $face=="12" or $face=="13"){
			$point=10;
		}else{
			$point=intval($face);
		}
		return $point;
}

//ポイントを数字に変換
function pointreverse($point){
		if($point==11){
			$face=array(1);
		}elseif($point==10){
			$face=array(10,11,12,13);
		}else{
			$face=array(2,3,4,5,6,7,8,9);
		}
		return $face;
}

////////上記関数含めたひとつのCPUの１ターンの処理
function cpu($deck,$flowed,$hands,$field){
			$goalmark=decidegoalmark($hands);
			$markcount=markcount($hands);
			if($markcount==1){//[5]のとき
				$wantkeys=wantkeys($field,$hands);
				if($wantkeys){		//場にgoalmarkと同じカードが存在する場合
					$mostwantkey=mostwantkey($field,$hands);
					$fieldmax=pointconvert($field[$mostwantkey]['face']);
					$handsfaces=array();
					foreach ($hands as $value) { 
						array_push($handsfaces,$value['face']);
					}
					$handsfaces=array_walk($fieldfaces,'pointconvert');
					$handsmin= min($fieldfaces);
					$handsmin_nums=pointreverse($handsmin);
					foreach($handsmin_nums as $value){
						$dashukey=array_search($handsmin_num,$fieldfaces);
					}
					if($fieldmax>$handsmin){	//場に手札より大きいカードが存在する場合
						$replaced_set=replace($field,$hands,$mostwantkey,$dashkey);
						$field=$replaced_set[0];
						$hands=$replaced_set[1];
					}else{}
				}else{}
			}else{
				$dashkey=dashkey($hands);
				$wantkeys=wantkeys($field,$hands);
				if($wantkeys){		//場にgoalmarkと同じカードが存在する場合
					$mostwantkey=mostwantkey($field,$hands);
					$replaced_set=replace($field,$hands,$mostwantkey,$dashkey);
					$field=$replaced_set[0];
					$hands=$replaced_set[1];

				}else{	//場にgoalmarkと同じカードが存在しなかった場合
					//流す
					$flowset=flow($field,$deck,$flowed);
					$field=$flowset[0];
					$deck=$flowset[1];
					$flowed=$flowset[2];
					//流した後のカード交換ここから
						//流した後の場の欲しいカードのキー取得ここから
						$wantkeys=wantkeys($field,$hands);
						if($wantkeys){		//場にgoalmarkと同じカードが存在する場合
								$mostwantkey=mostwantkey($field,$hands);
								$replaced_set=replace($field,$hands,$mostwantkey,$dashkey);
								$field=$replaced_set[0];
								$hands=$replaced_set[1];

								//場を流した後のカード交換ここまで
						}else{	//流したのにも関わらず、場にgoalmarkと同じカードが存在しなかった場合
								$fieldfaces=array();
								foreach ($field as $value) { 
									array_push($fieldfaces,$value['face']);
								}
								$fieldfaces_num=array();
								foreach($fieldfaces as $value){
									array_push($fieldfaces_num,$value);
								}
								$maxface= max($fieldfaces_num);
								$mostwantkey=array_search($maxface,$fieldfaces);
								$dashkey=dashkey($hands);
								$replaced_set=replace($field,$hands,$mostwantkey,$dashkey);
								$field=$replaced_set[0];
								$hands=$replaced_set[1];
								//場を流した後の要らないカード交換ここまで
						}
			}
			}
			$multi_set=array($deck,$flowed,$hands,$field);
			return $multi_set;
}

//ストップするかの判断
function stop($hands){
		$goalmark=decidegoalmark($hands);
		$markcount=markcount($hands);
		if($markcount==1){
			$sum=0;
			$handsfaces=array();
			foreach($hands as $value){
				array_push($handsfaces,$value['face']);
			 }
			foreach($handsfaces as $value){
			   $sum += pointconvert($value);
 			}
			if($sum>35){return 1;}else{return 0;}
		}else{
			return 0;
		}
}

//手札のポイントを集計
function point_sum($hands){
		$goalmark=decidegoalmark($hands);
		$markcount=markcount($hands);
		if($markcount==1){
			$sum=0;
			$handsfaces=array();
			foreach($hands as $value){
				array_push($handsfaces,$value['face']);
			 }
			foreach($handsfaces as $value){
			   $sum += pointconvert($value);
 			}
		}else{
			$sum=0;
		}
			return $sum;
}
///////////////自作関数ここまで///////////////////////




////////////////////START///////////////////////////////
if (isset($_GET['reset'])) {//スタート
	$stop=0;
	$deck=makedeck();
	shuffle($deck);
	$flowed=array();

	$set_field=drow5($deck);
	$field=$set_field[0];
	$deck=$set_field[1];

	$set_hands1=drow5($deck);
	$hands1=$set_hands1[0];
	$deck=$set_hands1[1];

	$set_hands2=drow5($deck);
	$hands2=$set_hands2[0];
	$deck=$set_hands2[1];

	$set_hands_p=drow5($deck);
	$hands_p=$set_hands_p[0];
	$deck=$set_hands_p[1];
}elseif(isset($_GET['flow'])){
	$deck=$_SESSION['deck'];
	$flowed=$_SESSION['flowed'];
	$hands1=$_SESSION['hands1'];
	$hands2=$_SESSION['hands2'];
	$hands_p=$_SESSION['hands_p'];
	$field=$_SESSION['field'];
	$stop=$_SESSION['stop'];
	$flowset=flow($field,$deck,$flowed);
	$field=$flowset[0];
	$deck=$flowset[1];
	$flowed=$flowset[2];
}elseif(isset($_GET['stop'])){
	$deck=$_SESSION['deck'];
	$flowed=$_SESSION['flowed'];
	$hands1=$_SESSION['hands1'];
	$hands2=$_SESSION['hands2'];
	$hands_p=$_SESSION['hands_p'];
	$field=$_SESSION['field'];

	$cpu1_p=point_sum($hands1);
	$cpu2_p=point_sum($hands2);
	$player_p=point_sum($hands_p);

	print "CPU1のポイント：";
	print "$cpu1_p";
	print "<br>";
	print "CPU2のポイント：";
	print "$cpu2_p";
	print "<br>";
	print "あなたののポイント：";
	print "$player_p";
	print "<br>";
}else{//２巡目以降
	if(!isset($_GET['f'])&&!isset($_GET['pass'])){
		$stop=0;
		$deck=makedeck();
		shuffle($deck);
		$flowed=array();

		$set_field=drow5($deck);
		$field=$set_field[0];
		$deck=$set_field[1];

		$set_hands1=drow5($deck);
		$hands1=$set_hands1[0];
		$deck=$set_hands1[1];

		$set_hands2=drow5($deck);
		$hands2=$set_hands2[0];
		$deck=$set_hands2[1];

		$set_hands_p=drow5($deck);
		$hands_p=$set_hands_p[0];
		$deck=$set_hands_p[1];
	}else{
		$deck=$_SESSION['deck'];
		$flowed=$_SESSION['flowed'];
		$hands1=$_SESSION['hands1'];
		$hands2=$_SESSION['hands2'];
		$hands_p=$_SESSION['hands_p'];
		$field=$_SESSION['field'];
		$stop=$_SESSION['stop'];
		if(isset($_GET['pass'])){
		}else{
			$mostwantkey=$_GET['f'];
			$dashkey=$_GET['p'];
			$replaced_set=replace($field,$hands_p,$mostwantkey,$dashkey);
			$field=$replaced_set[0];
			$hands_p=$replaced_set[1];
		}
		$multi_set1=cpu($deck,$flowed,$hands1,$field);
		$deck=$multi_set1[0];
		$flowed=$multi_set1[1];
		$hands1=$multi_set1[2];
		$field=$multi_set1[3];
		$stop=stop($hands1);
		if($stop){
			print 'CPU1のストップ！';
			print '<br>';
			$cpu1_p=point_sum($hands1);
			$cpu2_p=point_sum($hands2);
			$player_p=point_sum($hands_p);

			print "CPU1のポイント：";
			print "$cpu1_p";
			print "<br>";
			print "CPU2のポイント：";
			print "$cpu2_p";
			print "<br>";
			print "あなたののポイント：";
			print "$player_p";
			print "<br>";
		}else{
			$multi_set2=cpu($deck,$flowed,$hands2,$field);
			$deck=$multi_set2[0];
			$flowed=$multi_set2[1];
			$hands2=$multi_set2[2];
			$field=$multi_set2[3];
			$stop=stop($hands2);
			if($stop){
				print 'CPU2のストップ！';
				print '<br>';
				$cpu1_p=point_sum($hands1);
				$cpu2_p=point_sum($hands2);
				$player_p=point_sum($hands_p);

				print "CPU1のポイント：";
				print "$cpu1_p";
				print "<br>";
				print "CPU2のポイント：";
				print "$cpu2_p";
				print "<br>";
				print "あなたののポイント：";
				print "$player_p";
				print "<br>";
			}else{}
			}
}
}


$_SESSION['deck']=$deck;
$_SESSION['flowed']=$flowed;
$_SESSION['hands1']=$hands1;
$_SESSION['hands2']=$hands2;
$_SESSION['hands_p']=$hands_p;
$_SESSION['field']=$field;

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>51game</title>
</head>
<body>
<p>CPU1の手札<br>
<?php
foreach ($hands1 as $value) { 
  print $value['suit']; 
  print $value['face'];
  print '<br>';
}
?>
</p>
<p>CPU2の手札<br>
<?php
foreach ($hands2 as $value) { 
  print $value['suit']; 
  print $value['face'];
  print '<br>';
}
?>
</p>
<form method="get" action="51game.php">
<p>手札<br>
<?php
foreach ($hands_p as $key=>$value) { 
  print $value['suit']; 
  print $value['face'];
  print '<input type="radio" name="p" value="';
  print $key;
  print '">';
  print '<br>';
}
?>
</p>
<p>場<br>
<?php
foreach ($field as $key=>$value){ 
  print $value['suit']; 
  print $value['face'];
  print '<input type="radio" name="f" value="';
  print $key;
  print '">';
  print '<br>';
}
?>
<br>
</p>
<input type="submit" value="change">
<br>
<input type="submit" name='reset' value="reset">
<br>
<input type="submit" name="flow" value="flow">
<br>
<input type="submit" name="pass" value="pass">
<br>
<input type="submit" name="stop" value="stop">
</form>

</body>
</html>
