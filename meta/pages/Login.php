<?php # Script 12.5 - login.php #2
# This page processes the login form submission.
# The script now adds extra parameters to the setcookie() lines.

// Check if the form has been submitted:
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	// For processing the login:
	require('includes/login_functions.inc.php');

	// Need the database connection:
	file_exists('../../mysqli_connect.inc.php') ? require_once('../../mysqli_connect.inc.php') : require_once('../../../mysqli_connect.inc.php');

	// Check the login:
	list($check, $data) = check_login($dbc, $_POST['email'], $_POST['pass']);

	if ($check) { // OK!

		ini_set('session.gc_maxlifetime', 3600);

		// Set the cookies:
		session_start();
		$_SESSION['id'] = $data['id'];
		$_SESSION['first_name'] = $data['first_name'];
		$_SESSION['last_name'] = $data['last_name'];
		setcookie('dark', $data['dark'], time() + 3600, '/', '', 0, 0);
		session_regenerate_id();

		// Redirect:
		redirect_user();

	} else { // Unsuccessful!

		// Assign $data to $errors for error reporting
		// in the login_page.inc.php file.
		$errors = $data;

	}

	mysqli_close($dbc); // Close the database connection.

} // End of the main submit conditional.

// Create the page:
include('includes/login_page.inc.php');
?>