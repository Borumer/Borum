<?php 
session_start();
$page_title = "Contact Us";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$subject = trim($_POST['subject']);
	$body = trim($_POST['body']);

	// Fetch email from database
	$q = "SELECT id, email FROM users WHERE id = {$_SESSION['id']}";
	$r = mysqli_query($dbc, $q);
	$row = mysqli_fetch_array($r, MYSQLI_NUM);
	$email = $row[1];

	include('includes/header.html');
	echo "<div class = 'col-sm-7 page-with-form-body'>";
	sendEmail($subject, $email . "<br>" . $body, "VSpoet49@gmail.com", "<h3>Thanks for contacting us! We will respond within the next 3 days.</h3>");
	include('includes/footer.html');
	exit();
}

include('includes/header.html');
echo "<div class = 'col-sm-7 page-with-form-body'>";

?>

<h1>Contact Us</h1>
<form action="" method = "post">
	<p>
		<label for="name">Name: </label>
		<input required type="text" id="subject" name="subject" size="100">
	<p>
		<label for = "subject">Subject: </label>
		<input required type = "text" id = "subject" name = "subject" size = "100">
	</p>
	<p>
		<label for = "body">Details</label>
		<textarea required name = "body" id = "body" cols = "50"></textarea>
	</p>
	<p>
		<input type = "submit" value = "Send message">
	</p>
</form>

<?php 
include('includes/footer.html'); 
?>
