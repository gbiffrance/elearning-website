<?php
/************************************************************************/
/* ATutor																*/
/************************************************************************/
/* Copyright (c) 2002-2010                                              */
/* Inclusive Design Institute                                           */
/* http://atutor.ca														*/
/*																		*/
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/************************************************************************/
// $Id: master_list.php 10197 2010-09-16 16:18:25Z greg $

define('AT_INCLUDE_PATH', '../../../include/');
require(AT_INCLUDE_PATH.'vitals.inc.php');
admin_authenticate(AT_ADMIN_PRIV_USERS);

if (!defined('AT_MASTER_LIST') || !AT_MASTER_LIST) {
	require(AT_INCLUDE_PATH.'header.inc.php');
	$msg->addInfo('MASTER_LIST_DISABLED');
	$msg->printInfos();
	require(AT_INCLUDE_PATH.'footer.inc.php');
	exit;
}


if (isset($_POST['submit'])) {
	if ($_FILES['file']['error'] == 1) { 
		$errors = array('FILE_MAX_SIZE', ini_get('upload_max_filesize'));
		$msg->addError($errors);
		header('Location: '.$_SERVER['PHP_SELF']);
		exit;
	}

	if (!$_FILES['file']['name'] || (!is_uploaded_file($_FILES['file']['tmp_name']))) {
		$msg->addError('FILE_NOT_SELECTED');
		header('Location: '.$_SERVER['PHP_SELF']);
		exit;
	}

	$fp = fopen($_FILES['file']['tmp_name'], 'r');
	if ($fp) {
		$existing_accounts = array();
		$number_of_updates = 0;

		if ($_POST['override'] > 0) {
			/* Delete all the un-created accounts. (There is no member to delete or disable). */
			$sql = "DELETE FROM ".TABLE_PREFIX."master_list WHERE member_id=0";
			$result = mysql_query($sql, $db);

			/* Get all the created accounts. (They will be disabled or deleted if not in the new list). */
			$sql = "SELECT public_field, member_id FROM ".TABLE_PREFIX."master_list";
			$result = mysql_query($sql, $db);
			$num_affected += mysql_affected_rows($db);
			if ($num_affected > 0) {
				$number_of_updated += $num_affected;
			}
			while ($row = mysql_fetch_assoc($result)) {
				$existing_accounts[$row['public_field']] = $row['member_id'];
			}
		}
		$sql = '';
		while (($row = fgetcsv($fp, 1000, ',')) !== FALSE) {
			if (count($row) != 2) {
				continue;
			}
			if (!$existing_accounts[$row[0]]) {
				$row[0] = addslashes($row[0]);
				$row[1] = md5($row[1]); // this may be hashed

				$sql = "INSERT INTO ".TABLE_PREFIX."master_list VALUES ('$row[0]', '$row[1]', 0)";
				mysql_query($sql, $db);

				write_to_log(AT_ADMIN_LOG_INSERT, 'master_list', mysql_affected_rows($db), $sql);
				$num_affected = mysql_affected_rows($db);
				if ($num_affected > 0) {
					$number_of_updated += $num_affected;
				}
			}
			unset($existing_accounts[$row[0]]);
		}
		fclose($fp);

		if (($_POST['override'] == 1) && $existing_accounts) {
			// disable missing accounts
			$existing_accounts = implode(',', $existing_accounts);

			$sql    = "UPDATE ".TABLE_PREFIX."members SET status=".AT_STATUS_DISABLED.", creation_date=creation_date, last_login=last_login WHERE member_id IN ($existing_accounts)";
			$result = mysql_query($sql, $db);
			
			write_to_log(AT_ADMIN_LOG_UPDATE, 'members', mysql_affected_rows($db), $sql);

			// un-enrol disabled accounts
			$sql    = "DELETE FROM ".TABLE_PREFIX."course_enrollment WHERE member_id IN ($existing_accounts)";
			$result = mysql_query($sql, $db);

			$num_affected = mysql_affected_rows($db);
			if ($num_affected > 0) {
				$number_of_updated += $num_affected;
			}
			write_to_log(AT_ADMIN_LOG_DELETE, 'course_enrollment', mysql_affected_rows($db), $sql);
			
		} else if ($_POST['override'] == 2) {
			// delete missing accounts
		}

		if ($number_of_updated > 0) {
			$msg->addFeedback('MASTER_LIST_UPLOADED');
		} else {
			$msg->addFeedback('MASTER_LIST_NO_CHANGES');
		}
			header('Location: '.$_SERVER['PHP_SELF']);
	}

	exit;
} else if (isset($_GET['edit'], $_GET['id'])) {
	if (substr($_GET['id'], 0, 1) != '-') {
		header('Location: '.AT_BASE_HREF.'mods/_core/users/edit_user.php?id='.$_GET['id'] . SEP . 'ml=1');
	} else {
		header('Location: '.AT_BASE_HREF.'mods/_core/users/master_list_edit.php?id='.substr($_GET['id'], 1) . SEP . 'ml=1');
	}
	exit;
} else if (isset($_GET['delete'], $_GET['id'])) {
	if (substr($_GET['id'], 0, 1) != '-') {
		header('Location: '.AT_BASE_HREF.'mods/_core/users/admin_delete.php?id='.$_GET['id'] . SEP . 'ml=1');
	} else {
		header('Location: '.AT_BASE_HREF.'mods/_core/users/master_list_delete.php?id='.substr($_GET['id'], 1) . SEP . 'ml=1');
	}
	exit;
} else if (isset($_GET['delete']) || isset($_GET['edit'])) {
	$msg->addError('NO_ITEM_SELECTED');
}

