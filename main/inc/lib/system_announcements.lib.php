<?php
/* For licensing terms, see /license.txt */

/**
 * Class SystemAnnouncementManager
 */
class SystemAnnouncementManager
{
    CONST VISIBLE_GUEST = 1;
    CONST VISIBLE_STUDENT = 2;
    CONST VISIBLE_TEACHER = 3;

	/**
	 * Displays all announcements
	 * @param int $visible VISIBLE_GUEST, VISIBLE_STUDENT or VISIBLE_TEACHER
	 * @param int $id The identifier of the announcement to display
	 */
	public static function display_announcements($visible, $id = -1)
    {
		$user_selected_language = api_get_interface_language();
		$db_table = Database :: get_main_table(TABLE_MAIN_SYSTEM_ANNOUNCEMENTS);
        $tbl_announcement_group = Database :: get_main_table(TABLE_MAIN_SYSTEM_ANNOUNCEMENTS_GROUPS);
		$userGroup = new UserGroup();

        $temp_user_groups = $userGroup->get_groups_by_user(api_get_user_id(),0);
        $groups = array();
        foreach ($temp_user_groups as $user_group) {
            $groups = array_merge($groups, array($user_group['id']));
            $groups = array_merge($groups, $userGroup->get_parent_groups($user_group['id']));
        }

        $groups_string = '('.implode($groups,',').')';
        $now = api_get_utc_datetime();
        $sql = "SELECT *, DATE_FORMAT(date_start,'%d-%m-%Y %h:%i:%s') AS display_date
				FROM  $db_table
				WHERE
				 	(lang='$user_selected_language' OR lang IS NULL) AND
				 	(('$now' BETWEEN date_start AND date_end) OR date_end='0000-00-00') ";

        switch ($visible) {
            case self::VISIBLE_GUEST :
                $sql .= " AND visible_guest = 1 ";
                break;
            case self::VISIBLE_STUDENT :
                $sql .= " AND visible_student = 1 ";
                break;
            case self::VISIBLE_TEACHER :
                $sql .= " AND visible_teacher = 1 ";
                break;
        }

        if (count($groups) > 0) {
            $sql .= " OR id IN (
                        SELECT announcement_id FROM $tbl_announcement_group
                        WHERE group_id in $groups_string
                    ) ";
        }
		$current_access_url_id = 1;
		if (api_is_multiple_url_enabled()) {
			$current_access_url_id = api_get_current_access_url_id();
		}
		$sql .= " AND access_url_id = '$current_access_url_id' ";
		$sql .= " ORDER BY date_start DESC LIMIT 0,7";

