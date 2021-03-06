<?php
	include_once('../../../meta_connect.inc.php');

	// Generate query for question's information
	$query = '
	SELECT firstborumdatabase.users.id AS usr_id,
	metaborum.questions.id AS msg_id,
	metaborum.questions.subject AS subject,
	metaborum.questions.body AS ques_body, firstborumdatabase.users.profile_picture AS ques_profile_pic, firstborumdatabase.users.first_name AS ques_asker,
	metaborum.questions.topic_id,
	metaborum.topics.name AS topic
	FROM metaborum.questions
	JOIN firstborumdatabase.users
	ON metaborum.questions.user_id = firstborumdatabase.users.id
	JOIN metaborum.topics
	ON questions.topic_id = metaborum.topics.id
	WHERE metaborum.questions.id = ' . $_GET['id'];
	$result = mysqli_query($dbc, $query);
	if($result) {
		$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
	} else {
		include('../404.shtml');
		die();
	}

	$queryCorr = "SELECT id, SUM(vote), message_id FROM `question-votes` WHERE message_id = {$_GET['id']} GROUP BY message_id";
	$resultCorr = mysqli_query($dbc, $queryCorr);

	$query2 = '
	SELECT metaborum.answers.id AS msg_id,
	firstborumdatabase.users.id AS usr_id,
	metaborum.answers.body AS msg_body, firstborumdatabase.users.profile_picture AS profile, firstborumdatabase.users.first_name AS fn,
	SUM(metaborum.`answer-votes`.vote) AS votes
	FROM metaborum.answers
	JOIN firstborumdatabase.users
	ON firstborumdatabase.users.id = metaborum.answers.user_id
	LEFT OUTER JOIN metaborum.`answer-votes`
	ON metaborum.answers.id = metaborum.`answer-votes`.question_id
	WHERE metaborum.answers.question_id = ' . $_GET['id'] . '
	GROUP BY answers.id ORDER BY SUM(metaborum.`answer-votes`.vote) DESC';
	$result2 = mysqli_query($dbc, $query2);

	if ($resultCorr) {
		define("QUESNOVOTES", mysqli_num_rows($resultCorr) == 0);
	} else {
		define('QUESNOVOTES', TRUE);
	}

	$page_title = $row['subject'];
	include('includes/header.html');
	include('includes/login_functions.inc.php');
