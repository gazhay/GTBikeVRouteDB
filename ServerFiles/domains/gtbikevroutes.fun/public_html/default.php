<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" charset="utf-8" name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="RTStyles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</head> 
<body style="background-color:#49422D;">
<div class="container-fluid" >
  <div class="topbanner">
  <!-- <style>body {background-image:URL("images/gtbv_banr.jpg");}</style> -->
    <h1 style="color:#C0D0C8;">GTBike V Routes</h1>
    <p style="color:#C0D0C8;">Below you'll find the current tracked list of routes included in the <a class="link" href="https://github.com/gtbikev/courses">GTBike V course repository.</a><br>Be sure to check the repository out if you have interest in building your own routes, or to submit issues with routes.<br>For general discussion about the mod or routes, come visit the community <a class="link" href="https://www.facebook.com/groups/1089053124812221">Facebook group.</a><br>
	For issues with this site, development details, of just to take a look at how it all comes together, this project is maintained on <a class="link" href="https://github.com/defiancecp/GTBikeVRouteDB">Github.</a><br>When you stop by, be sure to take a look at the readme for the latest list of attributions and thanks.<br>And finally, these courses all utilize the brilliant <a class="link" href="gta5-mods.com/scripts/gt-bike-v">GT Bike V mod</a> for GTA V.<br><br>
	<b>You can now view your GT Bike V rides mapped on the GTA V Map, using the <a class="link" href="https://gtbikevroutes.fun/MapPreview.php" >GT Bike V ride viewer.</a> 
	</p>
  </div>
