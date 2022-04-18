<?php

    ini_set('default_charset', 'UTF-8');
    class Calendar
    {
        /**
        ** Constructor
        **/
        public function __construct()
        {
            $this->naviHref = htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES | ENT_HTML401, 'UTF-8');
            $this->longMonth = 1; /* 0 for short month - 1 for long month */
        }
        /********************* PROPERTY ********************/
        private $dayLabels = array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
        private $currentYear = 0;
        private $currentMonth = 0;
        private $currentDay = 0;
        private $currentDate = null;
        private $daysInMonth = 0;
        private $naviHref = null;
        private $longMonth = 0;
        private $eventsObject = null;
        /********************* PUBLIC **********************/
        /**
        ** Print out the calendar
        **/
        public function show($events = null)
        {
            $year = null;
            $month = null;
            $this->eventsObject = $events;
            if (null == $year && isset($_GET['cy'])) {
                $year = htmlentities($_GET['cy'], ENT_QUOTES | ENT_HTML401, 'UTF-8');
            } elseif (null == $year) {
                $year = date("Y", time());
            }
            if ((!is_numeric($year)) || ($year == "")) {
                $year = date("Y", time());
            }
            if (null == $month && isset($_GET['cm'])) {
                $month = htmlentities($_GET['cm'], ENT_QUOTES | ENT_HTML401, 'UTF-8');
            } elseif (null == $month) {
                $month = date("m", time());
            }
            if ((!is_numeric($month)) || ($month == "")) {
                $month = date("m", time());
            }
            $this->currentYear = $year;
            $this->currentMonth = $month;
            $this->daysInMonth = $this->_daysInMonth($month, $year);
            $content = '<div id="calendar">' . "\r\n" . '<div class="calendar_box">' . "\r\n" . $this->_createNavi() . "\r\n" . '</div>' . "\r\n" . '<div class="calendar_content">' . "\r\n" . '<div class="calendar_label">' . "\r\n" . $this->_createLabels() . '</div>' . "\r\n";
            $content .= '<div class="calendar_clear"></div>' . "\r\n";
            $content .= '<div class="calendar_dates">' . "\r\n";
            $weeksInMonth = $this->_weeksInMonth($month, $year);
            // Create weeks in a month
            for ($i = 0; $i < $weeksInMonth; $i++) {
                // Create days in a week
                for ($j = 0; $j <= 6; $j++) {
                    $content .= $this->_showDay($i * 7 + $j);
                }
            }
            $content .= '</div>' . "\r\n";
            $content .= '<div class="calendar_clear"></div>' . "\r\n";
            $content .= '</div>' . "\r\n";
            $content .= '</div>' . "\r\n";
            return $content;
        }
        /********************* PRIVATE **********************/
        /**
        ** Create the calendar days
        **/
        private function _showDay($cellNumber)
        {
            if ($this->currentDay == 0) {
                $firstDayOfTheWeek = date('N', strtotime($this->currentYear . '-' . $this->currentMonth . '-01'));
                if (intval($cellNumber) == intval($firstDayOfTheWeek)) {
                    $this->currentDay = 1;
                }
            }
            if (($this->currentDay != 0) && ($this->currentDay <= $this->daysInMonth)) {
                $this->currentDate = date('Y-m-d', strtotime($this->currentYear . '-' . $this->currentMonth . '-' . ($this->currentDay)));

                $cellContent = $this->currentDay;
                $this->currentDay++;
            } else {
                $this->currentDate = null;
                $cellContent = null;
            }
            $today_day = date("d");
            $today_mon = date("m");
            $today_yea = date("Y");
            $day_link = null;
            if ($this->eventsObject) {
                $keys = array_keys(array_column($this->eventsObject, 'post_start_date'), $this->currentDate);

                if (count($keys) > 0) {
                    $day_link .= '<div class="day_events">';
                    foreach ($keys as $key) {
                        $post_title = $this->eventsObject[$key]['post_title'];
                        $post_link = $this->eventsObject[$key]['post_link'];
                        $day_link .= '<p class="day_text"><a href="'.$post_link.'">'.$post_title.'</a></p>';
                    }
                    $day_link .= '</div>';
                } else {
                    $day_link = null;
                }
            }

            $class_day = ($cellContent == $today_day && $this->currentMonth == $today_mon && $this->currentYear == $today_yea ? "calendar_today" : "calendar_days");
            return '<div class="' . $class_day . '"><div class="day_number">' . $cellContent . '</div>'.$day_link.'</div>' . "\r\n";
        }
        /**
        ** Create navigation
        **/
        private function _createNavi()
        {
            $nextMonth = $this->currentMonth == 12 ? 1 : intval($this->currentMonth)+1;
            $nextYear = $this->currentMonth == 12 ? intval($this->currentYear)+1 : $this->currentYear;
            $preMonth = $this->currentMonth == 1 ? 12 : intval($this->currentMonth)-1;
            $preYear = $this->currentMonth == 1 ? intval($this->currentYear)-1 : $this->currentYear;
            if ($this->longMonth == 1) {
                $thisMonth = date('F Y', strtotime($this->currentYear . '-' . $this->currentMonth . '-1'));
            } else {
                $thisMonth = date('M Y', strtotime($this->currentYear . '-' . $this->currentMonth . '-1'));
            }
            return '<div class="calendar_header">' . "\r\n" . '<a class="calendar_prev" href="?cm=' . sprintf('%02d', $preMonth) . '&amp;cy=' . $preYear.'">Prev</a>' . "\r\n" . '<span class="calendar_title">' . $thisMonth . '</span>' . "\r\n" . '<a class="calendar_next" href="?cm=' . sprintf("%02d", $nextMonth) . '&amp;cy=' . $nextYear . '">Next</a>' . "\r\n"  . '</div>';
        }
        /**
        ** Create calendar week labels
        **/
        private function _createLabels()
        {
            $content = '';
            foreach ($this->dayLabels as $index => $label) {
                $content .= '<div class="calendar_names">' . $label . '</div>' . "\r\n";
            }
            return $content;
        }
        /**
        ** Calculate number of weeks in a particular month
        **/
        private function _weeksInMonth($month = null, $year = null)
        {
            if (null == ($year)) {
                $year = date("Y", time());
            }
            if (null == ($month)) {
                $month = date("m", time());
            }
            // Find number of days in this month
            $daysInMonths = $this->_daysInMonth($month, $year);
            $numOfweeks = ($daysInMonths % 7 == 0 ? 0 : 1) + intval($daysInMonths / 7);
            $monthEndingDay = date('N', strtotime($year . '-' . $month . '-' . $daysInMonths));
            $monthStartDay = date('N', strtotime($year . '-' . $month . '-01'));
            if ($monthEndingDay < $monthStartDay) {
                $numOfweeks++;
            }
            return $numOfweeks;
        }
        /**
        ** Calculate number of days in a particular month
        **/
        private function _daysInMonth($month = null, $year = null)
        {
            if (null == ($year)) {
                $year = date("Y", time());
            }
            if (null == ($month)) {
                $month = date("m", time());
            }
            return date('t', strtotime($year . '-' . $month . '-01'));
        }
    }
