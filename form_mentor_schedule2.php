<?
include('header.php');
include('../config.php');

//ini_set('display_errors', 1); 

$base_url = 'http://modernguild.com/';
$schedule_read_cal = 'http://modernguild.com/sites/all/modules/guild/mg/schedule_read_cal';
$mentor_read = $base_url . 'sites/all/modules/guild/mg/mentor_read.php';
$course_read = $base_url . 'sites/all/modules/guild/mg/course_read.php';

$schedule_message_create = $base_url . 'sites/all/modules/guild/mg/schedule_message_create.php';
$schedule_message_read = $base_url . 'sites/all/modules/guild/mg/schedule_message_read.php';
$schedule_create_appt = $base_url . 'sites/all/modules/guild/mg/schedule_create_appt.php';
$schedule_appt_update = $base_url . 'sites/all/modules/guild/mg/schedule_appt_update.php';
$schedule_appt_read = $base_url . 'sites/all/modules/guild/mg/schedule_appt_read.php';
$schedule_appt_update = $base_url . 'sites/all/modules/guild/mg/schedule_appt_update.php';

$schedule_cc_create = $base_url . 'sites/all/modules/guild/mg/schedule_cc_create.php';
$schedule_cc_read = $base_url . 'sites/all/modules/guild/mg/schedule_cc_read.php';
$schedule_cc_update = $base_url . 'sites/all/modules/guild/mg/schedule_cc_update.php';

$schedule_rec_days = $base_url . 'sites/all/modules/guild/mg/schedule_rec_days.php';

//important stuff
$user_type = $_SESSION['user']['user_type'];
$user_id = $user_id_logged = $_SESSION['user']['user_id']; //whoever is logged in

if ($_GET['mode'] == 'my_calendar') {
    $appointment_phase = 'my_calendar';
}


//get info based on user type
//    echo "user_type" . $user_type;

if ($user_type == 1) { //login - protege
    $user_id_protege = $_SESSION['user']['user_id'];
    $user_id_mentor = $_REQUEST['user_id_mentor'];

    $getC = "SELECT * FROM mg_user_course WHERE user_id='" . $user_id_protege . "' AND course_status='Enrolled' LIMIT 1";
    $resC = mysql_query($getC) or die(mysql_error());
    $c = mysql_fetch_assoc($resC);

    $course_id = $c['course_id'];
    $start_date = $c['start_date']; 
    
} //if($user_type == 1) { //login - protege
else { //login - mentor
    $user_id_mentor = $_SESSION['user']['user_id'];
}
//    echo "user_id_mentor" . $user_id_mentor;

$mentor_type = $_GET['mentor_type'];

if (!$mentor_type) {
    $getC = "SELECT user_type FROM mg_user WHERE user_id='" . $user_id_mentor . "' ";
    $resC = mysql_query($getC) or die(mysql_error());
    $c = mysql_fetch_assoc($resC);
    $mentor_type = $c['user_type'];
}


//initial proposal 
if ($mentor_type == 3) { //IE_Meeting
    $user_id_mentor = $_GET['user_id_mentor'];
    $meeting_num = $_GET['meeting_num'];
    $ass_type = 'IE_Appointment';
    
} else if ($mentor_type == 4) { //LMI_Meeting
    $getLMI = "SELECT user_id_lmi FROM mg_user WHERE user_id='$user_id_logged'";
    $resLMI = mysql_query($getLMI) or die(mysql_error());

    $lmi = mysql_fetch_assoc($resLMI);
    $user_id_mentor = $lmi['user_id_lmi'];

    $ass_type = 'LMI_Meeting';
} else if ($mentor_type == 2) { //CC_Meeting    
    $mentor_id_from_protege_id = $base_url . 'sites/all/modules/guild/mg/mentor_read_from_protege_id.php?protege_id=' . $user_id;
    $user_id_mentor = file_get_contents($mentor_id_from_protege_id);
    $ass_type = 'CC_Meeting';
}

//get meeting3 and meeting4 for recommended times
if($mentor_type == '3' || $mentor_type == '4') {
    $queryMeeting = "SELECT * FROM mg_appointment where user_id_protege='$user_id_logged' AND 
        (appointment_name='CC Meeting #3' || appointment_name='CC Meeting #4')";
    $resultMeeting = mysql_query($queryMeeting) or die(mysql_error());
    
    while($m = mysql_fetch_assoc($resultMeeting)) {
        
        if($m['time_start'] != '0000-00-00 00:00:00')
            if($m['appointment_name'] == 'CC Meeting #3')
                $ccMeeting3SQL = $m['time_start'];
            else 
                $ccMeeting4SQL = $m['time_start'];
    }
}

//get ASS_ID from user_id_protege
$queryAss = "SELECT c.course_id, assignment_id, assignment_name, assignment_type, meeting_interval_days, meeting_duration_minutes
    FROM mg_course c, mg_meeting m, mg_assignment a WHERE c.course_id = '".$course_id."' AND 
    m.course_id = c.course_id AND a.meeting_id = m.meeting_id AND
    (assignment_type='CC_Meeting' || assignment_type='IE_Appointment' || assignment_type='LMI_Meeting') ORDER BY assignment_id asc";
$resultAss = mysql_query($queryAss) or die(mysql_error());

$cc_max_meetings = 1;
while ($a = mysql_fetch_assoc($resultAss)) {
    if ($ass_type == $a['assignment_type']) { //what's the ASS_ID of this appointment?? 
        if ($ass_type == 'IE_Appointment') {
            if ($meeting_num == 3) {
                $whichIEMeeting = 'IE Meeting #3';
            } else if ($meeting_num == 2) {
                $whichIEMeeting = 'IE Meeting #2';
            } else {
                $whichIEMeeting = 'IE Meeting #1';
            }

            if ($whichIEMeeting == $a['assignment_name']) {
                $currentAss = $a['assignment_id'];
                $appointment_name = $a['assignment_name'];
                $appointment_type = $a['assignment_type'];
            }
        } else if ($ass_type == 'LMI_Meeting') {
            $currentAss = $a['assignment_id'];
            $appointment_name = $a['assignment_name'];
            $appointment_type = $a['assignment_type'];
        } else if ($ass_type == 'CC_Meeting') { //
            //echo $a['assignment_id'].' '.$a['assignment_name'].'<br />';
            $ccDuration[$cc_max_meetings] = $a['meeting_duration_minutes'];
            $ccAss[$cc_max_meetings] = $a['assignment_id'];
            $ccName[$cc_max_meetings] = $a['assignment_name'];
            $ccInterval[$cc_max_meetings] = $a['meeting_interval_days'];
            $cc_max_meetings ++;
        }
    }//if($ass_type == $a['assignment_type'])
}

$debug = $_GET['debug'];

//echo 'course_id '.$course_id;
//echo 'cc_max_meetings '. $cc_max_meetings;

