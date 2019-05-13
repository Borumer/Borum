<?php 
$page_title = "Borum - Topics";
include('includes/header.html');

file_exists('../../mysqli_connect.inc.php') ? require_once('../../mysqli_connect.inc.php') : require_once('../../Users/VSpoe/mysqli_connect.inc.php');
?>

<h1>Topics</h1>

<script>
	let flex = document.createElement("link");
	flex.type = "text/css";
	flex.rel = "stylesheet";
	flex.href = "../css/flex.css";
	document.head.appendChild(flex);
</script>

<?php 
/* Here is where the fun begins */
echo "<div class = 'flex-box' id = 'topic-list'>";
$query = "SELECT id, name FROM topics";
$result = mysqli_query($dbc, $query);
while ($row = mysqli_fetch_array($result, MYSQLI_NUM)) {
	echo "<li class = 'flex-item'><a href = '../Topics/$row[1]'>{$row[1]}</a></li>";
}
echo "</div>";

if (ISADMIN) {
	echo "<br><a href = 'pages/create_topic.php'>Add New Topic</a>";
}

?>

<?php
include('includes/footer.html');
?>

