<?php
/************************************************************************/
/* ATutor																*/
/************************************************************************/
/* Copyright (c) 2002-2010                                              */
/* Inclusive Design Institute                                           */
/* http://atutor.ca                                                     */
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/************************************************************************/
// $Id: course_properties.inc.php 10142 2010-08-17 19:17:26Z hwong $
if (!defined('AT_INCLUDE_PATH')) { exit; }

require_once(AT_INCLUDE_PATH.'../mods/_core/file_manager/filemanager.inc.php');
require_once(AT_INCLUDE_PATH.'../mods/_core/cats_categories/lib/admin_categories.inc.php');
require_once(AT_INCLUDE_PATH.'lib/tinymce.inc.php');
//require_once(AT_INCLUDE_PATH.'lib/course_icon.inc.php');

$_GET['show_courses'] = $addslashes(intval($_GET['show_courses']));
$_GET['current_cat'] = $addslashes(intval($_GET['current_cat']));

if (!isset($_REQUEST['setvisual']) && !isset($_REQUEST['settext'])) {
	if ($_SESSION['prefs']['PREF_CONTENT_EDITOR'] == 1) {
		$_POST['formatting'] = 1;
		$_REQUEST['settext'] = 0;
		$_REQUEST['setvisual'] = 0;

	} else if ($_SESSION['prefs']['PREF_CONTENT_EDITOR'] == 2) {
		$_POST['formatting'] = 1;
		$_POST['settext'] = 0;
		$_POST['setvisual'] = 1;

	} else { // else if == 0
		$_POST['formatting'] = 0;
		$_REQUEST['settext'] = 0;
		$_REQUEST['setvisual'] = 0;
	}
}

if (($_POST['setvisual'] && !$_POST['settext']) || $_GET['setvisual']) {
	load_editor(false, 'banner');
}

if (!isset($isadmin, $course, $db)) {
	return;	
}

if (isset($_POST['form_course'])) {

	$row['course_id']			= $_POST['course'];
	$row['title']				= $_POST['title'];
	$row['primary_language']	= $_POST['primary_language'];
	$row['member_id']			= $_POST['member_id'];
	$row['description']			= $_POST['description'];
	$row['course_dir_name']		= $_POST['course_dir_name'];
	$row['cat_id']				= $_POST['cat_id'];
	$row['content_packaging']	= $_POST['content_packaging'];

	$row['access']				= $_POST['access'];
	$row['notify']				= $_POST['notify'];

	$row['max_quota']			= $_POST['max_quota'];
	$row['max_file_size']		= $_POST['max_file_size'];

	$row['created_date']		= date('Y-m-d H:i:s');
	$row['primary_language']    = $_POST['pri_lang'];
	$row['rss']                 = $_POST['rss'];

	$row['copyright']			= $_POST['copyright'];
	$row['icon']				= $_POST['icon'];
	$row['banner']              = stripcslashes($_POST['banner']);

	if (intval($_POST['release_date'])) {
		$day_release	= intval($_POST['day_release']);
		$month_release	= intval($_POST['month_release']);
		$year_release	= intval($_POST['year_release']);
		$hour_release	= intval($_POST['hour_release']);
		$min_release	= intval($_POST['min_release']);

		if (strlen($month_release) == 1){
			$month_release = "0$month_release";
		}
		if (strlen($day_release) == 1){
			$day_release = "0$day_release";
		}
		if (strlen($hour_release) == 1){
			$hour_release = "0$hour_release";
		}
		if (strlen($min_release) == 1){
			$min_release = "0$min_release";
		}
		$row['release_date'] = "$year_release-$month_release-$day_release $hour_release:$min_release:00";
	} else {
		$row['release_date'] = 0;
	}

	if (intval($_POST['end_date'])) {
		$day_end	= intval($_POST['day_end']);
		$month_end	= intval($_POST['month_end']);
		$year_end	= intval($_POST['year_end']);
		$hour_end	= intval($_POST['hour_end']);
		$min_end	= intval($_POST['min_end']);

		if (strlen($month_end) == 1){
			$month_end = "0$month_end";
		}
		if (strlen($day_end) == 1){
			$day_end = "0$day_end";
		}
		if (strlen($hour_end) == 1){
			$hour_end = "0$hour_end";
		}
		if (strlen($min_end) == 1){
			$min_end = "0$min_end";
		}
		$row['end_date'] = "$year_end-$month_end-$day_end $hour_end:$min_end:00";
	} else {
		$row['end_date'] = 0;
	}

} else if ($course) {
	$sql	= "SELECT *, DATE_FORMAT(release_date, '%Y-%m-%d %H:%i:00') AS release_date, DATE_FORMAT(end_date, '%Y-%m-%d %H:%i:00') AS end_date  FROM ".TABLE_PREFIX."courses WHERE course_id=$course";
	$result = mysql_query($sql, $db);
	if (!($row	= mysql_fetch_assoc($result))) {
		echo _AT('no_course_found');
		return;
	}

} else {
	//new course defaults
	$row['content_packaging']	= 'top';
	$row['access']				= 'protected';
	$row['notify']				= '';
	$row['hide']				= '';

	$row['max_quota']			= AT_COURSESIZE_DEFAULT;
	$row['max_file_size']		= AT_FILESIZE_DEFAULT;

	$row['primary_language']	= $_SESSION['lang'];
	$row['created_date']		= date('Y-m-d H:i:s');
	$row['rss']                 = 0; // default to off
	$row['release_date']		= '0';
	$row['end_date']            = '0';
}
/*
if (($_POST['setvisual'] || $_POST['settext']) && !$_POST['submit']){
	$anchor =  "#banner";
} */