include('form_mentor_admin.php');
?>
<script src="media/moment.js"></script>
<script type="text/javascript" src="datepair/jquery.datepair.js"></script>
<script>
<?
if ($_REQUEST['mentor_type']) {
    $mode = 'P';
} else {
    $mode = 'M';
}
?>
    $(document).ready(function() {

        var debug = '<?=$debug?>'; 

        if(debug != 1)
            $('.hideThis').hide(); //hide debug info 

        $('#timeForm').hide(); //time picker form


        var user_id_mentor = $('#user_id_mentor').val();
        var mentor_type = '<?= $mentor_type ?>';
        var mode = '<?= $mode ?>';
        var appointment_phase;

        timeRange('cc1'); //time picker start, time picker end
        highlightOptionRow('option1'); //highlight option1 by default

        if (mode == 'M') { //login - mentor
            var send_user_id = '<?= $user_id_protege ?>';
            $('#send_user_id').val('<?= $user_id_protege ?>');
            $('#write_receive_user_id').val('<?=$user_id_protege?>');
        }
        else { //login - protege
            $('#appointment_phase').val('I');
            appointment_phase = 'I';
            
            var send_user_id = '<?= $user_id_mentor ?>';
            $('#send_user_id').val(send_user_id);
            $('#write_receive_user_id').val('<?= $user_id_mentor ?>');
        }

        if (mentor_type == '2' && appointment_phase == 'I') {
            var appointment_type = 'CC_Meeting';
            $('#appointment_type').val(appointment_type);
            ccShowOptions(); //initial CC Scheduling 
        }

        $('#write_send_user_id').val('<?=$user_id_logged?>');

        readScheduleMessage('<?= $user_id ?>', send_user_id); //read most recent text message

        showFullCalendar(user_id_mentor); //call the calendar     

        console.log('after show full calendar mentor_type ' + mentor_type); 
        $('#notes').val('after show full calendar');
        
        //LHS form 
        showScheduleForm(appointment_type);

        //reject (re-schedule) button
        $('input[id="accept"][value="reject"]').on('click', function() {
            $('#meetingOptions').show();
        });

        $('input[id="cc1_acc"][value="reject"]').on('click', function() {
            $('#cc1_options').show();
            $('#currentOption').val('ass1_proposal_1');
            $('#ass1_accept').val('reject');
            ccRejectHighlightRow('1', '1');
        });
        $('input[id="cc2_acc"][value="reject"]').on('click', function() {
            $('#cc2_options').show();
            $('#currentOption').val('ass2_proposal_1');
            $('#ass2_accept').val('reject');
            ccRejectHighlightRow('2', '1');
        });
        $('input[id="cc3_acc"][value="reject"]').on('click', function() {
            $('#cc3_options').show();
            $('#currentOption').val('ass3_proposal_1');
            $('#ass3_accept').val('reject');
            ccRejectHighlightRow('3', '1');
        });
        $('input[id="cc4_acc"][value="reject"]').on('click', function() {
            $('#cc4_options').show();
            $('#currentOption').val('ass4_proposal_1');
            $('#ass4_accept').val('reject');
            ccRejectHighlightRow('4', '1');
        });
        $('input[id="cc5_acc"][value="reject"]').on('click', function() {
            $('#cc5_options').show();
            $('#currentOption').val('ass5_proposal_1');
            $('#ass5_accept').val('reject');
            ccRejectHighlightRow('5', '1');
        });
        $('input[id="cc6_acc"][value="reject"]').on('click', function() {
            $('#cc6_options').show();
            $('#currentOption').val('ass6_proposal_1');
            $('#ass6_accept').val('reject');
            ccRejectHighlightRow('6', '1');
        });

        //accept buttons
        $('input[id="cc1_acc"][value="accept"]').on('click', function() {
            $('#cc1_options').hide();
            $('#ass1_accept').val('accept');
        });
        $('input[id="cc2_acc"][value="accept"]').on('click', function() {
            $('#cc2_options').hide();
            $('#ass2_accept').val('accept');
        });
        $('input[id="cc3_acc"][value="accept"]').on('click', function() {
            $('#cc3_options').hide();
            $('#ass3_accept').val('accept');
        });
        $('input[id="cc4_acc"][value="accept"]').on('click', function() {
            $('#cc4_options').hide();
            $('#ass4_accept').val('accept');
        });
        $('input[id="cc5_acc"][value="accept"]').on('click', function() {
            $('#cc5_options').hide();
            $('#ass5_accept').val('accept');
        });
        $('input[id="cc6_acc"][value="accept"]').on('click', function() {
            $('#cc6_options').hide();
            $('#ass6_accept').val('accept');
        });

    });

   

    function showScheduleForm(appointment_type) {

        setTimeout(function() {

            var appointment_phase = $('#appointment_phase').val();
            var status = $('#status').val();

            $('#meetingOptions').hide();
            $('#meetingList').hide();
            $('#ccAcceptReject').hide();
            $('#meetingCCOptions').hide();

            if (appointment_type == 'CC_Meeting') { //career coach meetings
          
                if (appointment_phase == 'I') {
                    $('#meetingCCOptions').show();
                    $('#ccAcceptReject').hide();
                }
                else if (appointment_phase == 'A/R') {
                    $('#meetingCCOptions').hide();
                    $('#ccAcceptReject').show();
                    $('#cc1_options').hide();
                    $('#cc2_options').hide();
                    $('#cc3_options').hide();
                    $('#cc4_options').hide();
                    $('#cc5_options').hide();
                    $('#cc6_options').hide();
                }
                else { //re-scheduling phase
                    if(status == 'Proposed')                    
                        $('#meetingList').show();
                    else if(status == 'Scheduled')
                        $('#meetingOptions').show();
                }
            } 
            else { //IE + LMI Meetings
                if (appointment_phase == 'I') {
                    $('#meetingOptions').show();
                    $('#meetingList').hide();
                }
                else if (appointment_phase == 'A/R') {
                    $('#meetingOptions').hide();
                    $('#meetingList').show();
                }
                else if (appointment_phase == 'Re') {
                    $('#meetingOptions').show();
                    $('#meetingList').hide();
                }
            }
            
        }, 100);
        
    }

    function apptRead(appointment_id) {

        showScheduleForm();

        //reset the form
        $("#meetingOptions").find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
        $("#meetingList").find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');

        $.ajax({
            url: '<?= $schedule_appt_read ?>',
            data: 'appointment_id=' + appointment_id,
            type: 'POST',
            success: function(json) {
                //alert(json);
                var appt = $.parseJSON(json);
                console.log('apptRead(' + appointment_id + ') ' + json);

                $('#opt1_date_display').html(appt.opt1_date_display);
                $('#opt1_time_display').html(appt.opt1_time_display);
                $('#opt2_date_display').html(appt.opt2_date_display);
                $('#opt2_time_display').html(appt.opt2_time_display);
                $('#opt3_date_display').html(appt.opt3_date_display);
                $('#opt3_time_display').html(appt.opt3_time_display);

                $('#appointment_name').val(appt.appointment_name);
                $('#appointment_type').val(appt.appointment_type);
                $('#status').val(appt.status);

                $('#user_id_protege').val(appt.user_id_protege);
                $('#user_id_mentor').val(appt.user_id_mentor);
                $('#user_id_last_updated').val(appt.user_id_last_updated);

                $('#proposal_1').val(appt.proposed_1);
                $('#proposal_2').val(appt.proposed_2);
                $('#proposal_3').val(appt.proposed_3);
                $('#currentAss').val(appt.assignment_id);
                $('#time_start').val(appt.time_start); 

                $('#appointment_phase').val('I'); //I = initial scheduling
                if (appt.appointment_type == 'IE_Appointment') {
                    if (appt.status == 'Proposed') {
                        $('#appointment_phase').val('A/R'); //Accept/Reject
                    }
                    else if (appt.status == 'Scheduled') {
                        $('#appointment_phase').val('Re'); //Re-Scheduling
                    }
                }
                else if (appt.appointment_type == 'LMI_Meeting') {
                    if (appt.status == 'Proposed') {
                        $('#appointment_phase').val('A/R');
                    }
                    else if (appt.status == 'Scheduled') {
                        $('#appointment_phase').val('Re');
                    }
                }
                else if (appt.appointment_type == 'CC_Meeting') {
                    if (appt.appointment_phase == 'A/R') {
                        $('#appointment_phase').val('A/R');
                    }
                    else if (appt.appointment_phase == 'Re') {
                        $('#appointment_phase').val('Re');
                    }
                    else if(appt.appointment_phase == 'Re1'){
                        $('#appointment_phase').val('Re1');
                    }
                }

                //reading messages
                var logged_user_id = '<?= $user_id ?>';
                var user_id_mentor = appt.user_id_mentor;
                var user_id_protege = appt.user_id_protege;
                var read_receive_user_id;
                var read_send_user_id;

                if (logged_user_id == user_id_protege) {
                    read_receive_user_id = user_id_protege;
                    read_send_user_id = user_id_mentor;
                }
                else {
                    read_receive_user_id = user_id_mentor;
                    read_send_user_id = user_id_protege;
                }

                $('#read_receive_user_id').val(read_receive_user_id);
                $('#read_send_user_id').val(read_send_user_id);

                $('#write_receive_user_id').val(read_send_user_id);
                $('#write_send_user_id').val(read_receive_user_id);

                readScheduleMessage(read_receive_user_id, read_send_user_id);
            }
        })
    }

    function ccApptRead(user_id_protege) {
        $.ajax({
            url: '<?= $schedule_cc_read ?>',
            data: 'user_id_protege=' + user_id_protege,
            type: 'POST',
            success: function(json) {
                var objects = $.parseJSON(json);

                console.log('ccApptRead ' + json);

                if (objects)
                    $.each(objects, function(n, appt) {
                        $('#ass' + n + '_appointment_id').val(appt.appointment_id);
                        $('#ass' + n + '_proposal_1').val(appt.proposed_1);
                        $('#cc' + n + '_date').text(appt.date_display);
                        $('#cc' + n + '_time').text(appt.time_display);
                        console.log('ccApptRead ' + n);
                    })
            }
        })
    }

    function createTimeDialog(time_range_start, time_range_end) {
        var mentor_type = '<?= $mentor_type ?>';
        var optionX = $('#currentOption').val();
        var currentMeeting = $('#currentMeeting').val();
        var appointment_type = $('#appointment_type').val();
        var appointment_phase = $('#appointment_phase').val();
        var appointment_id = $('#appointment_id').val();


        console.log('createTimeDialog() start: ' + time_range_start + ' end: ' + time_range_end + ' optionX: ' + optionX + ' currentMeeting: ' + currentMeeting);
        console.log('createTimeDialog() appointment_phase: ' + appointment_phase + ' mentor_type: ' + mentor_type + ' appointment_type: ' 
                        + appointment_type) ;

        $('.ui-dialog-buttonpane').children('button').removeClass('ui-button-text-only').addClass('grayButton');

        var currentOption = $('#currentOption').val(); 
        timeRange(currentOption); 
        

        $('#timeForm').dialog({
            modal: true,
            position: 'top',
            resizable: false,
            show: {
                effect: "explode",
                duration: 500
            },
            hide: {
                effect: "explode",
                duration: 500
            },
            buttons: {
                Save: function() {
                   
                    if(appointment_type == 'CC_Meeting') {
                        if(appointment_phase == 'I')
                            ccTransferTime(optionX); 
                        else if(appointment_phase == 'A/R')
                            ccAcceptTime(optionX);
                        else 
                            transferTimeToForm(optionX); 
                    }
                    else { //IE & LMI meetings
                        transferTimeToForm(optionX); 
                    }

                    $(this).dialog("close");
                },
                Cancel: function() {
                    $(this).dialog("close");
                },
            }
        });
    }

    function highlightOptionRow(row) {
        $('#currentOption').val(row);
        $('#option1').removeAttr('style');
        $('#option2').removeAttr('style');
        $('#option3').removeAttr('style');
        //$('#' + row).css("border", "1px solid black");
    }

    function ccRejectHighlightRow(ass, row) {
        $('#cc'+ass+'_option1').removeAttr('style');
        $('#cc'+ass+'_option2').removeAttr('style');
        $('#cc'+ass+'_option3').removeAttr('style');
        
    //    $('#cc'+ass+'_option'+row).css("border", "1px solid black");
    }
    
    //pass time_picker_start into left hand side 
    function transferTimeToForm(optionX) {
        var time_picker_start = $('#time_picker_start').val(); //selected time 
        var currentDate = $('#currentDate').val(); //current date
        var currentSQLDate = $('#currentSQLDate').val(); //date in SQL format

        $('#' + optionX + '_time').html(time_picker_start); //LHS time column
        $('#' + optionX + '_date').html(currentDate); //LHS date column 

        //put this into proposals fields 
        var thisNumber = optionX.substring(6, 7); //1 2 or 3
        $('#proposal_' + thisNumber).val(currentSQLDate + ' ' + time_picker_start);  //proposal_1 | proposal_2 | proposal_3

        //go to next option - optionX
        var nextNumber = parseInt(thisNumber) + 1;

        if (nextNumber <= 3) { //no more than 3 options
            var optionNext = 'option' + nextNumber;
            $('#currentOption').val(optionNext);
            highlightOptionRow(optionNext);
        }
        else {
            var optionNext = 'option3';
        }
    }


    function showFullCalendar(user_id_mentor) {

        user_id_mentor = $('#user_id_mentor').val();
        var user_id_protege = $('#user_id_protege').val();
        var user_id_logged = '<?= $user_id ?>';
        var phase = $('#appointment_phase').val();
        
        var eventURL = 'http://modernguild.com/sites/all/modules/guild/mg/schedule_read_cal.php?user_id_mentor=' + user_id_mentor + '&user_id_protege=' + user_id_protege + '&user_id_logged=' + user_id_logged + '&phase=' + phase;
        console.log('showFullCalendar ' + eventURL);

        var calendar = $('#calendar');
        calendar.fullCalendar('destroy'); //reset the calendar
        calendar = $('#calendar').fullCalendar({ 
            header: {
                left: 'prev',
                center: 'title',
                right: 'next'
            },
            events: eventURL,
            selectable: true,
            editable: false,
            firstDay: 1,
            eventClick: function(calEvent, jsEvent, view) { //clicking on time available
                var appointment_id = calEvent.appointment_id;
                var type = calEvent.type;
               
                console.log('eventClick: ' + calEvent.user_other + ' appid ' + appointment_id);
                console.log('calEvent.type: ' + calEvent.type + ' calEvent.appointment_type ' + calEvent.appointment_type);

                if (calEvent.type == 'Proposed' || calEvent.type == 'Scheduled') {
                    $('#appointment_id').val(appointment_id);
                     
                    if (calEvent.appointment_type == 'CC_Meeting') {
                        var user_id_mentor = $('#user_id_mentor').val();
                        
                        console.log('user_id_mentor inseide : ' + user_id_mentor);

                        if (user_id_mentor == calEvent.user_other) { 
                            var user_id_protege = $('#user_id_protege').val();    
                        } 
                        else {
                            var user_id_protege = calEvent.user_other;
                        }
                                               
                        ccApptRead(user_id_protege); //get CC appt data
                    }
                    
                    if(calEvent.appointment_type == 'LMI_Meeting' && calEvent.type == 'Scheduled') {
                        alert('To re-schedule this meeting, please call Modern Guild at 917-860-7059');
                        return false; 
                    }

                    apptRead(appointment_id); //get appt data       
                }
                else { //time available
                    var time_start = $.fullCalendar.formatDate(calEvent.start, "hh:mm tt");
                    var time_end = $.fullCalendar.formatDate(calEvent.end, "hh:mm tt");
                    var currentDate = $.fullCalendar.formatDate(calEvent.start, "MM/dd");
                    var currentSQLDate = $.fullCalendar.formatDate(calEvent.start, "yyyy-MM-dd");

                    $('#currentDate').val(currentDate);
                    $('#currentSQLDate').val(currentSQLDate);

                    $('#minTime').val(time_start);
                    $('#maxTime').val(time_end);
                }

                if (calEvent.type == 'Proposed' || calEvent.type == 'Scheduled')
                    setTimeout(function() {

                        console.log('eventClick2: ' + calEvent.user_other + ' ' + appointment_id);
                        loadUserPicture(calEvent.user_other, 'schedule_user_picture');
                        loadUserInfo(calEvent.user_other);

                        if (calEvent.type == 'Proposed' || calEvent.type == 'Scheduled') {
                            var user_id_last_updated = $('#user_id_last_updated').val();
                            var user_id_logged = '<?= $user_id ?>';

                            if (type == 'Scheduled') {      
                                $('#instructions').html('You can re-schedule the meeting here');
                                $('.acceptColumn').show();
                            }
                            else if (type == 'Proposed') {
                                if (user_id_last_updated == user_id_logged) {
                                    $('#instructions').html('Your proposed meeting times are awaiting a response from the other user');
                                    $('.acceptColumn').hide();
                                }
                                else { //you can do stuff since the other user updated the appointment
                                    $('#instructions').html('You can accept or re-schedule the appointment');
                                    $('.acceptColumn').show();
                                }
                            }
                        
                            showScheduleForm(calEvent.appointment_type);//hide show stuff here
                        }

                        showFullCalendar('refetch'); //reload calendar
                    }, 100);

                //$(this).css('border-color', 'black');

                if (calEvent.clickable) { //time available
                    
                    createTimeDialog(time_start, time_end); //open time dialog 
                }
            },
            select: function(start, end, allDay) {
                var appointment_phase = $('#appointment_phase').val();
                var appointment_type = $('#appointment_type').val();
                var today = new Date();
                var calDate = $.fullCalendar.formatDate(start, "yyyy-MM-dd HH:mm:ss");
                today = $.fullCalendar.formatDate(today, "yyyy-MM-dd HH:mm:ss");

                var currentDate = $.fullCalendar.formatDate(start, "MM/dd");
                var currentSQLDate = $.fullCalendar.formatDate(start, "yyyy-MM-dd");
                //compare gcal date to today
                if (calDate >= today) { //after today - schedule
                    
                    if (appointment_phase) {
                        $('#currentDate').val(currentDate);
                        $('#currentSQLDate').val(currentSQLDate);
                        createTimeDialog('00:00:00', '23:59:59');
                    }
                }
            },
            dayRender: function (thisDateD, cell) {
                var appointment_type = $('#appointment_type').val();
                var appointment_phase = $('#appointment_phase').val();
                
                var currentOption = $('#currentOption').val(); 
                var startDateSQL = $('#start_date').val();
                var startDateD = new Date(startDateSQL+' 00:00:00');
                
                var todayD = new Date();
                var todaySQL = $.fullCalendar.formatDate(todayD, "yyyy-MM-dd");
                var thisDaySQL = $.fullCalendar.formatDate(thisDateD, "yyyy-MM-dd");

                var thisDayWeek = thisDateD.getDay();
                
                //style info here
                var highlightBorder = "2px solid #00AEEF"; 
                var backgroundColor = "#F0FAFF";
                
    function highlightDay (thisDayWeek, cell) {
        
        cell.css("background-color", backgroundColor);
        cell.css("border-bottom", highlightBorder); //highlight days
        cell.css("border-top", highlightBorder); //highlight days

        if(thisDayWeek == 0) { //Sunday
            cell.css("border-right", highlightBorder); //highlight days
        }
        else if(thisDayWeek == 1) { //Monday
            cell.css("border-left", highlightBorder); //highlight days
        }
    }
    
    function scheduleRecDays(currentOption, thisDaySQL, cell) {
        var course_start_date = $('#start_date').val();
        var data = 'course_start_date='+course_start_date+'&current_meeting='+currentOption+'&this_day='+thisDaySQL;

        var url = '<?= $schedule_rec_days ?>?'+data;
        console.log('scheduleRecDays '+url);
        $.ajax({
            url: url,
            data: '',
            type: "POST",
            async: false,
            success: function(bool) {
                console.log('scheduleRecDays '+bool);
                if(bool == '1') highlightDay(thisDayWeek, cell);;
            },
            
        });
    }

                if(startDateSQL != '0000-00-00' && startDateSQL != '')
                    
                //CC initial scheduling 
                if(currentOption === 'cc1') {
                    var intervalField = 'interval_1';
                    var interval = $('#'+intervalField).val() - 1;
                    
                    var meetingRangeD = new Date(startDateD.getTime() + interval*24*60*60*1000);
                    var meetingRangeSQL = $.fullCalendar.formatDate(meetingRangeD, "yyyy-MM-dd");

                    scheduleRecDays(currentOption, thisDaySQL, cell);
         

                    $('#notes').val(  ' formatDate: '+thisDaySQL + "\n" + " startDateD "+startDateD+   "\n\n\
            meetingRangeSQL: "+meetingRangeSQL + ' thisDayWeek: '+thisDayWeek );
                }
                else if(currentOption == 'cc2') {
                    var intervalField = 'interval_2';
         
                    scheduleRecDays(currentOption, thisDaySQL, cell);
                }
                else if(currentOption == 'cc3') {
                    var intervalField = 'interval_3';
                    
                    scheduleRecDays(currentOption, thisDaySQL, cell);
                }
                else if(currentOption == 'cc4') {
                    var intervalField = 'interval_4';
                    
                    scheduleRecDays(currentOption, thisDaySQL, cell);
                }
                else if(currentOption == 'cc5') {
                    var intervalField = 'interval_5';
               
                    scheduleRecDays(currentOption, thisDaySQL, cell);
                }
                else if(currentOption == 'cc6') {
                    var intervalField = 'interval_6';
                    /*
                    if(recDays(currentOption, thisDaySQL)) {
                        highlightDay(thisDayWeek, cell);
                    }*/
                    
                    scheduleRecDays(currentOption, thisDaySQL, cell);
                }
                
                //IE Week / LMI Week
                if(appointment_type == 'IE_Appointment' || appointment_type == 'LMI_Meeting') {
                    
                    var ccMeeting3SQL = $('#ccMeeting3SQL').val();
                    var ccMeeting4SQL = $('#ccMeeting4SQL').val();
                    
                    var ccMeeting3D = new Date(ccMeeting3SQL);
                    var ccMeeting4D = new Date(ccMeeting4SQL);
                    
                    var meetingRangeHeadD = new Date(ccMeeting3D.getTime() + 24*60*60*1000 * 1);
                    var meetingRangeTailD = new Date(ccMeeting4D.getTime() - 24*60*60*1000 * 2);
                    
                    var meetingRangeHeadSQL = $.fullCalendar.formatDate(meetingRangeHeadD, "yyyy-MM-dd");
                    var meetingRangeTailSQL = $.fullCalendar.formatDate(meetingRangeTailD, "yyyy-MM-dd");
                    
                    if(thisDaySQL > todaySQL)
                    if(thisDaySQL >= meetingRangeHeadSQL && thisDaySQL <= meetingRangeTailSQL) {
                         cell.css("border", highlightBorder); //highlight days
                         cell.css("background-color", backgroundColor);
                    }
                    
                    $('#notes').val('if '+meetingRangeHeadSQL+' '+meetingRangeTailSQL);
                }
                
                //Re-scheduling recommended times 
                if(appointment_type == 'IE_Appointment' || appointment_type == 'CC_Meeting')
                if(appointment_phase == 'Re' || appointment_phase == 'Re1') {
                    var meetingTimeSQL = $('#time_start').val();
                    var meetingTimeD = new Date(meetingTimeSQL); 
                    
                    var meetingRangeHeadD = new Date(meetingTimeD.getTime() - 24*60*60*1000 * 1);
                    var meetingRangeTailD = new Date(meetingTimeD.getTime() + 24*60*60*1000 * 1);
                    
                    var meetingRangeHeadSQL = $.fullCalendar.formatDate(meetingRangeHeadD, "yyyy-MM-dd");
                    var meetingRangeTailSQL = $.fullCalendar.formatDate(meetingRangeTailD, "yyyy-MM-dd");
                    
                    if(thisDaySQL >= meetingRangeHeadSQL && thisDaySQL <= meetingRangeTailSQL) {
                         cell.css("border", highlightBorder); //highlight days
                         cell.css("background-color", backgroundColor);
                    }
                    
                    $('#notes').val("Rec: phase: "+appointment_phase+"\n meetingRangeHeadSQL: " +meetingRangeHeadSQL+"\n\
meetingRangeTailSQL: "+meetingRangeTailSQL);
                }
            }
        });
        
        $('.fc-button-next').find('.fc-text-arrow').html('NEXT >');
        $('.fc-button-prev').find('.fc-text-arrow').html('< PREV');
    }
    
    
    function recDays(currentOption, thisDaySQL) {
        var thisNumber = currentOption.substring(2, 3); //get the 1 in cc1, or 2 in cc2
        var interval = $('#interval_'+thisNumber).val(); //interval_1, interval_2, etc.
        
        var prevOption = parseInt(thisNumber) - 1; 
        var prevMeetingDateSQL = $('#cc'+prevOption+'_proposed').val(); //previous option
        var prevMeetingDateD = new Date(prevMeetingDateSQL); 

        var thisMeetingDateD = new Date(prevMeetingDateD.getTime() + 24*60*60*1000 * interval );  
        var meetingRangeHeadD = new Date(thisMeetingDateD.getTime() - 24*60*60*1000 * 3);
        var meetingRangeTailD = new Date(thisMeetingDateD.getTime() + 24*60*60*1000 * 3);

//        var thisMeetingDateSQL = $.fullCalendar.formatDate(thisMeetingDateD, "yyyy-MM-dd");
        var meetingRangeHeadSQL = $.fullCalendar.formatDate(meetingRangeHeadD, "yyyy-MM-dd");
        var meetingRangeTailSQL = $.fullCalendar.formatDate(meetingRangeTailD, "yyyy-MM-dd");

        $('#notes').val(  "currentOption: "+ currentOption+" interval: "+interval+"\n\
prevOption"+prevOption+"\n\
prevMeetingDateSQL: "+prevMeetingDateSQL+"\n\
meetingRangeHeadSQL: "+meetingRangeHeadSQL + "\n\
meetingRangeTailSQL: "+meetingRangeTailSQL  );

        if(thisDaySQL >= meetingRangeHeadSQL && thisDaySQL <= meetingRangeTailSQL) {
            return true;
        }
        else return false; 
    }
    
    function loadUserInfo(user_id) {
        $.ajax({
            url: '<?= $user_read ?>',
            data: 'user_id=' + user_id,
            type: "POST",
            success: function(data) {
                var u = jQuery.parseJSON(data);

                $('#schedule_user_name').html(u.first_name + ' ' + u.last_name);
                $('#schedule_user_type').html(u.user_type_value);
            }
        });
    }

    function saveEverything() {
        var body = $('#message_body').val();
        var mentor_type = '<?= $mentor_type ?>';
        var appointment_phase = $('#appointment_phase').val();
        var appointment_type = $('#appointment_type').val();

        var appointment_id = $('#appointment_id').val();
        var receive_user_id = $('#write_receive_user_id').val();
        var send_user_id = $('#write_send_user_id').val();
        var user_id_logged = '<?= $user_id_logged ?>';

        console.log('saveEverything(): receive_user_id: ' + receive_user_id + 'user_id_logged ' + user_id_logged);
        console.log('saveEverything(): mentor_type: ' + mentor_type + 'appointment_phase ' + appointment_phase);
        console.log('saveEverything(): appointment_type: ' + appointment_type + 'appointment_id ' + appointment_id);

        if (body) {
            createScheduleMessage(receive_user_id, send_user_id);
        }

        if(appointment_phase == 'I') {
            if(mentor_type == '2'){
                ccApptCreate(user_id_logged);
            }
            else if(mentor_type == '3' || mentor_type == '4'){
                createApptUser();
            }
        }
        else if(appointment_phase == 'A/R') {
            if(appointment_type == 'CC_Meeting'){
                ccApptUpdate(user_id_logged); //Accept & Reject CC meetings
            }
            else {
                updateApptUser(appointment_id);//
            }
        }
        else {
            updateApptUser(appointment_id);//
        }
        
    }


    function ccApptCreate(user_id_logged) {
        var data = $('#ccForm').serialize();
        var appointment_type = 'CC_Meeting';
        var status = 'Proposed';
        var user_id_mentor = $('#user_id_mentor').val();
        var user_id_protege = $('#user_id_protege').val();

        data += '&user_id_last_updated=' + user_id_logged + '&appointment_type=' + appointment_type + '&status=' + status + '&user_id_mentor=' + user_id_mentor + '&user_id_protege=' + user_id_protege;

        console.log('ccApptCreate() ' + data);

        $.ajax({//Process the form using $.ajax()
            type: 'POST',
            url: '<?= $schedule_cc_create ?>',
            data: data,
            success: function(json) {
                alert(json);
                $('#calendar').fullCalendar('refetchEvents');
                var return_url = 'index.php';
                window.location.href = return_url;
            }
        });
    }

    function ccApptUpdate(user_id_logged) {
        var data = $('#ccForm').serialize();
         
        var user_id_protege = $('#user_id_protege').val();
        data += '&user_id_mentor=' + user_id_logged + '&user_id_protege=' + user_id_protege;

        $('#notes').val(data);

        $.ajax({//Process the form using $.ajax()
            type: 'POST',
            url: '<?= $schedule_cc_update ?>',
            data: data,
            success: function(json) {
                alert(json); 
                $('#calendar').fullCalendar('refetchEvents');
                var return_url = 'index.php';
                window.location.href = return_url;
            }
        });
    }

    //Protege ==> IE & LMI - Initial Proposal 
    function createApptUser() {
        var user_id_protege = $('#user_id_protege').val();
        var user_id_mentor = $('#user_id_mentor').val();
        var appointment_name = '<?= $appointment_name ?>';
        var appointment_type = '<?= $appointment_type ?>';
        var ass_id = $('#currentAss').val();
        var proposal_1 = $('#proposal_1').val();
        var proposal_2 = $('#proposal_2').val();
        var proposal_3 = $('#proposal_3').val();
        var status = 'Proposed';
        var meeting_num = $('#meeting_num').val();
        var return_url = 'index.php';

        if (appointment_type == 'IE_Appointment') {
            return_url = 'form_live/form_187.php';
        }

        var data = 'appointment_name=' + appointment_name + '&appointment_type=' + appointment_type + '&user_id_protege=' + user_id_protege + '&user_id_mentor=' + user_id_mentor + '&assignment_id=' + ass_id + '&proposal_1=' + proposal_1 + '&proposal_2=' + proposal_2 + '&proposal_3=' + proposal_3 + '&status=' + status + '&meeting_num=' + meeting_num;

        console.log('createApptUser() data: ' + data);

        if (!ass_id) {
            alert("You are not enrolled in a course!");
        }
        else {
            if (!proposal_1 || !proposal_2 || !proposal_3) {
                alert('Error! You must select all 3 options!');
            }
            else {
                $.ajax({
                    url: 'http://modernguild.com/sites/all/modules/guild/mg/schedule_create_appt.php',
                    data: data,
                    type: 'POST',
                    success: function(msg) {
                        alert(msg);
                        window.location.href = return_url;
                    }
                });
            }
        }
    }

    function updateApptUser(appointment_id) {
        var accept = $('input[id="accept"]:checked').val();
        var mode;
        var data;
        var status;
        var proposed_1 = $('#proposal_1').val();
        var proposed_2 = $('#proposal_2').val();
        var proposed_3 = $('#proposal_3').val();
        var time_end = $('#time_picker_end').val();
        var appointment_type = $('#appointment_type').val();
        var appointment_phase = $('#appointment_phase').val();
        var user_id_last_updated = '<?= $user_id ?>'; //whoever is logged in

        if (appointment_phase == 'Re') { //Re-scheduling
            
            // test tp see if re-scheduling has been accepted 
            mode = 'reject';
        
            data = 'appointment_id=' + appointment_id + '&appointment_type=' + appointment_type + '&mode=' + mode + '&proposed_1=' + proposed_1 + '&proposed_2=' + proposed_2 + '&proposed_3=' + proposed_3 + '&user_id_last_updated=' + user_id_last_updated;
        }   
        else { 
            if (accept == undefined) {
                alert('You must select one option!');
            }
            else {
                if (accept == 'reject') { //reject & reschedule 3 options
                    mode = 'reject';
                   
                    data = 'appointment_id=' + appointment_id + '&appointment_type=' + appointment_type + '&mode=' + mode + '&proposed_1=' + proposed_1 + '&proposed_2=' + proposed_2 + '&proposed_3=' + proposed_3 + '&user_id_last_updated=' + user_id_last_updated;
                }
                else { //determine which option becomes the new time_start
                    var time_start;
                    mode = 'accept';
                   
                    if (accept == 'option3_accept') { //option 3
                        time_start = proposed_3;
                    }
                    else if (accept == 'option2_accept') { //option 2
                        time_start = proposed_2;
                    }
                    else { //option 1
                        time_start = proposed_1;
                    }

                    data = 'appointment_id=' + appointment_id + '&appointment_type=' + appointment_type + '&mode=' + mode + '&time_start=' + time_start + '&time_end=' + time_end + '&user_id_last_updated=' + user_id_last_updated;
                }
            }
        }
        
        $('#notes').val(data);
        console.log('updateApptUser(' + appointment_id + ') ' + data);
        $.ajax({//Process the form using $.ajax()
            type: 'POST',
            url: '<?= $schedule_appt_update ?>',
            data: data,
            success: function(json) {
                alert(json);
                $('#calendar').fullCalendar('refetchEvents');
                var return_url = 'index.php';
                window.location.href = return_url;
            }
        });
    }


    function createScheduleMessage(receive_user_id, send_user_id) {
        var body = $('#message_body').val();
        body = escape(body);

        var data = 'send_user_id=' + send_user_id + '&receive_user_id=' + receive_user_id + '&body=' + body + '&subject=Scheduling Message';
        console.log(data); //alert(data);
        $.ajax({//Process the form using $.ajax()
            type: 'POST',
            url: '<?= $schedule_message_create ?>',
            data: data,
            success: function(json) {
            //  alert(json);
            }
        });
    }

    function readScheduleMessage(receive_user_id, send_user_id) {
        $('#most_recent_message').html('');
        var data = 'receive_user_id=' + receive_user_id + '&send_user_id=' + send_user_id;
        console.log('readScheduleMessage ' + data);
        $.ajax({//Process the form using $.ajax()
            type: 'POST',
            url: '<?= $schedule_message_read ?>',
            data: data,
            success: function(json) {

                var objects = $.parseJSON(json);

                if (objects)
                    $.each(objects, function(i, e) {
                        if (e.body)
                            $('#most_recent_message').html('<p>Message from ' + e.full_name + ' (' + e.message_time + ')</p>' + e.body);
                    })
            }
        });
    }