require(AT_INCLUDE_PATH.'header.inc.php');


if ($_GET['reset_filter']) {
	unset($_GET);
}
?>

<form name="importForm" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
<div class="input-form">
	<div class="row">
		<h3><?php echo _AT('update_list'); ?></h3>
		<label for="file"><?php echo _AT('file'); ?></label><br />
		<input type="file" name="file" size="40" id="file" />
	</div>
	
	<div class="row">
		<?php echo _AT('master_not_in_list'); ?><br />
		<input type="radio" name="override" id="o0" value="0" checked="checked" /><label for="o0"><?php echo _AT('leave_unchanged'); ?></label>
		<input type="radio" name="override" id="o1" value="1" /><label for="o1"><?php echo _AT('disable');     ?></label>
	</div>

	<div class="row buttons">
		<input type= "submit" name="submit" value="<?php echo _AT('upload'); ?>" />
	</div>
</div>
</form>

<?php

if (isset($_GET['status']) && ($_GET['status'] != '')) {
	if ($_GET['status'] == 1) {
		$status = ' M.member_id=0 ';
	} else {
		$status = ' M.member_id>0 ';
	}
	$page_string .= SEP.'status='.$_GET['status'];
} else {
	$status = '1';
}

if ($_GET['search']) {
	$_GET['search'] = trim($_GET['search']);
	$page_string .= SEP.'search='.urlencode($_GET['search']);
	$search = $addslashes($_GET['search']);

	$search = explode(',', $search);

	$sql = '';
	foreach ($search as $term) {
		$term = trim($term);
		$term = str_replace(array('%','_'), array('\%', '\_'), $term);
		if ($term) {
			if (strpos($term, '-') === FALSE) {
				$term = '%'.$term.'%';
				$sql .= "(M.public_field LIKE '$term') OR ";
			} else {
				// range search
				$range = explode('-', $term, 2);
				$range[0] = trim($range[0]);
				$range[1] = trim($range[1]);
				if (is_numeric($range[0]) && is_numeric($range[1])) {
					$sql .= "(M.public_field >= $range[0] AND M.public_field <= $range[1]) OR ";
				} else {
					$sql .= "(M.public_field >= '$range[0]' AND M.public_field <= '$range[1]') OR ";
				}
			}
		}
	}
	$sql = '('.substr($sql, 0, -3).')';
	$search = $sql;
} else {
	$search = '1';
}

$sql	= "SELECT COUNT(member_id) AS cnt FROM ".TABLE_PREFIX."master_list M WHERE $status AND $search";

$result = mysql_query($sql, $db);
$row = mysql_fetch_assoc($result);

$num_results = $row['cnt'];

$results_per_page = 100;
$num_pages = max(ceil($num_results / $results_per_page), 1);
$page = intval($_GET['p']);
if (!$page) {
	$page = 1;
}
$offset = ($page-1)*$results_per_page;

$sql	= "SELECT M.*, B.login, B.first_name, B.second_name, B.last_name FROM ".TABLE_PREFIX."master_list M LEFT JOIN ".TABLE_PREFIX."members B USING (member_id) WHERE $status AND $search ORDER BY M.public_field LIMIT $offset, $results_per_page";
$result = mysql_query($sql, $db);
?>