</div>
    <?php
    include('/home/u544302174/dbscripts/dbconfig.php');
    include('/home/u544302174/dbscripts/getip.php');

    // parameter handling: Metric vs. Imperial measures
    $met = $_GET['met'];
    if($met === NULL) {
        $met = 'Metric';
    }
    if($met === 'Metric') {
        $opmet = 'Imperial';
        $mettag = '?met='.$met;
        $metswtag = '?met='.$opmet;
        $metsw = 'images/tg_on.png';
    }
    if($met === 'Imperial') {
        $opmet = 'Metric';
        $mettag = '?met='.$met;
        $metswtag = '?met='.$opmet;
        $metsw = 'images/tg_off.png';
    }

    // page displays either focused on a single route, or general of all routes.  This makes that distinction.
    $route = $_GET['route'];
    if($route === NULL) {
        $routetag = '';
    } else {
        $routetag = '&route='.$route;
    }    
   
	// if a route is passed, go to details.  If it isn't, show summary.
    if($route === NULL) {

        $sql = "CALL GetRouteData('".get_ip_address()."','ALL');";
        // execute SQL query to get all the route data.
        $conn = myConnection();
        // This is just handling of connection results.
        if ($result = $conn->query($sql)) {
            if ($result->num_rows > 0) {
				// now create a table to show results, and then cycle through each result row and build an html "tr" for each row and "td" for each displayed element.
				echo '<table id="tblSummary" style="color:#a0b0a8;" class="table table-dark table-striped">';

				// Sort/Filter Controls:
				echo '<tr id="rowSortFilter">';
				echo '<th><input id="myInRoute" type="text" onkeyup="fltFn()" placeholder="search route.." title="f1"></th>';
				echo '<th><input id="myInAuthor" type="text" onkeyup="fltFn()" placeholder="search author.." title="f2"></th>';
				echo '<th><input id="myInType" type="text" onkeyup="fltFn()" placeholder="search type.." title="f3"></th>';

				echo '<th><input id="myDistMin" type="number" onkeyup="fltFn()" placeholder=" Dist >" title="f5"><br>';
				echo '<input id="myDistMax" onkeyup="fltFn()" placeholder="Dist <.." title="f7"></th>';

				echo '<th><input id="btnDistAsc" type="button" value="sort-^" onclick="srtFn(9,1)"><br>';
				echo '<input id="btnDistDesc" type="button" value="sort-v" onclick="srtFn(9,-1)"></th>';

				echo '<th><input id="myElvMin" type="number" onkeyup="fltFn()" placeholder="Elev >.." title="f6"><br>';
				echo '<input id="myElvMax" type="number" onkeyup="fltFn()" placeholder="Elev <.." title="f8"></th>';

				echo '<th><input id="btnElvAsc" type="button" value="sort-^" onclick="srtFn(11,1)"><br>';
				echo '<input id="btnElvDesc" type="button" value="sort-v" onclick="srtFn(11,-1)"></th>';


				echo '<th><input stype="number" id="myRateMin" onkeyup="fltFn()" placeholder="Rated >.." title="f4"></th>';

				echo '<th ><input id="btnRtgAsc" type="button" value="sort-^" onclick="srtFn(8,1)"><br>';
				echo '<input id="btnRtgDesc" type="button" value="sort-v" onclick="srtFn(8,-1)"></th>';

				echo '<th colspan="2" >Units of Measure:<br><a href="default.php'.$metswtag.$routetag.'"><img src="'.$metsw.'" height="26px"/></a>'.$met.'</th>';

				echo '</tr>';

				// header row
				echo '<tr><th>Route</th><th>Author</th><th>Type</th><th colspan="2">Distance</th><th colspan="2">Elevation</th><th colspan="2" width="145px">Rating</th><th>Download</th><th>Map</th><th style="display:none;">numRating</th><th style="display:none;">numKM</th><th style="display:none;">numMI</th><th style="display:none;">numM</th><th style="display:none;">numFT</th></tr>'; // table opener & header row
						
				
                // output data of each row
                while($row = $result->fetch_assoc()) { // fetch is pop-like so each row is cycled through. when done, result of the assignment will be false, ending loop.
					// this value contains the definition for each row's rating link URL.  See submitrating.php for implementation of this rating.
                    $ratinglink = 'SubmitRating.php?route='.$row["RouteName"].'&rating='.$row["CurrentRating"].'&submit=FALSE&ratingcount='.$row["RatingCount"];
                    if($met === 'Metric') { // shift displayed metrics based on user selection
                        $velevation = $row["ElevationM"]."m"; 
                        $vdistance = $row["DistanceKM"]."km"; 
                    } else {
                        $velevation = $row["ElevationFT"]."ft"; 
                        $vdistance = $row["DistanceMI"]."mi"; 
                    } // not using the map exactly like encoded in readme
					// which, to be clear, is on me: I submitted the pull request to structure the table in the readme :P 
					// but for now just fixing. This will probably be better in the API.
                    $mapstring = str_replace('Map</a>','<img src="images/map.png" class="link" height="20px"/></a>',$row["Map"]);
					// now build the tr.
                    echo '<tr><td><a class="link" href="default.php'.$mettag.'&route='.$row["RouteName"].'">'.$row["RouteName"].'</a></td><td>'.$row["Author"].'</td><td>'.$row["Type"].'</td><td colspan="2">'.$vdistance.'</td><td colspan="2">'.$velevation.'</td><td colspan="2"><iframe src="'.$ratinglink.'" class="embed-responsive-item" width="100%" height="20px" allowtransparency="true" style="border:0px solid black;"></iframe></td><td><img src=/images/dl.png class="link" height="20px" onclick="downloadResource(\'https://raw.githubusercontent.com/gtbikev/courses/master/courses/'.$row["RouteName"].'.json\',\''.$row["RouteName"].'.json\')"></td><td>'.$mapstring.'</td><td style="display:none;">'.$row["CurrentRating"].'</td><td style="display:none;">'.$row["DistanceKM"].'</td><td style="display:none;">'.$row["DistanceMI"].'</td><td style="display:none;">'.$row["ElevationM"].'</td><td style="display:none;">'.$row["ElevationFT"].'</td></tr>';
				}
					// all tr's done, close it up.
				echo "</table>"; // table opener & header row
			} else {
				echo "0 results";
			}
				// connection cleanup
			$result->free_result();
			$conn->close();
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
	} else {
		// This is the specific route page.  Most of the code is similar to summary.
		echo '<div style="color:#C0D0C8;">';
		echo '<a href="default.php'.$metswtag.$routetag.'"><img src="'.$metsw.'" height="20px"/></a>'.$met.'';
        $sql = "CALL GetRouteData('".get_ip_address()."','ALL');";
        echo '&nbsp;&nbsp;&nbsp;<a href="default.php?met='.$met.'"><img src="images/bkup.png" height="20px"/></a>Return';
        echo '</div>';
        $sql = "CALL GetRouteData('".get_ip_address()."','".$route."');";
        // execute SQL query gathering table details
        $conn = myConnection();
        // This is just handling of connection results.
        if ($result = $conn->query($sql)) {
            if ($result->num_rows > 0) {
                echo '<table id="tblSingle" style="color:#a0b0a8;" class="table table-dark table-striped"><tr><th>Route</th><th>Author</th><th>Type</th><th>Distance</th><th>Elevation</th><th>Download</th><th>Map</th><th width="145px">Rating</th></tr>'; // table opener & header row
                // output data of each row
                while($row = $result->fetch_assoc()) {// fetch is pop-like so each row is cycled through. when done, result of the assignment will be false, ending loop.
					// this value contains the definition for each row's rating link URL.  See submitrating.php for implementation of this rating.
                    $ratinglink = 'SubmitRating.php?route='.$row["RouteName"].'&rating='.$row["CurrentRating"].'&submit=FALSE&ratingcount='.$row["RatingCount"];
                    if($met === 'Metric') { // shift displayed metrics based on user selection
                        $velevation = $row["ElevationM"]."m";
                        $vdistance = $row["DistanceKM"]."km";
                    } else {
                        $velevation = $row["ElevationFT"]."ft";
                        $vdistance = $row["DistanceMI"]."mi";
                    }
                    $mapstring = str_replace('Map</a>','<img src="images/map.png" class="link" height="20px"/></a>',$row["Map"]);
					// not using the map exactly like encoded in readme
					// which, to be clear, is on me: I submitted the pull request to structure the table in the readme :P 
					// but for now just fixing. This will probably be better in the API.
                    $mappic = str_replace('">Map</a>','',str_replace('<a href="','',$row["Map"]));
					// now build the row for this data...
                    echo '<tr><td><a class="link" href="default.php'.$mettag.'&route='.$row["RouteName"].'">'.$row["RouteName"].'</a></td><td>'.$row["Author"].'</td><td>'.$row["Type"].'</td><td>'.$vdistance.'</td><td>'.$velevation.'</td><td><img src=/images/dl.png class="link" height="20px" onclick="downloadResource(\'https://raw.githubusercontent.com/gtbikev/courses/master/courses/'.$row["RouteName"].'.json\',\''.$row["RouteName"].'.json\')"></td><td>'.$mapstring.'</td><td><iframe src="'.$ratinglink.'" class="embed-responsive-item" width="100%" height="20px" allowtransparency="true" style="border:0px solid black;"></iframe></td></tr>';
					echo '<tr><td colspan="8">'.$row["Description"].'</td></tr>';
 
					// but for the map link, if there's a GPX file for this route, skip the map and use route preview instead.
					if(file_exists ("./gpx/".$row["RouteName"].".gpx")) { 
						echo '<tr><td colspan="8" height=100%><iframe src="MapPreview.php?route='.$row["RouteName"].'&met='.$met.'" class="embed-responsive-item" width="1280px" height="720px" allowtransparency="true" style="border:0px solid black;"></iframe></td></tr>';
					} else {
						echo '<tr><td colspan="8" height=100%><img src="'.$mappic.'"/></td></tr>';
					}
				}
				echo "</table>"; // table opener & header row
			} else {
				echo "0 results";
			}
			$result->free_result();
			$conn->close();
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
	}
?>

<script>
const minFileThreshold = 200; 
// minimum number of bytes for valid file
// cross-domain download requires client .js to pull into a blob and reconstruct the file.  This is critical because
//  it means the file could possibly be created out of (for example) a 404 message.
//  Intent of this threshold is to throw out bad results instead of giving them to the user as a file, with no indication
//  that it's not a "real" file.

	function fltFn() {
		const defaultMet = "Metric";
		const queryString = window.location.search;
		const urlParams = new URLSearchParams(queryString);
		var rtTable,filterRoute,filterAuthor,filterType,filterRateMin,filterDistMin,filterElvMin,filterDistMax,filterElvMax,tblRow,tdRoute,tdAuthor,tdType,tdDistance,tdElv,tdRating,rowRoute,rowAuthor,rowType,rowDistance,rowElv,rowRating,met;
		met = urlParams.get('met'); // this carries user selection of the units of measurement
		if (met === null){
			met = defaultMet; // default
		}
		filterRoute = document.getElementById("myInRoute").value.toUpperCase();
		filterAuthor = document.getElementById("myInAuthor").value.toUpperCase();
		filterType = document.getElementById("myInType").value.toUpperCase();
		filterRateMin = document.getElementById("myRateMin").value*1;
		filterDistMin = document.getElementById("myDistMin").value*1;
		filterElvMin = document.getElementById("myElvMin").value*1;
		filterDistMax = document.getElementById("myDistMax").value*1;
		filterElvMax = document.getElementById("myElvMax").value*1;
		rtTable = document.getElementById("tblSummary");
		tblRow = rtTable.getElementsByTagName("tr");
		for (i = 0; i < tblRow.length; i++) {
			tdRoute = tblRow[i].getElementsByTagName("td")[0];
			if (tdRoute) {rowRoute = tdRoute.textContent || tdRoute.innerText;};
			
			tdAuthor = tblRow[i].getElementsByTagName("td")[1];
			if (tdAuthor) {rowAuthor = tdAuthor.textContent || tdAuthor.innerText;};
			
			tdType = tblRow[i].getElementsByTagName("td")[2];
			if (tdType) {rowType  = tdType .textContent || tdType .innerText;};
			
			if(met === "Imperial"){tdDistance = tblRow[i].getElementsByTagName("td")[10];} else {tdDistance = tblRow[i].getElementsByTagName("td")[9];};
			if (tdDistance) {rowDistance = tdDistance.textContent || tdDistance.innerText;};
			
			if(met === "Imperial"){tdElv = tblRow[i].getElementsByTagName("td")[12];} else {tdElv = tblRow[i].getElementsByTagName("td")[11];};
			if (tdElv) {rowElv = tdElv.textContent || tdElv.innerText;};
			
			tdRating = tblRow[i].getElementsByTagName("td")[8];
			if (tdRating) {rowRating = tdRating.textContent || tdRating.innerText;};

			if (tdRating || tdElv || tdDistance || tdType || tdAuthor || tdRoute){
				tblRow[i].style.display = "";
				if (!(rowRoute.toUpperCase().indexOf(filterRoute) > -1)){
					tblRow[i].style.display = "none";
				};
				if (!(rowAuthor.toUpperCase().indexOf(filterAuthor) > -1)){
					tblRow[i].style.display = "none";
				};
				if (!(rowType.toUpperCase().indexOf(filterType) > -1)){
					tblRow[i].style.display = "none";
				};
				if (filterRateMin > 0) {
					if (!(rowRating >= filterRateMin )) {
						tblRow[i].style.display = "none";
					};
				};
				
				if (filterDistMin > 0) {
					if (!(rowDistance >= filterDistMin )) {
						tblRow[i].style.display = "none";
					};
				};
				
				if (filterDistMax > 0) {
					if (!(rowDistance <= filterDistMax )) {
						tblRow[i].style.display = "none";
					};
				};
				
				if (filterElvMin > 0) {
					if (!(rowElv >= filterElvMin )) {
						tblRow[i].style.display = "none";
					};
				};
				
				if (filterElvMax > 0) {
					if (!(rowElv <= filterElvMax )) {
						tblRow[i].style.display = "none";
					};
				};
			};
		}
	}

function srtFn(sIndex, sOrder) {

	// sOrder: 1 asc, -1 desc

	// sIndex:
	// 10 	 - rating
	// 11 - dist
	// 13 - elv

	// text options that don't work:
	// 0 - name
	// 1 - author
	// 2 - type

	var table, rows, switching, i, x, y, shouldSwitch, sOrder, sIndex;
	table = document.getElementById("tblSummary");
	switching = true;
	while (switching) {
		switching = false;
		rows = table.rows;
		for (i = 0; i < rows.length; i++) {
			shouldSwitch = false;
			x = rows[i].getElementsByTagName("TD")[sIndex];
			y = rows[i + 1].getElementsByTagName("TD")[sIndex];
			if(x && y) {
				if (sIndex >= 8) { // all the indexes above 8 are numeric
					if ((x.innerHTML*sOrder) > (y.innerHTML*sOrder)) {
						shouldSwitch = true;
						break;
					};
	// this is the broken text sort routine.
				} else {
					if(
						(sOrder = -1 && x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase())
						|| (sOrder = 1 && x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase())
					) {
						shouldSwitch = true;
						break;
					};
	// end broken text sort routine.
				};
			};
		};
		if (shouldSwitch) {
		rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
		switching = true;
		}
	}
}

function forceDownload(blob, filename) {
	var a = document.createElement('a');
	a.download = filename;
	a.href = blob;
	document.body.appendChild(a);
	a.click();
	a.remove();
}

function downloadResource(url, filename) {
  if (!filename) filename = url.split('\\').pop().split('/').pop();
  fetch(url, {
      headers: new Headers({
        'Origin': location.origin
      }),
      mode: 'cors'
    })
    .then(response => response.blob())
    .then(blob => {
      let blobUrl = window.URL.createObjectURL(blob);
	  if(blob.size>minFileThreshold){;
		forceDownload(blobUrl, filename)
	  } else {alert("File not found on github server. This is normal for original included routes (no need to download them, they're included!).  If this was not an included route, please submit an issue in the github repository linked at https://github.com/defiancecp/GTBikeVRouteDB .  Thanks!")};
    })
    .catch(e => console.error(e));
}
</script>

</body>
</html>