?>

<form method="post" action="<?php echo $_SERVER['PHP_SELF'];  ?>" name="course_form" enctype="multipart/form-data">
	<input type="hidden" name="form_course" value="true" />
	<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $_config['prof_pic_max_file_size']; ?>" />
	<input type="hidden" name="course" value="<?php echo $course; ?>" />
	<input type="hidden" name="old_access" value="<?php echo $row['access']; ?>" />
	<input type="hidden" name="created_date" value="<?php echo $row['created_date']; ?>" />
	<input type="hidden" name="show_courses" value="<?php echo $_GET['show_courses']; ?>" />
	<input type="hidden" name="current_cat" value="<?php echo $_GET['current_cat']; ?>" />
	<input type="submit" name="submit" style="display:none;"/>

<div class="input-form">
	<fieldset class="group_form"><legend class="group_form"><?php echo _AT('properties'); ?></legend>
<?php if ($isadmin): ?>
	<div class="row">
		<span class="required" title="<?php echo _AT('required_field'); ?>">*</span><label for="inst"><?php echo  _AT('instructor'); ?></label><br />
			<?php 
			$sql = "SELECT member_id, login FROM ".TABLE_PREFIX."members WHERE status=".AT_STATUS_INSTRUCTOR;
			$result = mysql_query($sql, $db);
			
			if ($instructor_row = mysql_fetch_assoc($result)) {
				echo '<select name="instructor" id="inst">';
				do {
					if ($instructor_row['member_id'] == $row['member_id']) {
						echo '<option value="'.$instructor_row['member_id'].'" selected="selected">'.$instructor_row['login'].'</option>';
					} else {
						echo '<option value="'.$instructor_row['member_id'].'">'.$instructor_row['login'].'</option>';
					}
				} while($instructor_row = mysql_fetch_assoc($result));
				echo '</select>';
			} else {
				echo '<span id="inst">'._AT('none_found').'</span>';
			}
			?>
	</div>
