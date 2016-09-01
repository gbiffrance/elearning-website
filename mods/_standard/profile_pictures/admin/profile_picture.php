<?php
/***********************************************************************/
/* ATutor															   */
/***********************************************************************/
/* Copyright (c) 2002-2010                                             */
/* Inclusive Design Institute	                                       */
/* http://atutor.ca													   */
/*																	   */
/* This program is free software. You can redistribute it and/or	   */
/* modify it under the terms of the GNU General Public License		   */
/* as published by the Free Software Foundation.					   */
/***********************************************************************/
// $Id: profile_picture.php 10605 2011-01-06 16:57:42Z cindy $
// $Id: profile_picture.php 10605 2011-01-06 16:57:42Z cindy $
define('AT_INCLUDE_PATH', '../../../../include/');
require (AT_INCLUDE_PATH.'vitals.inc.php');
admin_authenticate(AT_ADMIN_PRIV_USERS);

$member_id = intval($_GET['member_id']);

require('../save_profile_picture.php');

require(AT_INCLUDE_PATH.'../mods/_standard/profile_pictures/html/profile_picture.inc.php'); 
?>