		$announcements = Database::query($sql);
		if (Database::num_rows($announcements) > 0) {
			$query_string = ereg_replace('announcement=[1-9]+', '', $_SERVER['QUERY_STRING']);
			$query_string = ereg_replace('&$', '', $query_string);
			$url = api_get_self();
			echo '<div class="system_announcements">';
			echo '<h3>'.get_lang('SystemAnnouncements').'</h3>';
			echo '<div style="margin:10px;text-align:right;"><a href="news_list.php">'.get_lang('More').'</a></div>';

			while ($announcement = Database::fetch_object($announcements)) {
				if ($id != $announcement->id) {
					if (strlen($query_string) > 0) {
						$show_url = 'news_list.php#'.$announcement->id;
					} else {
						$show_url = 'news_list.php#'.$announcement->id;
					}
			        $display_date = api_convert_and_format_date($announcement->display_date, DATE_FORMAT_LONG);
					echo '<a name="'.$announcement->id.'"></a>
						<div class="system_announcement">
							<div class="system_announcement_title"><a name="ann'.$announcement->id.'" href="'.$show_url.'">'.$announcement->title.'</a></div><div class="system_announcement_date">'.$display_date.'</div>
					  	</div>';
				} else {
					echo '<div class="system_announcement">
							<div class="system_announcement_title">'
								.$announcement->display_date.'
								<a name="ann'.$announcement->id.'" href="'.$url.'?'.$query_string.'#ann'.$announcement->id.'">'.$announcement->title.'</a>
							</div>';
				}
				echo '<br />';
			}
			echo '</div>';
		}
		return;
	}

    /**
     * @param $visible
     * @param $id
     * @param int $start
     * @param string $user_id
     * @return string
     */
    public static function display_all_announcements($visible, $id = -1, $start = 0,$user_id='')
    {
		$user_selected_language = api_get_interface_language();
		$start	= intval($start);
        $userGroup = new UserGroup();
	    $tbl_announcement_group = Database :: get_main_table(TABLE_MAIN_SYSTEM_ANNOUNCEMENTS_GROUPS);
	    $temp_user_groups = $userGroup->get_groups_by_user(api_get_user_id(),0);
        $groups = array();
	    foreach ($temp_user_groups as $user_group) {
	        $groups = array_merge($groups, array($user_group['id']));
	        $groups = array_merge($groups, $userGroup->get_parent_groups($user_group['id']));
	    }

	    // Checks if tables exists to not break platform not updated
	    $groups_string = '('.implode($groups,',').')';

		$db_table = Database :: get_main_table(TABLE_MAIN_SYSTEM_ANNOUNCEMENTS);
		$now  = api_get_utc_datetime();

		$sql = "SELECT * FROM ".$db_table."
				WHERE
				    (lang = '$user_selected_language' OR lang IS NULL) AND
				    ( '$now' >= date_start AND '$now' <= date_end) ";

		switch ($visible) {
			case self::VISIBLE_GUEST :
				$sql .= " AND visible_guest = 1 ";
				break;
			case self::VISIBLE_STUDENT :
				$sql .= " AND visible_student = 1 ";
				break;
			case self::VISIBLE_TEACHER :
				$sql .= " AND visible_teacher = 1 ";
				break;
		}

	    if (count($groups) > 0) {
            $sql .= " OR id IN (
                    SELECT announcement_id FROM $tbl_announcement_group
                    WHERE group_id in $groups_string
                    ) ";
	    }

		if (api_is_multiple_url_enabled()) {
			$current_access_url_id = api_get_current_access_url_id();
            $sql .= " AND access_url_id IN ('1', '$current_access_url_id')";
		}

		if(!isset($_GET['start']) || $_GET['start'] == 0) {
			$sql .= " ORDER BY date_start DESC LIMIT ".$start.",20";
		} else {
			$sql .= " ORDER BY date_start DESC LIMIT ".($start+1).",20";
		}
		$announcements = Database::query($sql);
		$content = '';
		if (Database::num_rows($announcements) > 0) {
			$query_string = ereg_replace('announcement=[1-9]+', '', $_SERVER['QUERY_STRING']);
			$query_string = ereg_replace('&$', '', $query_string);
			$url = api_get_self();
			$content .= '<div class="system_announcements">';
			$content .= '<h3>'.get_lang('SystemAnnouncements').'</h3>';
			$content .= '<table align="center">';
				$content .= '<tr>';
					$content .= '<td>';
						$content .= SystemAnnouncementManager :: display_arrow($user_id);
					$content .= '</td>';
				$content .= '</tr>';
			$content .= '</table>';
			$content .= '<table align="center" border="0" width="900px">';
			while ($announcement = Database::fetch_object($announcements)) {
				$display_date = api_convert_and_format_date($announcement->display_date, DATE_FORMAT_LONG);
				$content .= '<tr><td>';
				$content .= '<a name="'.$announcement->id.'"></a>
						<div class="system_announcement">
						<h2>'.$announcement->title.'</h2><div class="system_announcement_date">'.$display_date.'</div>
						<br />
					  	<div class="system_announcement_content">'
					  			.$announcement->content.'
						</div>
					  </div><br />';
				$content .= '</tr></td>';
			}
			$content .= '</table>';

			$content .= '<table align="center">';
				$content .= '<tr>';
					$content .= '<td>';
						$content .= SystemAnnouncementManager :: display_arrow($user_id);
					$content .= '</td>';
				$content .= '</tr>';
			$content .= '</table>';
			$content .= '</div>';
		}

		return $content;
	}

    /**
     * @param int $user_id
     * @return string
     */
    public static function display_arrow($user_id)
    {
		$start = (int)$_GET['start'];
		$nb_announcement = SystemAnnouncementManager :: count_nb_announcement($start,$user_id);
		$next = ((int)$_GET['start']+19);
		$prev = ((int)$_GET['start']-19);
		$content = '';
		if(!isset($_GET['start']) || $_GET['start'] == 0) {
			if($nb_announcement > 20) {
				$content .= '<a href="news_list.php?start='.$next.'">'.get_lang('NextBis').' >> </a>';
			}
		} else {
			echo '<a href="news_list.php?start='.$prev.'"> << '.get_lang('Prev').'</a>';
			if ($nb_announcement > 20) {
				$content .= '<a href="news_list.php?start='.$next.'">'.get_lang('NextBis').' >> </a>';
			}
		}
		return $content;
	}

    /**
     * @param int $start
     * @param string $user_id
     * @return int
     */
    public static function count_nb_announcement($start = 0, $user_id = '')
    {
		$start = intval($start);
		$visibility = api_is_allowed_to_create_course() ? self::VISIBLE_TEACHER : self::VISIBLE_STUDENT;
		$user_selected_language = api_get_interface_language();
		$db_table = Database :: get_main_table(TABLE_MAIN_SYSTEM_ANNOUNCEMENTS);
		$sql = 'SELECT id FROM '.$db_table.'
		        WHERE (lang="'.$user_selected_language.'" OR lang IS NULL) ';
		if (isset($user_id)) {
			switch ($visibility) {
				case self::VISIBLE_GUEST :
					$sql .= " AND visible_guest = 1 ";
					break;
				case self::VISIBLE_STUDENT :
					$sql .= " AND visible_student = 1 ";
					break;
				case self::VISIBLE_TEACHER :
					$sql .= " AND visible_teacher = 1 ";
					break;
			}
 		}

		$current_access_url_id = 1;
		if (api_is_multiple_url_enabled()) {
			$current_access_url_id = api_get_current_access_url_id();
		}
		$sql .= " AND access_url_id = '$current_access_url_id' ";


		$sql .= 'LIMIT '.$start.', 21';
		$announcements = Database::query($sql);
		$i = 0;
		while ($rows = Database::fetch_array($announcements)) {
			$i++;
		}
		return $i;
	}

	/**
	 * Get all announcements
	 * @return array An array with all available system announcements (as php
	 * objects)
	 */
	public static function get_all_announcements()
    {
		$table = Database :: get_main_table(TABLE_MAIN_SYSTEM_ANNOUNCEMENTS);
		$now = api_get_utc_datetime();
		$sql = "SELECT *, IF ( '$now'  >= date_start AND '$now' <= date_end, '1', '0') AS visible
				FROM $table";

		$current_access_url_id = 1;
		if (api_is_multiple_url_enabled()) {
			$current_access_url_id = api_get_current_access_url_id();
		}
		$sql .= " WHERE access_url_id = '$current_access_url_id' ";
		$sql .= " ORDER BY date_start ASC";

		$announcements = Database::query($sql);
		$all_announcements = array();
		while ($announcement = Database::fetch_object($announcements)) {
			$all_announcements[] = $announcement;
		}
		return $all_announcements;
	}

	/**
	 * Adds an announcement to the database
	 * @param string Title of the announcement
	 * @param string Content of the announcement
	 * @param string Start date (YYYY-MM-DD HH:II: SS)
	 * @param string End date (YYYY-MM-DD HH:II: SS)
	 * @param int    Whether the announcement should be visible to teachers (1) or not (0)
	 * @param int    Whether the announcement should be visible to students (1) or not (0)
	 * @param int    Whether the announcement should be visible to anonymous users (1) or not (0)
	 * @param string The language for which the announvement should be shown. Leave null for all langages
	 * @param int    Whether to send an e-mail to all users (1) or not (0)
	 * @return mixed  insert_id on success, false on failure
	 */
    public static function add_announcement(
        $title,
        $content,
        $date_start,
        $date_end,
        $visible_teacher = 0,
        $visible_student = 0,
        $visible_guest = 0,
        $lang = '',
        $send_mail = 0,
        $add_to_calendar = false,
        $sendEmailTest = false
    ) {
		$original_content = $content;
		$a_dateS = explode(' ',$date_start);
		$a_arraySD = explode('-',$a_dateS[0]);
		$a_arraySH = explode(':',$a_dateS[1]);
		$date_start_to_compare = array_merge($a_arraySD,$a_arraySH);

		$a_dateE = explode(' ',$date_end);
		$a_arrayED = explode('-',$a_dateE[0]);
		$a_arrayEH = explode(':',$a_dateE[1]);
		$date_end_to_compare = array_merge($a_arrayED,$a_arrayEH);

		$db_table = Database :: get_main_table(TABLE_MAIN_SYSTEM_ANNOUNCEMENTS);

		if (!checkdate($date_start_to_compare[1], $date_start_to_compare[2], $date_start_to_compare[0])) {
			Display :: display_normal_message(get_lang('InvalidStartDate'));
			return false;
		}

		if (($date_end_to_compare[1] ||
            $date_end_to_compare[2] ||
            $date_end_to_compare[0]) &&
            !checkdate($date_end_to_compare[1], $date_end_to_compare[2], $date_end_to_compare[0])
        ) {
			Display :: display_normal_message(get_lang('InvalidEndDate'));
			return false;
		}

		if (strlen(trim($title)) == 0) {
			Display::display_normal_message(get_lang('InvalidTitle'));

			return false;
		}

		$start = api_get_utc_datetime($date_start);
        $end = api_get_utc_datetime($date_end);

		// Fixing urls that are sent by email
		$content = str_replace('src=\"/home/', 'src=\"'.api_get_path(WEB_PATH).'home/', $content);
		$content = str_replace('file=/home/', 'file='.api_get_path(WEB_PATH).'home/', $content);

        $lang = is_null($lang) ? '' : $lang;

		$current_access_url_id = 1;
		if (api_is_multiple_url_enabled()) {
			$current_access_url_id = api_get_current_access_url_id();
		}

		$params = [
			'title' => $title,
			'content' => $content,
			'date_start' => $start,
			'date_end' => $end,
			'visible_teacher' => $visible_teacher,
			'visible_student' => $visible_student,
			'visible_guest' => $visible_guest,
			'lang' => $lang,
			'access_url_id' => $current_access_url_id,
		];

		$resultId = Database::insert($db_table, $params);

		if ($resultId) {
			if ($sendEmailTest) {
				SystemAnnouncementManager::send_system_announcement_by_email(
					$title,
					$content,
					$visible_teacher,
					$visible_student,
					$lang,
					true
				);
			} else {
				if ($send_mail == 1) {
					SystemAnnouncementManager::send_system_announcement_by_email(
						$title,
						$content,
						$visible_teacher,
						$visible_student,
						$lang
					);
				}
			}

			if ($add_to_calendar) {
				$agenda = new Agenda();
				$agenda->setType('admin');
				$agenda->addEvent(
					$date_start,
					$date_end,
					false,
					$title,
					$original_content
				);
			}

			return $resultId;

		}

		return false;
	}

    /**
    * Makes the announcement id visible only for groups in groups_array
    * @param int announcement id
    * @param array array of group id
    **/
    public static function announcement_for_groups($announcement_id, $group_array)
    {
        $tbl_announcement_group = Database:: get_main_table(
            TABLE_MAIN_SYSTEM_ANNOUNCEMENTS_GROUPS
        );
        //first delete all group associations for this announcement
        $res = Database::query(
            "DELETE FROM $tbl_announcement_group WHERE announcement_id=".intval(
                $announcement_id
            )
        );

        if ($res === false) {
            return false;
        }

        foreach ($group_array as $group_id) {
            if (intval($group_id) != 0) {
                $sql = "INSERT INTO $tbl_announcement_group SET
                        announcement_id=".intval($announcement_id).",
                        group_id=".intval($group_id);
                $res = Database::query($sql);
                if ($res === false) {

                    return false;
                }
            }
        }

        return true;
    }

    /**
    * Gets the groups of this announce
    * @param int announcement id
    * @return array array of group id
    **/
    public static function get_announcement_groups($announcement_id)
    {
        $tbl_announcement_group = Database :: get_main_table(TABLE_MAIN_SYSTEM_ANNOUNCEMENTS_GROUPS);
        $tbl_group = Database :: get_main_table(TABLE_USERGROUP);
        //first delete all group associations for this announcement

        $sql = "SELECT
                    g.id as group_id,
                    g.name as group_name
                FROM $tbl_group g , $tbl_announcement_group ag
                WHERE
                    announcement_id =".intval($announcement_id)." AND
                    ag.group_id = g.id";
        $res = Database::query($sql);
        $groups = Database::fetch_array($res);
        return $groups;
    }

	/**
	 * Updates an announcement to the database
	 * @param integer $id      : id of the announcement
	 * @param string  $title   : title of the announcement
	 * @param string  $content : content of the announcement
	 * @param array $date_start: start date of announcement (0 => day ; 1 => month ; 2 => year ; 3 => hour ; 4 => minute)
	 * @param array $date_end : end date of announcement (0 => day ; 1 => month ; 2 => year ; 3 => hour ; 4 => minute)
	 * @return	bool	True on success, false on failure
	 */
    public static function update_announcement(
        $id,
        $title,
        $content,
        $date_start,
        $date_end,
        $visible_teacher = 0,
        $visible_student = 0,
        $visible_guest = 0,
        $lang = null,
        $send_mail = 0,
        $sendEmailTest = false
    ) {
        $em = Database::getManager();
		$announcement = $em->find('ChamiloCoreBundle:SysAnnouncement', $id);

		if (!$announcement) {

			return false;
		}

		$a_dateS = explode(' ',$date_start);
		$a_arraySD = explode('-',$a_dateS[0]);
		$a_arraySH = explode(':',$a_dateS[1]);
		$date_start_to_compare = array_merge($a_arraySD,$a_arraySH);

		$a_dateE = explode(' ',$date_end);
		$a_arrayED = explode('-',$a_dateE[0]);
		$a_arrayEH = explode(':',$a_dateE[1]);
		$date_end_to_compare = array_merge($a_arrayED,$a_arrayEH);

        $lang = is_null($lang) ? '' : $lang;

		if (!checkdate($date_start_to_compare[1], $date_start_to_compare[2], $date_start_to_compare[0])) {
			Display :: display_normal_message(get_lang('InvalidStartDate'));

			return false;
		}

		if (($date_end_to_compare[1] ||
            $date_end_to_compare[2] ||
            $date_end_to_compare[0]) &&
            !checkdate($date_end_to_compare[1], $date_end_to_compare[2], $date_end_to_compare[0])
        ) {
			Display :: display_normal_message(get_lang('InvalidEndDate'));

			return false;
		}

		if (strlen(trim($title)) == 0) {
			Display::display_normal_message(get_lang('InvalidTitle'));

			return false;
		}

		$start = api_get_utc_datetime($date_start);
        $end = api_get_utc_datetime($date_end);

		// Fixing urls that are sent by email
		$content = str_replace('src=\"/home/', 'src=\"'.api_get_path(WEB_PATH).'home/', $content);
		$content = str_replace('file=/home/', 'file='.api_get_path(WEB_PATH).'home/', $content);

        if ($sendEmailTest) {
            SystemAnnouncementManager::send_system_announcement_by_email(
                $title,
                $content,
                null,
                null,
                $lang,
                $sendEmailTest
            );
        } else {
            if ($send_mail==1) {
                SystemAnnouncementManager::send_system_announcement_by_email(
                    $title,
                    $content,
                    $visible_teacher,
                    $visible_student,
                    $lang
                );
            }
        }

        $dateStart = new DateTime($start, new DateTimeZone('UTC'));
        $dateEnd = new DateTime($end, new DateTimeZone('UTC'));

        $announcement
            ->setLang($lang)
            ->setTitle($title)
            ->setContent($content)
            ->setDateStart($dateStart)
            ->setDateEnd($dateEnd)
            ->setVisibleTeacher($visible_teacher)
            ->setVisibleStudent($visible_student)
            ->setVisibleGuest($visible_guest)
            ->setAccessUrlId(api_get_current_access_url_id());

        $em->merge($announcement);
        $em->flush();

		return true;
	}

	/**
	 * Deletes an announcement
	 * @param 	int $id The identifier of the announcement that should be
	 * @return	bool	True on success, false on failure
	 */
	public static function delete_announcement($id)
    {
		$db_table = Database :: get_main_table(TABLE_MAIN_SYSTEM_ANNOUNCEMENTS);
		$id = intval($id);
		$sql = "DELETE FROM ".$db_table." WHERE id =".$id;
		$res = Database::query($sql);
		if ($res === false) {

			return false;
		}
		return true;
	}

	/**
	 * Gets an announcement
	 * @param 	int		$id The identifier of the announcement that should be
	 * @return	object	Object of class StdClass or the required class, containing the query result row
	 */
	public static function get_announcement($id)
    {
		$db_table = Database :: get_main_table(TABLE_MAIN_SYSTEM_ANNOUNCEMENTS);
		$id = intval($id);
		$sql = "SELECT * FROM ".$db_table." WHERE id = ".$id;
		$announcement = Database::fetch_object(Database::query($sql));

		return $announcement;
	}

	/**
	 * Change the visibility of an announcement
	 * @param 	int $announcement_id
	 * @param 	int $user For who should the visibility be changed
     * (possible values are VISIBLE_TEACHER, VISIBLE_STUDENT, VISIBLE_GUEST)
	 * @return 	bool	True on success, false on failure
	 */
	public static function set_visibility($announcement_id, $user, $visible)
    {
		$db_table = Database::get_main_table(TABLE_MAIN_SYSTEM_ANNOUNCEMENTS);
		$visible = intval($visible);
		$announcement_id = intval($announcement_id);

        if (!in_array($user, array(self::VISIBLE_GUEST, self::VISIBLE_STUDENT, self::VISIBLE_TEACHER))) {
            return false;
        }

		$field = ($user == self::VISIBLE_TEACHER ? 'visible_teacher' : ($user == self::VISIBLE_STUDENT ? 'visible_student' : 'visible_guest'));

		$sql = "UPDATE ".$db_table." SET ".$field." = '".$visible."'
		        WHERE id='".$announcement_id."'";
		$res = Database::query($sql);

		if ($res === false) {
			return false;
		}

		return true;
	}

	/**
	 * Send a system announcement by e-mail to all teachers/students depending on parameters
	 * @param	string	$title
	 * @param	string	$content
	 * @param	int		$teacher Whether to send to all teachers (1) or not (0)
	 * @param	int		$student Whether to send to all students (1) or not (0)
	 * @param	string	$language Language (optional, considered for all languages if left empty)
     * @param	bool	$sendEmailTest
	 * @return  bool    True if the message was sent or there was no destination matching. False on database or e-mail sending error.
	 */
	public static function send_system_announcement_by_email(
		$title,
		$content,
		$teacher,
		$student,
		$language = null,
		$sendEmailTest = false
	) {
        $content = str_replace(array('\r\n', '\n', '\r'),'', $content);
        $now = api_get_utc_datetime();

        if ($sendEmailTest) {
            MessageManager::send_message_simple(api_get_user_id(), $title, $content);

            return true;
        }

        $user_table = Database :: get_main_table(TABLE_MAIN_USER);
        if (api_is_multiple_url_enabled()) {
            $current_access_url_id = api_get_current_access_url_id();
            $url_rel_user = Database :: get_main_table(TABLE_MAIN_ACCESS_URL_REL_USER);
            $url_condition = " INNER JOIN $url_rel_user uu ON uu.user_id = u.user_id ";
        }

        if ($teacher <> 0 && $student == 0) {
			$sql = "SELECT DISTINCT u.user_id FROM $user_table u $url_condition 
					WHERE status = '1' ";
		}

		if ($teacher == 0 && $student <> 0) {
			$sql = "SELECT DISTINCT u.user_id FROM $user_table u $url_condition 
					WHERE status = '5' ";
		}

		if ($teacher<> 0 && $student <> 0) {
			$sql = "SELECT DISTINCT u.user_id FROM $user_table u $url_condition 
					WHERE 1 = 1 ";
		}

		if (!empty($language)) {
			//special condition because language was already treated for SQL insert before
			$sql .= " AND language = '".Database::escape_string($language)."' ";
		}

		if (api_is_multiple_url_enabled()) {
            $sql .= " AND access_url_id = '".$current_access_url_id."' ";
        }

        // Sent to active users.
        $sql .= " AND email <>'' AND active = 1 ";

        // Expiration date
        $sql .= " AND (expiration_date = '' OR expiration_date IS NULL OR expiration_date > '$now') ";

		if ((empty($teacher) || $teacher == '0') && (empty($student) || $student == '0')) {

            return true;
		}

		$result = Database::query($sql);
		if ($result === false) {

            return false;
		}

        $message_sent = false;
		while ($row = Database::fetch_array($result,'ASSOC')) {
            MessageManager::send_message_simple($row['user_id'], $title, $content);
            $message_sent = true;
		}

		return $message_sent; //true if at least one e-mail was sent
	}

	/**
     * Displays announcements as an slideshow
     * @param int $visible VISIBLE_GUEST, VISIBLE_STUDENT or VISIBLE_TEACHER
     * @param int $id The identifier of the announcement to display
     */
    public static function display_announcements_slider($visible, $id = null)
    {
        $user_selected_language = Database::escape_string(api_get_interface_language());
        $table = Database :: get_main_table(TABLE_MAIN_SYSTEM_ANNOUNCEMENTS);

        $cut_size = 500;
        $now = api_get_utc_datetime();

        $sql = "SELECT * FROM " . $table . "
				WHERE
				    (lang = '$user_selected_language' OR lang = '') AND
				    ('$now' >= date_start AND '$now' <= date_end) ";

        switch ($visible) {
            case self::VISIBLE_GUEST :
                $sql .= " AND visible_guest = 1 ";
                break;
            case self::VISIBLE_STUDENT :
                $sql .= " AND visible_student = 1 ";
                break;
            case self::VISIBLE_TEACHER :
                $sql .= " AND visible_teacher = 1 ";
                break;
        }

        if (isset($id) && !empty($id)) {
            $id = intval($id);
            $sql .= " AND id = $id ";
        }

        if (api_is_multiple_url_enabled()) {
            $current_url_id = api_get_current_access_url_id();
            $sql .= " AND access_url_id IN ('1', '$current_url_id') ";
        }

        $sql .= " ORDER BY date_start DESC";
        $result = Database::query($sql);
        $announcements = [];

        if (Database::num_rows($result) > 0) {
            while ($announcement = Database::fetch_object($result)) {
                $announcementData = [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'content' => $announcement->content,
                    'readMore' => null
                ];

                if (empty($id)) {
                    if (api_strlen(strip_tags($announcement->content)) > $cut_size) {
                        $announcementData['content'] = cut($announcement->content, $cut_size);
                        $announcementData['readMore'] = true;
                    }
                }

                $announcements[] = $announcementData;
            }
        }

        if (count($announcements) === 0) {
            return null;
        }

        $template = new Template(null, false, false);
        $template->assign('announcements', $announcements);

        return $template->fetch('default/announcement/slider.tpl');
    }

    /**
     * Get the HTML code for an announcement
     * @param int $announcementId The announcement ID
     * @param int $visibility The announcement visibility
     * @return string The HTML code
     */
    public static function displayAnnouncement($announcementId, $visibility)
    {
        $selectedUserLanguage = Database::escape_string(api_get_interface_language());
        $announcementTable = Database :: get_main_table(TABLE_MAIN_SYSTEM_ANNOUNCEMENTS);

        $now = api_get_utc_datetime();

        $whereConditions = [
            "(lang = ? OR lang IS NULL) " => $selectedUserLanguage,
            "AND (? >= date_start AND ? <= date_end) " => [$now, $now],
            "AND id = ? " => intval($announcementId)
        ];

        switch ($visibility) {
            case self::VISIBLE_GUEST :
                $whereConditions["AND visible_guest = ? "] = 1;
                break;
            case self::VISIBLE_STUDENT :
                $whereConditions["AND visible_student = ? "] = 1;
                break;
            case self::VISIBLE_TEACHER :
                $whereConditions["AND visible_teacher = ? "] = 1;
                break;
        }

        if (api_is_multiple_url_enabled()) {
            $whereConditions["AND access_url_id IN (1, ?) "] = api_get_current_access_url_id();
        }

        $announcement = Database::select(
            "*",
            $announcementTable,
            [
                "where" => $whereConditions,
                "order" => "date_start"
            ], 'first'
        );

        $template = new Template(null, false, false);
        $template->assign('announcement', $announcement);

        return $template->fetch('default/announcement/view.tpl');
    }
}