<?php endif; ?>

	<div class="row">
		<span class="required" title="<?php echo _AT('required_field'); ?>">*</span><label for="title"><?php echo _AT('title'); ?></label><br />
		<input type="text" id="title" name="title" size="40" value="<?php echo htmlspecialchars($row['title']); ?>" />
	</div>

	<div class="row">
		<label for="pri_lang"><?php  echo _AT('primary_language'); ?></label><br />
		<?php $languageManager->printDropdown($row['primary_language'], 'pri_lang', 'pri_lang'); ?>
	</div>

	<div class="row">
		<label for="description"><?php echo _AT('description'); ?></label><br />
		<textarea id="description" cols="45" rows="2" name="description"><?php echo $row['description']; ?></textarea>
	</div>

	<?php if ($_config['course_dir_name']): ?>
	<div class="row">
		<label for="course_dir_name"><?php echo _AT('course_dir_name'); ?></label><br />
		<input type="text" id="course_dir_name" name="course_dir_name" size="40" value="<?php echo htmlspecialchars($row['course_dir_name']); ?>" />
	</div>
	<?php endif; ?>

	<?php $categories = get_categories(); ?>
	<?php if (is_array($categories)): ?>
		<div class="row">
		<label for="cat"><?php echo _AT('category'); ?></label><br />
			<select name="category_parent" id="cat">
				<option value="0">&nbsp;&nbsp;&nbsp;[&nbsp;&nbsp;<?php echo _AT('cats_uncategorized'); ?>&nbsp;&nbsp;]&nbsp;&nbsp;&nbsp;</option>
				<?php select_categories($categories, 0, $row['cat_id'], false); ?>

			</select>
		</div>
	<?php endif; ?>

	<div class="row">
		<?php  echo _AT('export_content'); ?><br />
		<?php
			switch ($row['content_packaging']) {
				case 'none':
						$none = ' checked="checked"';
						break;

				case 'top':
						$top	 = ' checked="checked"';
						break;

				case 'all':
						$all	= ' checked="checked"';
						break;
			}
			?>
		<label><input type="radio" name="content_packaging" value="none" id="none" <?php echo $none; ?> /><?php echo _AT('content_packaging_none'); ?></label><br />
		<label><input type="radio" name="content_packaging" value="top" id="ctop"  <?php echo $top; ?> /><?php  echo _AT('content_packaging_top'); ?></label><br />
		<label><input type="radio" name="content_packaging" value="all" id="all" <?php echo $all; ?> /><?php  echo _AT('content_packaging_all'); ?></label>
	</div>

	<div class="row">
		<?php echo _AT('syndicate_announcements'); ?><br />
		<?php
				$rss_no = $rss_yes = '';

				if ($row['rss']) {
					$rss_yes = ' checked="checked"';
				} else {
					$rss_no = ' checked="checked"';
				}
		?>
		<label><input type="radio" name="rss" value="1" id="rss_y" <?php echo $rss_yes; ?> /><?php echo _AT('enable_syndicate'); ?></label><br />
		<label><input type="radio" name="rss" value="0" id="rss_n"  <?php echo $rss_no; ?> /><?php  echo _AT('disable_syndicate'); ?></label>
	</div>

	<div class="row">
		<?php echo _AT('access'); ?><br />
		<?php
				switch ($row['access']) {
					case 'public':
							$pub = ' checked="checked"';
							$disable = 'disabled="disabled"'; // disable the nofity box
							break;

					case 'protected':
							$prot	 = ' checked="checked"';
							$disable = 'disabled="disabled"'; // disable the nofity box
							break;

					case 'private':
							$priv	= ' checked="checked"';
							break;
				}

				if ($row['notify']) {
					$notify = ' checked="checked"';
				}

				if ($row['hide']) {
					$hide = ' checked="checked"';
				}
		?>
		<input type="radio" name="access" value="public" id="pub" onclick="disableNotify();" <?php echo $pub; ?> /><label for="pub"><strong> <?php echo  _AT('public'); ?>: </strong></label><?php echo  _AT('about_public'); ?><br /><br />

		<input type="radio" name="access" value="protected" id="prot" onclick="disableNotify();" <?php echo $prot; ?> /><label for="prot"><strong><?php echo  _AT('protected'); ?>:</strong></label> <?php echo _AT('about_protected'); ?><br /><br />

		<input type="radio" name="access" value="private" id="priv" onclick="enableNotify();" <?php echo $priv; ?> /><label for="priv"><strong><?php echo  _AT('private'); ?>:</strong></label> <?php echo  _AT('about_private'); ?><br />
		<input type="checkbox" name="notify" id="notify" value="1" <?php
			echo $disable;
			echo $notify; ?> /><label for="notify"><?php echo  _AT('email_approvals'); ?></label>
		<br />
		<input type="checkbox" name="hide" id="hide" value="1" <?php
		echo $disable;
		echo $hide; ?> /><label for="hide"><?php echo  _AT('hide_course'); ?></label>.
	</div>

	<div class="row">
		<?php echo _AT('release_date'); ?><br />
		<?php
			$rel_no = $rel_yes = '';

			if (intval($row['release_date'])) {
				$rel_yes = ' checked="checked"';

				$today_day   = substr($row['release_date'], 8, 2);
				$today_mon   = substr($row['release_date'], 5, 2);
				$today_year  = substr($row['release_date'], 0, 4);

				$today_hour  = substr($row['release_date'], 11, 2);
				$today_min   = substr($row['release_date'], 14, 2);
			} else {
				$rel_no = ' checked="checked"'; 
				$today_year  = date('Y');
			}

		?>

		<input type="radio" name="release_date" value="0" id="release_now" <?php echo $rel_no; ?> /> <label for="release_now"><?php echo _AT('available_immediately'); ?></label><br />


		<input type="radio" name="release_date" value="1" id="release_later" <?php echo $rel_yes; ?> /> <label for="release_later"><?php echo _AT('release_on'); ?></label> 
		<?php
			$name = '_release';
			require(AT_INCLUDE_PATH.'html/release_date.inc.php');
		?>
	</div>

	<div class="row">
		<?php echo _AT('end_date'); ?><br />
		<?php
			$end_no = $end_yes = '';

			if (intval($row['end_date'])) {
				$end_yes = ' checked="checked"';

				$today_day   = substr($row['end_date'], 8, 2);
				$today_mon   = substr($row['end_date'], 5, 2);
				$today_year  = substr($row['end_date'], 0, 4);

				$today_hour  = substr($row['end_date'], 11, 2);
				$today_min   = substr($row['end_date'], 14, 2);
			} else {
				$end_no = ' checked="checked"'; 
				$today_year  = date('Y')+1;
			}

		?>

		<input type="radio" name="end_date" value="0" id="end_now" <?php echo $end_no; ?> /> <label for="end_now"><?php echo _AT('no_end_date'); ?></label><br />

		<input type="radio" name="end_date" value="1" id="end_later" <?php echo $end_yes; ?> /> <label for="end_later"><?php echo _AT('end_on'); ?></label> 
		<?php
			$name = '_end';
			require(AT_INCLUDE_PATH.'html/release_date.inc.php');
		?>
	</div>

	<div class="row">
		<?php
			if (($_POST['setvisual'] && !$_POST['settext']) || $_GET['setvisual']){
				echo '<input type="hidden" name="setvisual" value="'.$_POST['setvisual'].'" />';
				echo '<input type="submit" name="settext" value="'._AT('switch_text').'"  class="button"/>';
			} else {
				echo '<input type="submit" name="setvisual" value="'._AT('switch_visual').'" class="button"/>';
			}
		?>
	</div>
	<div class="row">

		<label for="banner"><?php echo _AT('banner'); ?></label><br />
		<textarea id="banner" cols="45" rows="15" name="banner"><?php echo $row['banner']; ?></textarea>
	</div>