</script>
<center>

    <div class="panel panel-primary hideThis">
        <div class="panel-body" stye="padding: 20px" class="hideThis">
            
            <p>&nbsp;</p> 

            <table  class="hideThis">
                <tr>
                    <td>
                        <textarea id="notes" cols="50" rows="7"></textarea>
                    </td>
                    <td> 
                        user_id_mentor 
                        <input type="text" class="hideThis" id="user_id_mentor" value="<?= $user_id_mentor ?>" size="4" /> <br />
                        user_id_protege
                        <input type="text" class="hideThis "id="user_id_protege" value="<?= $user_id_protege ?>" size="4" /> <br />
                        appointment_id 
                        <input type="text" class="hideThis "id="appointment_id" value="<?= $appointment_id ?>" size="4" /> <br />
                        appointment_name
                        <input type="text" class="hideThis" id="appointment_name" value="<?= $appointment_name ?>" /><br />
                        appointment_type 
                        <input type="text" class="hideThis" id="appointment_type" value="<?= $appointment_type ?>" /><br />
                        appointment_phase
                        <input type="text" class="hideThis" id="appointment_phase" value="<?= $appointment_phase ?>" /> <br />
                        status <input type="text" id="status" value="" /><br />
                    </td>
                    <td>
                        (read) send_user_id <input type="text" class="hideThis" id="read_send_user_id" value="<?= $send_user_id ?>" size="4" /><br />
                        (read) receive_user_id <input type="text" class="hideThis" id="read_receive_user_id" value="<?= $receive_user_id ?>" size="4" /><br />
                        (write) send_user_id <input type="text" class="hideThis" id="write_send_user_id" value="<?= $send_user_id ?>" size="4" /><br />
                        (write) receive_user_id <input type="text" class="hideThis" id="write_receive_user_id" value="<?= $receive_user_id ?>" size="4" /><br />
                        
                        currentAss  <input type="text" class="hideThis" id="currentAss" value="<?= $currentAss ?>" size='6' /><br />

                        currentOption <input type="text" class="hideThis" id="currentOption" value="option1" /><br />

                        currentDate <input type="text" class="hideThis" id="currentDate" size='10' /><br />

                        currentSQLDate <input type="text" class="hideThis" id="currentSQLDate" size='15' />
                    </td>
                    <td>
                        
                        user_id_logged <input type="text" id="user_id_logged" value="<?=$user_id_logged?>" /><br />
                        user_id_last_updated <input type="text" class="hideThis" id="user_id_last_updated" size="4" /><br />
                        
                        time_picker_start<br />
                        minTime <input type="text" id="minTime" value="12:00 AM" /><br />
                        maxTime <input type="text" id="maxTime" value="11:59 PM" /><br />
                        time_start <input type="text" id="time_start" value="" /><br />
                        proposal_1 <input type="text" class="hideThis" id="proposal_1" value="" /><br />

                        proposal_2 <input type="text" class="hideThis" id="proposal_2" value="" /><br />

                        proposal_3 <input type="text" class="hideThis" id="proposal_3" value="" />
                    </td>
                    <td>
                        meeting_length <input type="text" id="meeting_length" value="" /><br />
                        cc_max_meetings <input type="text" id="cc_max_meetings" value="<?= $cc_max_meetings ?>" /><br />
                        (user_id_ie) meeting_num <input type="text" id="meeting_num" value="<?=$meeting_num?>" /><br />
                        (mg_user_course) start_date <input type="text" id="start_date"  value="<?=$start_date?>" />
                    </td> 
                </tr>
            </table>

            <form id="ccForm" method="POST">
                <table class="hideThis">
                    <tr valign="top">  