?>
	<div class = "col-sm-10">
	<h1><?php echo $row['subject']; ?></h1>

	<table id = "question-page-table">
		<tbody>
			<tr>
				<td class = "vote-container">
				<!-- PHP Functions -->
				<?php
					function votedOnQuestion($msg_id, $vote) {
						global $dbc;
						if (isset($_SESSION['id'])) {
							$query = "SELECT vote FROM `user-message-votes` WHERE user_id = {$_SESSION['id']} AND message_id = $msg_id ORDER BY id DESC LIMIT 1"; // Select latest vote for the user for the question
							$result = mysqli_query($dbc, $query);
							return @mysqli_fetch_array($result, MYSQLI_NUM)[0] == $vote;
						}

					}

					function getUpArrow() {
						global $fillColor;
						return '<svg aria-hidden="true" class="svg-icon m0 iconArrowUpLg" width="36" height="36" viewBox="0 0 36 36"><path style = "fill:' . $fillColor . '" d="M2 26h32L18 10z"></path></svg>';
					}

					function getDownArrow() {
						global $fillColor;
						return '<svg aria-hidden="true" class="svg-icon m0 iconArrowDownLg" width="36" height="36" viewBox="0 0 36 36"><path style = "fill:' . $fillColor . '" d="M2 10h32L18 26z"></path></svg>';
					}

					function getNoAccountButton($way) {
							return "\t<button type = 'button' onclick = \"window.location.href = '/Login'\">$way</button>\n";
					}
				?>
				<?php

					$ques_id = $row['msg_id'];

					$fillColor = votedOnQuestion($ques_id, 1) ? 'lightgreen' : 'rgb(221, 221, 221)';
					$uparrow = getUpArrow();
					$noAccountVoteUpBtn = getNoAccountButton($uparrow);

					$fillColor = votedOnQuestion($ques_id, -1) ? 'lightgreen' : 'rgb(221, 221, 221)';
					$downarrow = getDownArrow();
					$noAccountVoteDownBtn = getNoAccountButton($downarrow);
					$voteupbtn = isset($_SESSION['id']) ? "<button type = 'button' id = 'ques-vote-up-btn' onclick = \"loadXMLDoc('up', {$_SESSION['id']}, $ques_id, 'ques-vote-count')\">$uparrow</button>\n" : $noAccountVoteUpBtn;
					$votedownbtn = isset($_SESSION['id']) ? "<button type = 'button' id = 'ques-vote-down-btn' onclick = \"loadXMLDoc('down', {$_SESSION['id']}, $ques_id, 'ques-vote-count')\">$downarrow</button>\n" : $noAccountVoteDownBtn;

					$rowCorr = !QUESNOVOTES ? @mysqli_fetch_array($resultCorr, MYSQLI_NUM) : array(NULL, 0);
					echo $voteupbtn;
					echo "\t\t<div class = 'vote-counter' id = 'ques-vote-count'>{$rowCorr[1]}</div>\n";
					echo $votedownbtn;

				?>
				</td>
				<td>
					<p id = "ques-body"><?php echo $row['ques_body'] ?></p>
				</td>
			</tr>
			<tr class = 'user-profile-container'>
				<?php
					if (LOGGEDIN && $_SESSION['id'] === $row['usr_id']) {
						$what_to_echo = $ques_id . '/Edit';

						echo '<td class = "modify-links">';
						echo "<a href = '$what_to_echo'>Edit</a> ";

						$what_to_echo = $ques_id . '/Delete';

						echo "<a href = '$what_to_echo'>Delete</a>";
						echo "</td>";
					}
				?>
				<td colspan = "2" class = "question-poster">
					<div>
						<a href = '<?php echo "/Users/{$row['usr_id']}"; ?>'>
							<span><?php echo $row['ques_asker'] ?></span>
						</a>
						<a href = '<?php echo "/Users/{$row['usr_id']}"; ?>'>
							<img height = '30' src = '../pages/show_image.php?image=<?php echo $row['ques_profile_pic']?>'>
						</a>
					</div>
				</td>
			</tr>
			<tr>
				<td>
					<div class = "question-tags">
						<a href = "../Topics/<?php echo $row['topic']; ?>"><?php echo $row['topic'] ?></a>
					</div>
				</td>
			</tr>
			<?php
				$n = mysqli_num_rows($result2);
				echo "<tr style = 'border-bottom-style: solid;'><td colspan = '3'><span style = 'font-size: 1.5em'>$n answers</span></td></tr>";
				$counter = 1;
				while ($row2 = mysqli_fetch_array($result2, MYSQLI_ASSOC)) {
					$ans_id = $row2['msg_id'];
					$fillColor = votedOnQuestion($row2['msg_id'], 1) ? 'lightgreen' : 'rgb(221, 221, 221)';
					$uparrow = getUpArrow();
					$noAccountVoteUpBtn = getNoAccountButton($uparrow);

					$fillColor = votedOnQuestion($row2['msg_id'], -1) ? 'lightgreen' : 'rgb(221, 221, 221)';
					$downarrow = getDownArrow();
					$noAccountVoteDownBtn = getNoAccountButton($downarrow);

					$voteupbtn = isset($_SESSION['id']) ? "\t<button type = 'button' onclick = \"loadXMLDoc('up', {$_SESSION['id']}, {$row2['msg_id']}, 'ans-$counter-vote-count')\">$uparrow</button>\n" : $noAccountVoteUpBtn;
					$votedownbtn = isset($_SESSION['id']) ? "\t\t<button type = 'button' onclick = \"loadXMLDoc('down', {$_SESSION['id']}, {$row2['msg_id']}, 'ans-$counter-vote-count')\">$downarrow</button>\n" : $noAccountVoteDownBtn;

					echo "<tr>";
					echo "<td>";
					echo $voteupbtn;

						$voteCount = $row2['votes'] == null ? 0 : $row2['votes'];

					echo "\t\t<br><div class = 'vote-counter' id = 'ans-$counter-vote-count'>$voteCount</div>";
					echo $votedownbtn;
					echo "</td>";
					// Generate query for answers' information
					echo "<td>";
					echo "\t\t<p id = \"{$row2['msg_id']}\" class = 'ans-body'>{$row2['msg_body']}</p>\n";
					echo "</td>";
					echo "</tr>\n";
					echo "<tr class = 'user-profile-container'>";
						if (LOGGEDIN && $_SESSION['id'] === $row2['usr_id']) {
							$what_to_echo = $row2['msg_id'] . '/Edit';

							echo '<td class = "modify-links">';
							echo "<a href = '$what_to_echo'>Edit</a> ";

							$what_to_echo = $row2['msg_id'] . '/Delete';

							echo "<a href = '$what_to_echo'>Delete</a>";
							echo "</td>";
						}
						echo "<td colspan = \"2\" class = \"question-poster\">
								<div>
									<a href = '/Users/{$row2['usr_id']}'>
										<span>{$row2['fn']}</span>
									</a>
									<a href = '/Users/{$row2['usr_id']}'>
										<img height = '30' src = \"../pages/show_image.php?image={row2['profile']}\">
									</a>
								</div>
							</td>
						</tr>";
						$counter++;
					}

				?>
			</tbody>
	</table>
	<h2>Your Answer</h2>
	<p>
		<textarea id = 'your-answer-ta' name = "answer" cols = '125' rows = '20'></textarea>
	</p>
	<input type = 'button' value = 'Post your Answer' onclick = 'answerQuestion(<?php echo $_GET['id']; ?>, document.getElementById("your-answer-ta").value)'>
	</form>
	<?php
		if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Handle all form submissions
			switch ($_POST['action']) {
				case "edit-question":
					if (isset($_POST['subject'])) {
						$sub = mysqli_real_escape_string($dbc, trim($_POST['subject']));
					}
					if (isset($_POST['body'])) {
						$body = mysqli_real_escape_string($dbc, trim($_POST['body']));
					}
					$id = $_POST['id'];

					$query = isset($sub) ? "UPDATE messages SET subject = '$sub', body = '$body' WHERE id = $id" : "UPDATE messages SET body = '$body' WHERE id = $id";
					mysqli_query($dbc, $query);
					break;
			}
		}

		mysqli_close($dbc);
		include('includes/footer.html');
	?>