<?php if (!$course) : ?>
	<div class="row">
		<label for="initial_content"><?php echo _AT('initial_content'); ?></label><br />
		<select name="initial_content" id="initial_content" size="5">
			<option value="0"><?php echo _AT('empty'); ?></option>
			<option value="1" selected="selected"><?php echo _AT('create_basic'); ?></option>
			<?php 
			$Backup = new Backup($db);

			if ($isadmin) {
				$sql	= "SELECT course_id, title FROM ".TABLE_PREFIX."courses ORDER BY title";
			} else {
				$sql	= "SELECT course_id, title FROM ".TABLE_PREFIX."courses WHERE member_id=$_SESSION[member_id] ORDER BY title";
			}

			$result = mysql_query($sql, $db);

			if ($course_row = mysql_fetch_assoc($result)) {
				do {
					$Backup->setCourseID($course_row['course_id']);
					$list = $Backup->getAvailableList();

					if (!empty($list)) { 
						echo '<optgroup label="'. _AT('restore').': '.$course_row['title'].'">';
						foreach ($list as $list_item) {
							echo '<option value="'.$list_item['backup_id'].'_'.$list_item['course_id'].'">'.$list_item['file_name'].' - '.get_human_size($list_item['file_size']).'</option>';
						}
						echo '</optgroup>';
					}
				} while ($course_row = mysql_fetch_assoc($result));
			}
			?>
			</select>
	</div>