<?

function ass_proposal($n) {
    echo "<td>
                CC ass $n <br />
                appointment_id <input type='text' id='ass" . $n . "_appointment_id' name='ass" . $n . "_appointment_id' size='4' /><br />
                ass" . $n . "_accept <input type='text' id='ass" . $n . "_accept' name='ass" . $n . "_accept' /> <br />
                ass" . $n . "_proposal_1 <input type='text' id='ass" . $n . "_proposal_1' name='ass" . $n . "_proposal_1' /><br />
                ass" . $n . "_proposal_2 <input type='text' id='ass" . $n . "_proposal_2' name='ass" . $n . "_proposal_2' /><br />
                ass" . $n . "_proposal_3 <input type='text' id='ass" . $n . "_proposal_3' name='ass" . $n . "_proposal_3' /><br />
            </td>";
}

for ($n = 1; $n <= 6; $n++) {
    echo ass_proposal($n);
}
?>
                    </tr>
                </table>
                
                <br />
                <table>
                    <tr>
                        <td align="center"> 
                            CC proposal_1 <br /> 
                            intro <input type="text" id="cc1_proposed" name="cc1_proposed" /><br />
                            cc1 <input type="text" id="cc2_proposed" name="cc2_proposed" /><br />
                            cc2 <input type="text" id="cc3_proposed" name="cc3_proposed" /><br />
                            cc3 <input type="text" id="cc4_proposed" name="cc4_proposed" /><br />
                            cc4 <input type="text" id="cc5_proposed" name="cc5_proposed" /><br />
                            cc5 <input type="text" id="cc6_proposed" name="cc6_proposed" />
                        </td>
                        <td width="270px" align="center">
                            CC name <br />
                            name1 <input type="text" id="appointment_name_1" name="appointment_name_1" value="<?= $ccName[1] ?>" /><br />
                            name2 <input type="text" id="appointment_name_2" name="appointment_name_2" value="<?= $ccName[2] ?>" /><br />
                            name3 <input type="text" id="appointment_name_3" name="appointment_name_3" value="<?= $ccName[3] ?>" /><br />
                            name4 <input type="text" id="appointment_name_4" name="appointment_name_4" value="<?= $ccName[4] ?>" /><br />
                            name5 <input type="text" id="appointment_name_5" name="appointment_name_5" value="<?= $ccName[5] ?>" /><br />
                            name6 <input type="text" id="appointment_name_6" name="appointment_name_6" value="<?= $ccName[6] ?>" /><br />      
                        </td>
                         <td align="center">
                            CC ass_id <br />
                            ass1 <input type="text" id="assignment_id_1" name="assignment_id_1" size="4" value="<?= $ccAss[1] ?>" /><br />
                            ass2 <input type="text" id="assignment_id_2" name="assignment_id_2" size="4" value="<?= $ccAss[2] ?>" /><br />
                            ass3 <input type="text" id="assignment_id_3" name="assignment_id_3" size="4" value="<?= $ccAss[3] ?>" /><br />
                            ass4 <input type="text" id="assignment_id_4" name="assignment_id_4" size="4" value="<?= $ccAss[4] ?>" /><br />
                            ass5 <input type="text" id="assignment_id_5" name="assignment_id_5" size="4" value="<?= $ccAss[5] ?>" /><br />
                            ass6 <input type="text" id="assignment_id_6" name="assignment_id_6" size="4" value="<?= $ccAss[6] ?>" /><br />
                        </td>
                        <td>
                            meeting_duration_minutes<br />
                            <?
                            for($d = 1; $d <= 6; $d ++) {
                                echo 'duration_'.$d.' <input type="text" id="duration_'.$d.'" value="'.$ccDuration[$d].'" size="4" /><br />';
                            }
                            ?>
                            
                        </td>
                        <td>
                            meeting_interval_days <br />
                            interval_1 <input type="text" id="interval_1" name="interval_1" value="<?= $ccInterval[1]?>" size="4" /><br />
                            interval_2 <input type="text" id="interval_2" name="interval_2" value="<?= $ccInterval[2]?>" size="4" /><br />
                            interval_3 <input type="text" id="interval_3" name="interval_3" value="<?= $ccInterval[3]?>" size="4" /><br />
                            interval_4 <input type="text" id="interval_4" name="interval_4" value="<?= $ccInterval[4]?>" size="4" /><br />
                            interval_5 <input type="text" id="interval_5" name="interval_5" value="<?= $ccInterval[5]?>" size="4" /><br />
                            interval_6 <input type="text" id="interval_6" name="interval_6" value="<?= $ccInterval[6]?>" size="4" /><br />
                        </td>
                        <td>
                            recommended meeting times <br />
                            ccMeeting3SQL <input type="text" id="ccMeeting3SQL" name="ccMeeting3SQL" value="<?=$ccMeeting3SQL?>" /><br />
                            ccMeeting4SQL <input type="text" id="ccMeeting4SQL" name="ccMeeting3SQL" value="<?=$ccMeeting4SQL?>" /><br />
                            prevMeetingSQL <input type="text" id="prevMeetingSQL" name="prevMeetingSQL" /><br />
                        </td>
                    </tr>
                </table>
            </form>

        </div>
    </div>
    
    <p>&nbsp;</p>

    <table id="masterCalendar">
        <tr valign="top">
            <td width="220px">

                <div class="whiteBox" style="margin-right: 20px;">
                    <div class="whiteBoxInner">
                    <table>
                        <tr valign="middle">
                            <td><img id="schedule_user_picture" width="80px" /></td>
                            <td><span id="schedule_user_name" class="large"></span><br />
                                <span id="schedule_user_type" class="lightBlue medium bold"></span>
                            </td>
                        </tr>
                    </table>
                    </div>
                </div>

                <br /><br />
                
                <div class="hideThis">
                    <input type="button" onclick="ccHighlightOptionRow('cc1')" value="row1" />
                    <input type="button" onclick="ccHighlightOptionRow('cc2')" value="row2" />
                    <input type="button" onclick="ccHighlightOptionRow('cc3')" value="row3" />
                    <input type="button" onclick="ccHighlightOptionRow('cc4')" value="row4" />
                    <input type="button" onclick="ccHighlightOptionRow('cc5')" value="row5" />
                    <input type="button" onclick="ccHighlightOptionRow('cc6')" value="row6" />
                </div>
                
                 
                <div class="whiteBoxInner">
                    <? include('form_mentor_options.php'); ?>
                </div>
                
            </td>
            <td>
                <div class="whiteBox">
                    <div id="calendar"></div>

                    <br />
                    &nbsp; &nbsp; <img src="css/images/blueBox.jpg" /> 
                </div>
                
            </td>
        </tr>
    </table>
    
    <style>
        .ui-draggable .ui-dialog-titlebar {
            display: none;
        }
     
        .ui-resizable-n, .ui-resizable-e, .ui-resizable-w, .ui-resizable-s, .ui-corner-all,
        .ui-dialog, .ui-dialog > * {
             background-color: #777;
        }
      
        .ui-widget-content {
            background-color: #777;
        }
        
        #timeForm, #timeForm > * {
            background-color: #777;
        }
        
        #timeForm > * {
            color: white;
            font: 1em 'Lucida Sans Unicode';
            font-weight: bold;
        }
        
        .ui-timepicker-select, #start_end .end {
            width: 250px;
            color: gray;
            border: 1px gray solid;
            
        }
        
        .ui-timepicker-select > option {
            opacity: 0.4;
            filter: alpha(opacity=40); /* For IE8 and earlier */
        }
        
        .ui-timepicker-list {
            opacity: 0.4;
            filter: alpha(opacity=40); /* For IE8 and earlier */
        }
        
        .styled option {
            
            background: transparent;
            filter:alpha(opacity=60); /* IE */
            -moz-opacity:0.6; /* Mozilla */
            opacity: 0.6; /* CSS3 */
            background-color: black;
            padding: 5px;
            font-size: 16px;
            line-height: 1;
            border: 0;
            border-radius: 0;
            height: 34px;
            -webkit-appearance: none;
            }
    </style>
    
    
    <form id="timeForm" title="Pick a Time">
        <br />
        
            
        <table id="start_end" class="medium" cellpadding="7">
            <tr>
                <td align="left"><p>START</p>
                    <div class="styled">
                    <input type="text" class="time start" name="time_picker_start" id="time_picker_start" size="20" value="12:00 AM" />
                    </div>
                </td>
            </tr>
            <tr>
                <td align="left">
                     <p>END</p>
            <input type="text" class="time end" id="time_picker_end" size="20" disabled value="12:30 AM" />
                </td>
            </tr>
        </table> 
        <br />  
    </form>

    <p>&nbsp;</p>

