<!DOCTYPE html>
<html lang="en">
<head>
  <title>Simple MSW for Swimmers - YOUR BEACH NAME</title>
  <meta charset="utf-8">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
  <link rel="stylesheet" type="text/css" href="SimpleMSW.css">
</head>
<body>

<div class="container">
  <h2>YOUR BEACH NAME</h2>
  <div class="row">
    <div class="col-8 bg-light">
		<table class="tg">
		  <tr>
			<th class="tg-h">Date</th>
			<th class="tg-h">Wave</th>
			<th class="tg-h">Swell</th>
			<th class="tg-h">Wind</th>
			<th class="tg-h">Score</th>
		  </tr>

<?php

function inRange($x, $lowerbound, $upperbound) {
  return $lowerbound <= $x && $x <= $upperbound;
}

function CellColor($thescore) {
  switch ($thescore) {
    case 1:
      return "green";
    case 2:
      return "yellow";
    case 3:
      return "red";
    case 4:
      return "gray";
    default:
      return "white";
  }
}

function score($Wave, $SwellHeight, $SwellPeriod, $WindSpeed, $WindDirection) {
/* ENTER YOUR FORMULA HERE */
/* return a value between 1-5 */
}

$Wave_text = "High entrance waves. ";
$Swell_text = "High Swell. ";
$SwellPeriod_text = "Strong Swell. ";
$Wind_text = "Windy. ";

$data = file_get_contents("https://magicseaweed.com/api/YOUR MSW KEY/forecast/?spot_id=3978&units=eu&fields=localTimestamp,issueTimestamp,swell.minBreakingHeight,swell.maxBreakingHeight,swell.components.combined.height,swell.components.combined.period,swell.components.combined.direction,wind.speed,wind.direction,wind.compassDirection");
$v = json_decode($data);
$LastUpdate = date("d/m/Y H:i", $v[0]->issueTimestamp);

foreach ($v as $forecast) {
	echo "<tr>";
	echo "<td class=\"tg-date\">".date("D H:00", $forecast->localTimestamp)."</th>";
	$swell = $forecast->swell;
	echo "<td class=\"tg-wave\">".$swell->minBreakingHeight."-".$swell->maxBreakingHeight."</td>";
    $components = $swell->components;
	$combined = $components->combined;
	echo "<td class=\"tg-swell\">".$combined->height." @ ".$combined->period."<div class=\"arrow\" style=\"transform: rotate(".round($combined->direction)."deg)\"><img src=\"SwellArrow.png\"></div>";
	echo "</td>";
	$wind = $forecast->wind;
    echo "<td class=\"tg-wind\">".$wind->speed."<div class=\"arrow\" style=\"transform: rotate(".$wind->direction."deg)\"><img src=\"WindArrow.png\"></div>";
	echo "</td>";
	$myScore = score($swell->minBreakingHeight, $combined->height, $combined->period, $wind->speed, $wind->direction);
	$remark = "";
	if ($swell->minBreakingHeight>0.7) $remark = $remark.$Wave_text;
	if ($combined->height>0.6) $remark = $remark.$Swell_text;
	if ($combined->period<6) $remark = $remark.$SwellPeriod_text;
	if ($wind->speed>12) $remark = $remark.$Wind_text;
    echo "<td class=\"tg-score\" bgcolor=\"".CellColor($myScore)."\">".$remark."</td>";
    echo "</tr>\r\n";
	if (date("H", $forecast->localTimestamp)=="21") {
		echo "<tr><td class=\"tg-break\" colspan=\"5\"></td></tr>\r\n";
	}
}

?>

	</table>
    </div>
    <div class="col-4 bg-light">
		<fieldset>
		<legend>Summary for 06:00</legend>
		<table class="tg">
		  <tr>
			<th class="tg-h">Day</th>
			<th class="tg-h">Score</th>
		  </tr>

<?php		
		foreach ($v as $forecast) {
			if (date("H", $forecast->localTimestamp)=="6") {
			echo "<tr>";
			echo "<td class=\"tg-date\">".date("l", $forecast->localTimestamp)."</th>";
			$swell = $forecast->swell;
			$components = $swell->components;
			$combined = $components->combined;
			$wind = $forecast->wind;
			$myScore = score($swell->minBreakingHeight, $combined->height, $combined->period, $wind->speed, $wind->direction);
			$remark = "";
			if ($swell->minBreakingHeight>0.7) $remark = $remark.$Wave_text;
			if ($combined->height>0.6) $remark = $remark.$Swell_text;
			if ($combined->period<6) $remark = $remark.$SwellPeriod_text;
			if ($wind->speed>12) $remark = $remark.$Wind_text;
			echo "<td class=\"tg-score\" bgcolor=\"".CellColor($myScore)."\">".$remark."</td>";
			echo "</tr>\r\n";
			}
		}

?>	
		</table>
		</fieldset>
	
		<fieldset>
			<legend>Legend</legend>
			<div class="tl-wrap">
			<table class="tl">
			  <tr>
				<td class="tl-green">&nbsp;&nbsp;&nbsp;&nbsp;</td>
				<td class="tl-legend-text">Novice swimmer</td>
			  </tr>
			  <tr>
				<td class="tl-yellow"></td>
				<td class="tl-legend-text">Intermediate swimmer</td>
			  </tr>
			  <tr>
				<td class="tl-red"></td>
				<td class="tl-legend-text">Expert swimmer</td>
			  </tr>
			  <tr>
				<td class="tl-gray"></td>
				<td class="tl-legend-text">Unsafe</td>
			  </tr>
			</table>
			</div>
			<br>
			<br>
			<div class="tl-wrap">
			<table class="tl">
			  <tr>
				<th class="tl-legend-text">Wave</th>
				<td class="tl-legend-text">Height in meters</td>
			  </tr>
			  <tr>
				<th class="tl-legend-text">Swell<br>unbroken waves</th>
				<td class="tl-legend-text">Height in meters<br>Period between waves</td>
			  </tr>
			  <tr>
				<th class="tl-legend-text">Wind</th>
				<td class="tl-legend-text">Speed in kph<br>Direction</td>
			  </tr>
			</table>
			</div>
		</fieldset>
		<br>
		<br>
		<a href="http://magicseaweed.com"><img src="https://im-1-uk.msw.ms/msw_powered_by.png"></a>
		<br>
		Last Update: <?php echo $LastUpdate;?>
		<br>
		<br>
		<a href="https://www.beachcam.co.il/zuk.html">Live Camera</a>

	</div>
	</div>
	</div>
	<br>
	The data on the site can be inaccurate.<br>
	Anyone who uses the information on the site does so at his own risk.

</body>
</html>