<?php endif; // !$course_id ?>

<?php if ($isadmin) : ?>
	<div class="row">
		<?php  echo _AT('course_quota'); ?><br />
		<?php 
			if ($row['max_quota'] == AT_COURSESIZE_UNLIMITED) { 
				$c_unlim = ' checked="checked" ';
				$c_oth2 = ' disabled="disabled" ';
			} elseif ($row['max_quota'] == AT_COURSESIZE_DEFAULT) {
				$c_def = ' checked="checked" ';
				$c_oth2 = ' disabled="disabled" ';
			} else {
				$c_oth = ' checked="checked" ';
				$c_oth2 = '';
			}

			if ($course > 0) {
				$course_size = dirsize(AT_CONTENT_DIR . $course.'/');
			} else {
				$course_size = 0;
			}

			if ($course) {
				echo _AT('current_course_size') .': '.get_human_size($course_size).'<br />'; 
			}
		?>

		<input type="radio" id="c_default" name="quota" value="<?php echo AT_COURSESIZE_DEFAULT; ?>" onclick="disableOther();" <?php echo $c_def;?> /><label for="c_default"> <?php echo _AT('default') . ' ('.get_human_size($MaxCourseSize).')'; ?></label> <br />
		<input type="radio" id="c_unlim" name="quota" value="<?php echo AT_COURSESIZE_UNLIMITED; ?>" onclick="disableOther();" <?php echo $c_unlim;?>/><label for="c_unlim"> <?php echo _AT('unlimited'); ?></label> <br />
		<input type="radio" id="c_other" name="quota" value="2" onclick="enableOther();" <?php echo $c_oth;?>/><label for="c_other"> <?php echo _AT('other'); ?> </label> - 
		<input type="text" id="quota_entered" name="quota_entered" <?php echo $c_oth2?> value="<?php if ($row['max_quota']!=AT_COURSESIZE_UNLIMITED && $row['max_quota']!=AT_COURSESIZE_DEFAULT) { echo bytes_to_megabytes($row['max_quota']); } ?>" size="4" /> <?php echo _AT('mb'); ?>
	</div>

	<div class="row">
		<?php  echo _AT('max_file_size'); ?><br />
		<?php 
			$max_allowed = megabytes_to_bytes(substr(ini_get('upload_max_filesize'), 0, -1));

			if ($row['max_file_size'] == AT_FILESIZE_DEFAULT) { 
				$f_def = ' checked="checked" ';
				$f_oth2 = ' disabled="disabled" ';
			} elseif ($row['max_file_size'] == AT_FILESIZE_SYSTEM_MAX) {
				$f_max = ' checked="checked" ';
				$f_oth2 = ' disabled="disabled" ';
			} else {
				$f_oth = ' checked="checked" ';
				$f_oth2 = '';
			}
		?>
		<input type="radio" id="f_default" name="filesize" value="<?php echo AT_FILESIZE_DEFAULT; ?>" onclick="disableOther2();" <?php echo $f_def;?> /><label for="f_default"> <?php echo _AT('default') . ' ('.get_human_size($MaxFileSize).')'; ?></label> <br />
		<input type="radio" id="f_maxallowed" name="filesize" value="<?php echo AT_FILESIZE_SYSTEM_MAX; ?>" onclick="disableOther2();" <?php echo $f_max;?>/><label for="f_maxallowed"> <?php echo _AT('max_file_size_system') . ' ('.get_human_size($max_allowed).')'; ?></label> <br />
		<input type="radio" id="f_other" name="filesize" value="2" onclick="enableOther2();" <?php echo $f_oth;?>/><label for="f_other"> <?php echo _AT('other'); ?> </label> - 
		<input type="text" id="filesize_entered" name="filesize_entered" <?php echo $f_oth2?> value="<?php if ($row['max_file_size']!=AT_FILESIZE_DEFAULT && $row['max_file_size']!=AT_FILESIZE_SYSTEM_MAX) { echo bytes_to_megabytes($row['max_file_size']); } ?>" size="4" /> <?php echo _AT('mb'); ?>
	</div>

