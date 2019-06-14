<?php

$page_title = "Questions";
include('includes/header.html');
?></div>
<div class = "col-sm-10">

<h1>Recent Questions</h1>

<?php 

require('includes/pagination_functions.inc.php');

define('DISPLAY', 10); // Number of records to show per page

$pages = getPagesValue('id', 'messages', 'WHERE parent_id = 0');
$start = getStartValue();

list($sort, $order_by) = getSortValue('messages');

if ($order_by != 'unanswered') {
    $q = '
    SELECT
        T1.votes AS votes, T1.msg_id AS msg_id, T1.subject AS subject, T1.date_posted AS date_posted, T1.name AS topic_name, IFNULL(T2.answers, 0) AS answers
    FROM
        (
        SELECT
            `user-message-votes`.id,
            IFNULL(SUM(`user-message-votes`.vote), 0) AS votes,
            messages.id AS msg_id,
            messages.subject,
            DATEDIFF(NOW(), messages.date_entered) AS date_posted, messages.date_entered AS de,
            topics.name
        FROM
            messages
        JOIN topics ON messages.forum_id = topics.id
        LEFT OUTER JOIN `user-message-votes` ON messages.id = `user-message-votes`.message_id
        WHERE
            messages.parent_id = 0
        GROUP BY
            messages.id
    ) T1
        LEFT OUTER JOIN(
            SELECT
                id,
                parent_id,
                COUNT(id) AS answers
            FROM
                messages
            WHERE
                parent_id != 0
            GROUP BY
                parent_id
        ) T2
    ON
        T1.msg_id = T2.parent_id
    ORDER BY
            ' . $order_by . ' LIMIT ' . $start . ', ' . DISPLAY;    

} else {
    $q = '
    SELECT
        T1.votes AS votes, T1.msg_id AS msg_id, T1.subject AS subject, T1.date_posted AS date_posted, T1.name AS topic_name, IFNULL(T2.answers, 0) AS answers
    FROM
        (
        SELECT
            `user-message-votes`.id,
            IFNULL(SUM(`user-message-votes`.vote), 0) AS votes,
            messages.id AS msg_id,
            messages.subject,
            DATEDIFF(NOW(), messages.date_entered) AS date_posted,
            topics.name
        FROM
            messages
        JOIN topics ON messages.forum_id = topics.id
        LEFT OUTER JOIN `user-message-votes` ON messages.id = `user-message-votes`.message_id
        WHERE
            messages.parent_id = 0
        GROUP BY
            messages.id
    ) T1
        LEFT OUTER JOIN(
            SELECT
                id,
                parent_id,
                COUNT(id) AS answers
            FROM
                messages
            WHERE
                parent_id != 0
            GROUP BY
                parent_id
        ) T2
    ON
        T1.msg_id = T2.parent_id
    WHERE T2.answers IS NULL
    ORDER BY T1.votes DESC
    LIMIT ' . $start . ', ' . DISPLAY;   
}

$result = mysqli_query($dbc, $q);

?>

<div class = "sorting" style = "float:right">
	<a class = "<?php echo $sort == 'top' ? 'active': ''; ?>" href = "/Questions?sort=top">Top</a>
	<a class = "<?php echo $sort == 'new' ? 'active': ''; ?>" href = "/Questions?sort=new">New</a>
	<a class = "<?php echo $sort == 'active' ? 'active': ''; ?>"href = "/Questions?sort=active">Active</a>
	<a class = "<?php echo $sort == 'unanswered' ? 'active': ''; ?>"href = "/Questions?sort=unanswered">Unanswered</a>
</div>

<?php
echo "<table id = 'latest-questions'>";
while ($row = @mysqli_fetch_array($result, MYSQLI_ASSOC)) { // Loop through the records in an associative array
	$timeelapsed = $row['date_posted'] . " days ago";

	if ($row['date_posted'] == 0) {
		$timeelapsed = "today";
	} else if ($row['date_posted'] == 1) {
		$timeelapsed = "yesterday";
	}
	echo "
	<tr>
	<td><div class = 'numbers'>Votes<span>{$row['votes']}</span></div></td>
	<td><div class = 'numbers'>Answers<span>{$row['answers']}</span></div></td>
	<td align = \"left\">
		<a href = \"Questions/{$row['msg_id']}\">{$row['subject']}</a>
		<a class = \"question-tags\" href = \"Topics/{$row['topic_name']}\">{$row['topic_name']}</a>
	</td>
	<td align = \"right\" class = 'date-diff' style = 'font-style: italic'>Asked $timeelapsed</td>
	</tr>
	";

}
echo "</table>";

@mysqli_free_result($result);
mysqli_close($dbc);

setPreviousAndNextLinks('Questions');

?>
</div>
<div class = "col-sm-2">
    <fieldset>
        <legend>Tags you are Following</legend>
            <?php 
            $q = "SELECT `followed-topics`.id, topics.name FROM `followed-topics` JOIN topics ON topics.id = `followed-topics`.topic_id WHERE `followed-topics`.id = {$_COOKIE['id']}";
            $r = mysqli_query($dbc, $q);
            while($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
                echo "<p><a>{$row['name']}</a></p>";
            } 
            ?>
        </fieldset>
        <fieldset>
            <legend>Tags you are Ignoring</legend>
            <?php 
            $q = "SELECT `ignored-topics`.id, topics.name FROM `ignored-topics` JOIN topics ON topics.id = `ignored-topics`.topic_id WHERE `ignored-topics`.id = {$_COOKIE['id']}";
            $r = mysqli_query($dbc, $q);
            while($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
                echo "<p><a>{$row['name']}</a></p>";
            }
            ?>
        </fieldset>
<?php
    @mysqli_free_result($result);
    mysqli_close($dbc);
    include('includes/footer.html');
?>