</center>
<script>


    function timeRange(currentOption) {
        var meeting_duration_minutes;

        switch(currentOption) {
            default: 
            case 'cc1':
                meeting_duration_minutes = 'duration_1';
                break;
            case 'cc2':
                meeting_duration_minutes = 'duration_2';
                break; 
            case 'cc3':
                meeting_duration_minutes = 'duration_3';
                break;
            case 'cc4':
                meeting_duration_minutes = 'duration_4';
                break;
            case 'cc5':
                meeting_duration_minutes = 'duration_5';
                break;
            case 'cc6':
                meeting_duration_minutes = 'duration_6';
                break;
        }
     
        var numMinutes = $('#'+meeting_duration_minutes).val(); 
        if (numMinutes == '') numMinutes = 30;
     
        $('#start_end .start').timepicker({
            'timeFormat': 'h:i A',
            'forceRoundTime': true,
            'minTime': '12:00 AM',
            'maxTime': '11:59 PM',
            'useSelect': true,
            'className': 'styled' 
        }); 


        $('#start_end .start').change(function() {

            var currentSQLDate = $('#currentSQLDate').val();
            var jQueryDate = currentSQLDate + ' ' + $(this).val();

            $('#notes').val(jQueryDate+ ' '+ ' '+ meeting_duration_minutes+' '+numMinutes);

            $('#start_end .end').val(addMinutes(jQueryDate, numMinutes));

        });
    }

    function addMinutes(jQueryDate, numMinutes) {
        
        var start = new Date(jQueryDate);
        var m; //AM or PM
        var end = new Date(start.getTime() + numMinutes * 60000);
        var endHour = end.getHours();
        var endMinute = end.getMinutes();

        console.log('addMinutes '+jQueryDate+' '+start);

        if (endHour < 12) {
            m = 'AM';
        }
        else {
            m = 'PM';
        }

        if (endHour > 12)
            endHour -= 12;

        if (endMinute < 10) {
            endMinute = '0' + endMinute;
        }

        if (endHour < 10) {
            endHour = '0' + endHour;
        }

        return endHour + ':' + endMinute + ' ' + m;
    }

    function ccShowOptions() {
        var table = $('#meetingCCOptions').find('table');

        var tableCCRow = '<tr class="whiteBoxTop" id="cc1" onclick="ccHighlightOptionRow(\'cc1\');">\n\
<td>Intro</td><td id="cc1_date">--</td><td id="cc1_time">Schedule</td></tr>\
<tr><td></td></tr>\
<tr class="whiteBoxTop" id="cc2" onclick="ccHighlightOptionRow(\'cc2\');"><td>Mtg 1</td><td id="cc2_date">--</td><td id="cc2_time">Schedule</td></tr>\
<tr><td></td></tr>\
<tr class="whiteBoxTop" id="cc3" onclick="ccHighlightOptionRow(\'cc3\');"><td>Mtg 2</td><td id="cc3_date">--</td><td id="cc3_time">Schedule</td></tr>\
<tr><td></td></tr>\
<tr class="whiteBoxTop" id="cc4" onclick="ccHighlightOptionRow(\'cc4\');"><td>Mtg 3</td><td id="cc4_date">--</td><td id="cc4_time">Schedule</td></tr>\
<tr><td></td></tr>\
<tr class="whiteBoxTop" id="cc5" onclick="ccHighlightOptionRow(\'cc5\');"><td>Mtg 4</td><td id="cc5_date">--</td><td id="cc5_time">Schedule</td></tr>';
        table.append(tableCCRow);
        ccHighlightOptionRow('cc1');
        $('#currentOption').val('cc1'); //the default option 
    }


    function ccHighlightOptionRow(row) {
        $('#currentOption').val(row);
        $('#cc1').removeAttr('style');
        $('#cc2').removeAttr('style');
        $('#cc3').removeAttr('style');
        $('#cc4').removeAttr('style');
        $('#cc5').removeAttr('style');
        $('#cc6').removeAttr('style');
        $('#' + row).attr("class", "whiteBoxActive lightBlue");
        
        setTimeout(function() {
            showFullCalendar('refetch'); //reload calendar
        }, 100);
    }

    function ccAcceptTime(optionX) {
        var time_picker_start = $('#time_picker_start').val(); //selected time 
        var currentDate = $('#currentDate').val(); //current date
        var currentSQLDate = $('#currentSQLDate').val(); //date in SQL format

        var ass = optionX.substring(3, 4);
        var thisNumber = optionX.substring(14, 15);

        $('#' + optionX).val(currentSQLDate + ' ' + time_picker_start);
        $('#cc' + ass + '_option' + thisNumber + '_date').text(currentDate);
        $('#cc' + ass + '_option' + thisNumber + '_time').text(time_picker_start);

        //go to next option - optionX
        var nextNumber = parseInt(thisNumber) + 1;
        var optionNext = 'ass' + ass + '_proposal_' + nextNumber;

        $('#cc' + ass + '_option'+nextNumber).css("border", "1px solid black");

        $('#cc' + ass).attr("class", "whiteBoxComplete darkGreen");

        if (nextNumber <= 3) {
            $('#currentOption').val(optionNext);
        }
        else {
            var optionNext = 'ass' + ass + '_proposal_3';
        }
    }

    //pass time_picker_start into left hand side 
    function ccTransferTime(optionX) {
        var time_picker_start = $('#time_picker_start').val(); //selected time 
        var currentDate = $('#currentDate').val(); //current date
        var currentSQLDate = $('#currentSQLDate').val(); //date in SQL format

        $('#' + optionX + '_time').html(time_picker_start); //LHS time column
        $('#' + optionX + '_date').html(currentDate); //LHS date column 
        $('#' + optionX + '_proposed').val(currentSQLDate + ' ' + time_picker_start);
        $('#' + optionX).attr("class", "whiteBoxComplete darkGreen");

        if (optionX == 'cc1') {
            ccCalculateDates(7);
        } 
        
        //go to next option - optionX
        //put this into proposals fields 
        var thisNumber = optionX.substring(2, 3);
        $('#cc' + thisNumber).val(currentSQLDate + ' ' + time_picker_start);  //intro, cc1, cc2, cc3

        //go to next option - optionX
        var nextNumber = parseInt(thisNumber) + 1;

        if (nextNumber <= 6) { //no more than 6 options
            var optionNext = 'cc' + nextNumber;
            $('#currentOption').val(optionNext);
            ccHighlightOptionRow(optionNext);
        }
        else {
            var optionNext = 'cc6';
        }

    }

    function ccCalculateDates(days) {
        var currentSQLDate = $('#currentSQLDate').val();
        var currentTime = $('#time_picker_start').val();
        
        var firstDate = new Date( currentSQLDate + ' 00:00:00' );
        
        firstDate.setDate(firstDate.getDate() + 7); 
        var suggestMonth = parseInt(firstDate.getMonth() + 1);
        var suggestDay = firstDate.getDate();
        if(suggestMonth < 10) suggestMonth = '0'+suggestMonth;
        if(suggestDay < 10) suggestDay = '0'+suggestDay;
        var suggestDate = suggestMonth+'/'+suggestDay;
        var suggestSQL = firstDate.getFullYear()+'-'+suggestMonth+'-'+suggestDay+' '+currentTime;
        
        $('#cc2_date').html( suggestDate );
        $('#cc2_time').html(currentTime);
        $('#cc2_proposed').val(suggestSQL);
        
        firstDate.setDate(firstDate.getDate() + 7);
        var suggestMonth = parseInt(firstDate.getMonth() + 1);
        var suggestDay = firstDate.getDate();
        if(suggestMonth < 10) suggestMonth = '0'+suggestMonth;
        if(suggestDay < 10) suggestDay = '0'+suggestDay;
        var suggestDate = suggestMonth+'/'+suggestDay;
        var suggestSQL = firstDate.getFullYear()+'-'+suggestMonth+'-'+suggestDay+' '+currentTime;
        
        $('#cc3_date').html( suggestDate );
        $('#cc3_time').html(currentTime);
        $('#cc3_proposed').val(suggestSQL);
        
        firstDate.setDate(firstDate.getDate() + 7);
        var suggestMonth = parseInt(firstDate.getMonth() + 1);
        var suggestDay = firstDate.getDate();
        if(suggestMonth < 10) suggestMonth = '0'+suggestMonth;
        if(suggestDay < 10) suggestDay = '0'+suggestDay;
        var suggestDate = suggestMonth+'/'+suggestDay;
        var suggestSQL = firstDate.getFullYear()+'-'+suggestMonth+'-'+suggestDay+' '+currentTime;
        
        $('#cc4_date').html( suggestDate );
        $('#cc4_time').html(currentTime);
        $('#cc4_proposed').val(suggestSQL);
         
        firstDate.setDate(firstDate.getDate() + 14); //2 week interval for IE meeting week
        var suggestMonth = parseInt(firstDate.getMonth() + 1);
        var suggestDay = firstDate.getDate();
        if(suggestMonth < 10) suggestMonth = '0'+suggestMonth;
        if(suggestDay < 10) suggestDay = '0'+suggestDay;
        var suggestDate = suggestMonth+'/'+suggestDay;
        var suggestSQL = firstDate.getFullYear()+'-'+suggestMonth+'-'+suggestDay+' '+currentTime;
        
        $('#cc5_date').html( suggestDate );
        $('#cc5_time').html(currentTime);
        $('#cc5_proposed').val(suggestSQL);
         
        firstDate.setDate(firstDate.getDate() + 7);
        var suggestMonth = parseInt(firstDate.getMonth() + 1);
        var suggestDay = firstDate.getDate();
        if(suggestMonth < 10) suggestMonth = '0'+suggestMonth;
        if(suggestDay < 10) suggestDay = '0'+suggestDay;
        var suggestDate = suggestMonth+'/'+suggestDay;
        var suggestSQL = firstDate.getFullYear()+'-'+suggestMonth+'-'+suggestDay+' '+currentTime;
        
        $('#cc6_date').html( suggestDate );
        $('#cc6_time').html(currentTime);
        $('#cc6_proposed').val(suggestSQL);
    }
</script>
<? include('footer.php'); ?>