<?php else: ?>
	<input type="hidden" name="quota" value="<?php echo $row['max_quota']; ?>" />
	<input type="hidden" name="filesize" value="<?php echo $row['max_file_size']; ?>" />
	<input type="hidden" name="tracking" value="<?php echo $row['tracking']; ?>" />
<?php endif; ?>

	<div class="row">
		<label for="copyright"><?php echo _AT('course_copyright'); ?></label><br />
		<textarea name="copyright" rows="2" cols="65" id="copyright"><?php echo $row['copyright']; ?></textarea>
	</div>
	<div class="row">
		<?php 
            if ($row['icon'] != ''): 
                $path = AT_CONTENT_DIR.$row['course_id']."/custom_icons/";
                if (file_exists($path.$row['icon'])) {
                    if (defined('AT_FORCE_GET_FILE') && AT_FORCE_GET_FILE) {
                        $custom_icon_path = 'get_course_icon.php/?id='.$row['course_id'];
                    } else {
                        $_base_href = 'content/' . $row['course_id'] . '/';
                    }
                } else {
                    $_base_href = "images/courses/";	//$_base_href = 'get_course_icon.php/?id='.$row['course_id'];
                }

            $force_get = (defined('AT_FORCE_GET_FILE') && AT_FORCE_GET_FILE) ? true : false;
            echo "<input type='hidden' name='boolForce' id='boolForce' value='$force_get' />";
        
       //include(AT_INCLUDE_PATH.'html/course_icon.inc.php');        
        ?>
		<img id="i0" src="<?php echo ($custom_icon_path=='')?$_base_href.$row['icon']:$custom_icon_path; ?>" alt="<?php echo $row['icon']; ?>" border="1" height="79" width="79"  style="float: left; margin: 2px;" />

		<?php else: ?>
			<img id="i0" src="images/clr.gif" alt="" style="float: left; margin: 2px;" border="1" height="79" width="79"  />
			<input type='hidden' name='boolForce' id='boolForce' value='' />
		<?php endif; ?>
		<div style="width:40%; float:left;">
		<label for="icons"><?php echo _AT('icon'); ?></label><br />
		<select name="icon" id="icons" onchange="SelectImg()">
			<option value=""><?php echo _AT('no_icon'); ?></option>
            <?php // ------------- custom course icons
                $path = AT_CONTENT_DIR.$row['course_id']."/custom_icons/";
                $boolCustom = false;
                $optCount = 0;

                if (is_dir($path)) {
                    $boolCustom = true;  // true if custom icons are uploaded, otherwise false
                    
                    /*$files = scandir($path);  //SCANDIR STOPS ATUTOR WHEN RUN AS INSTRUCTOR, BUT NOT AS ADMIN. WHY? -Gorzan */
                    
                    /* PHP 4 REPLACEMENT FOR SCANDIR */
					$dh  = opendir($path);
					while (false !== ($filename = readdir($dh))) {
						$files[] = $filename;
					}

					/*END PHP 4 REPLACEMENT FOR SCANDIR*/
                    echo "<optgroup label='"._AT('custom_icons')."'>";
                    foreach($files as $val) {
						$file_ext = substr(strtolower($val), -3);
                        if ($file_ext == "jpg" || $file_ext == "png" || $file_ext == "gif") {
                            $optCount++;
                            echo "<option value='".$val."'";
                            if ($val == $row['icon']) {
                                echo 'selected="selected"';
                            }
                            echo ">".$val."</option>";
                        }
                    }
                    echo "</optgroup>";
                }
                
            ?>
			<?php // ------------- other icons

				$course_imgs = array();
				if ($dir = opendir(AT_INCLUDE_PATH.'../images/courses/')) {
					while (false !== ($file = readdir($dir)) ) {
						if( ($file == '.') || ($file == '..')) { 
							continue;
						}
						$course_imgs[] = $file;
					}		
					closedir($dir);	
				}
				sort($course_imgs);
                if ($boolCustom == true) {
                    echo "<optgroup label='"._AT('builtin_icons')."'>";
                }
				foreach ($course_imgs as $file) {
					echo '<option value="' . $file . '" ';
					if ($file == $row['icon']) { 
						echo 'selected="selected"'; 
					}
					echo ' >' . $file . '</option>';	
				}
                if ($boolCustom == true) {
                    echo "</optgroup>";
                }
			?>
		</select><?php echo "&nbsp;&nbsp;&nbsp; "._AT('or'); ?>
	</div>
            <!-- div class="row" style="float:right;width:40%;">
            <?php echo _AT('upload_icon'); ?><br />
                <input type="file" name="customicon" id="customicon" value="<?php echo $_POST['customicon']; ?>"/><br />
                <small><?php echo _AT('upload_icon_text'); ?></small>
            </div -->

        <?php  require_once(AT_INCLUDE_PATH.'../mods/_core/courses/html/course_icon.inc.php'); ?>

        <br style="clear: left;" />

	</div>

    <div style="clear: both;"></div>

    

	<div class="buttons">
	        <?php
            echo "<input type='hidden' name='custOptCount' id='custOptCount' value='".$optCount."' />";
            echo "<input type='hidden' name='courseId' id='courseId' value='".$row['course_id']."' />";
		?>

		<input type="submit" name="submit" value="<?php echo _AT('save'); ?>" accesskey="s" /> 
		<input type="submit" name="cancel" value="<?php echo _AT('cancel');?>" />
	</div>
    </fieldset>