<form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<div class="input-form">
		<div class="row">
			<h3><?php echo _AT('results_found', $num_results); ?></h3>
		</div>

		<div class="row">
			<?php echo _AT('account_status'); ?><br />
			<input type="radio" name="status" value="1" id="s0" <?php if ($_GET['status'] == 1) { echo 'checked="checked"'; } ?> /><label for="s0"><?php echo _AT('not_created'); ?></label> 

			<input type="radio" name="status" value="2" id="s1" <?php if ($_GET['status'] == 2) { echo 'checked="checked"'; } ?> /><label for="s1"><?php echo _AT('created'); ?></label> 

			<input type="radio" name="status" value="" id="s" <?php if ($_GET['status'] == '') { echo 'checked="checked"'; } ?> /><label for="s"><?php echo _AT('all'); ?></label> 
		</div>

		<div class="row">
			<label for="search"><?php echo _AT('search'); ?> (<?php echo _AT('student_id'); ?>)</label><br />
			<input type="text" name="search" id="search" size="20" value="<?php echo htmlspecialchars($_GET['search']); ?>" />
		</div>

		<div class="row buttons">
			<input type="submit" name="filter" value="<?php echo _AT('filter'); ?>" />
			<input type="submit" name="reset_filter" value="<?php echo _AT('reset_filter'); ?>" />
		</div>
	</div>
</form>

<div class="paging">
	<ul>
	<?php for ($i=1; $i<=$num_pages; $i++): ?>
		<li>
			<?php if ($i == $page) : ?>
				<a class="current" href="<?php echo $_SERVER['PHP_SELF']; ?>?p=<?php echo $i.$page_string; ?>"><strong><?php echo $i; ?></strong></a>
			<?php else: ?>
				<a href="<?php echo $_SERVER['PHP_SELF']; ?>?p=<?php echo $i.$page_string; ?>"><?php echo $i; ?></a>
			<?php endif; ?>
		</li>
	<?php endfor; ?>
	</ul>
</div>


<form name="form" method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<input type="hidden" name="status" value="<?php echo $_GET['status']; ?>" />

<table summary="" class="data" rules="cols" style="width: 60%;">
<thead>
<tr>
	<th scope="col">&nbsp;</th>
	<th scope="col"><?php echo _AT('student_id'); ?></th>
	<th scope="col"><?php echo _AT('login_name'); ?></th>
	<th scope="col"><?php echo _AT('first_name'); ?></th>
	<th scope="col"><?php echo _AT('second_name'); ?></th>
	<th scope="col"><?php echo _AT('last_name'); ?></th>
</tr>
</thead>
<?php if ($num_results > 0): ?>
<tfoot>
<tr>
	<td colspan="6"><input type="submit" name="edit" value="<?php echo _AT('edit'); ?>" /> <input type="submit" name="delete" value="<?php echo _AT('delete'); ?>" /></td>
</tr>
</tfoot>
<tbody>
	<?php while($row = mysql_fetch_assoc($result)): ?>
		<tr onmousedown="document.form['m<?php echo $row['public_field']; ?>'].checked = true;rowselect(this);" id="r_<?php echo $row['public_field']; ?>">
			<td><input type="radio" name="id" value="<?php 
				if ($row['member_id']) {
					echo $row['member_id'];
				} else {
					echo '-'.$row['public_field'];
				}
				?>" id="m<?php echo $row['public_field']; ?>" /></td>
			<td><label for="m<?php echo $row['public_field']; ?>"><?php echo $row['public_field']; ?></label></td>
			<td><?php
				if ($row['member_id']) {
					echo $row['login'];
				} else {
					echo '-';
				}
				?></td>
			<td><?php
				if ($row['member_id']) {
					echo $row['first_name'];
				} else {
					echo '-';
				}
				?></td>
			<td><?php
				if ($row['member_id']) {
					echo $row['second_name'];
				} else {
					echo '-';
				}
				?></td>
			<td><?php
				if ($row['member_id']) {
					echo $row['last_name'];
				} else {
					echo '-';
				}
				?></td>
		</tr>
	<?php endwhile; ?>
</tbody>
<?php else: ?>
	<tr>
		<td colspan="6"><?php echo _AT('none_found'); ?></td>
	</tr>
<?php endif; ?>
</table>
</form>
<?php require(AT_INCLUDE_PATH.'footer.inc.php'); ?>