</div>

</form>
<?php

if ($_POST['customicon']) {
    echo "<script language='javascript' type='text/javascript'>document.getElementById('uploadform').focus();</script>";
}
?>

<script language="javascript" type="text/javascript">
<!--
function enableNotify() {
	document.course_form.notify.disabled = false;
	document.course_form.hide.disabled = false;
}

function disableNotify() {
	document.course_form.notify.disabled = true;
	document.course_form.hide.disabled = true;
}

function enableOther()		{ document.course_form.quota_entered.disabled = false; }
function disableOther()		{ document.course_form.quota_entered.disabled = true; }
function enableOther2()		{ document.course_form.filesize_entered.disabled = false; }
function disableOther2()	{ document.course_form.filesize_entered.disabled = true; }

function enableRelease() { 
	document.course_form.day_release.disabled = false; 
	document.course_form.month_release.disabled = false; 
	document.course_form.year_release.disabled = false; 
	document.course_form.hour_release.disabled = false; 
	document.course_form.min_release.disabled = false; 
}
function disableRelease() { 
	document.course_form.day_release.disabled = true; 
	document.course_form.month_release.disabled = true; 
	document.course_form.year_release.disabled = true; 
	document.course_form.hour_release.disabled = true; 
	document.course_form.min_release.disabled = true; 
}

function SelectImg() {
    // UPDATED by Martin Turlej - for custom course icon

    var boolForce = document.getElementById('boolForce').value;
	if (document.course_form.icon.options[document.course_form.icon.selectedIndex].value == "") {
		document.getElementById('i0').src = "images/clr.gif";
		document.getElementById('i0').alt = "";
	} else {
        var iconIndx = document.course_form.icon.selectedIndex;
        var custIndx = document.getElementById('custOptCount').value;
        var courseId = document.getElementById('courseId').value;

        // if icon is part of custom icons choose corresponding directory
        if (iconIndx <= custIndx && boolForce != '') {			
            var dir = (boolForce == 1) ? "get_course_icon.php/?id="+courseId : "/content/"+courseId+"/custom_icons/";
        } else {
            var dir = "images/courses/";
        }

		document.getElementById('i0').src = dir + document.course_form.icon.options[iconIndx].value;
		document.getElementById('i0').alt = document.course_form.icon.options[iconIndx].value;
	}
}

function toggleFrm(id) {
    if (document.getElementById(id).style.display == "none") {
		//show
		document.getElementById(id).style.display='';	

		if (id == "c_folder") {
			document.form0.new_folder_name.focus();
		} else if (id == "upload") {
			document.form0.file.focus();
		}

	} else {
		//hide
		document.getElementById(id).style.display='none';
	}
}
// -->
</script>