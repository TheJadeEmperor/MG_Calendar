<script>
    $(document).ready(function() {

        generateUserList('<?=$mentor_type?>');

        setTimeout(function() {
            chooseMentor('<?=$user_id_mentor?>');

            changeCourse('<?=$course_id?>');
        }, 150);   
        //get the courses for the courses drop down menu
        $.ajax({  
            type        : 'POST', //Method type
            url         : '<?=$course_read?>', 
            success     : function(data) {
                // alert(data);
                var courseObject = $.parseJSON(data);
                //console.log('c: '+c);
                $.each(courseObject, function(index, courseArray) {
                    $.each(courseArray, function(key, c){
                        $('.course_id').append(new Option( '#'+c.course_id+' - '+c.course_name+' - '+c.course_type, c.course_id));
                    });
                });
            },
        });
    });

    function generateUserList(user_type_from_url) {
        if(user_type_from_url == '') {
            var user_type = $('select[id=mentor_type]').val(); //mentor type
        }
        else {
            var user_type = user_type_from_url;
            $("#user_type").val(user_type);
        }
//        console.log('user_type_from_url: '+user_type_from_url+' user_type: '+user_type);

        var user_id_choose = $('#user_id_choose');
        var appointment_type = '';
        

        var data = 'user_type='+user_type; 
        $.ajax({
            url: 'http://modernguild.com/sites/all/modules/guild/mg/mentor_read.php',
            data: data,
            type: 'POST',
            success: function(json) {
                //alert(json);
                $('#user_id_choose option[value!="0"]').remove();
                $.each($.parseJSON(json), function(n, value) {  //append to drop down menu
                    var user_id = value.user_id;
                    user_id_choose.append($('<option></option>').val(user_id).html(user_id + ' - ' + value.full_name));
                });
            }
        })
    }

    function chooseMentor(user_id_from_url) {         
        var user_id_mentor;
        if(user_id_from_url == '')  //select user_id_mentor from drop down
            user_id_mentor = $('select[id=user_id_choose]').val();
        else { //user_id_mentor passed in from URL
            user_id_mentor = user_id_from_url;
            $('#user_id_choose').val(user_id_mentor);
        }
        
        console.log('user_id_from_url: '+user_id_from_url+' user_id_mentor: '+user_id_mentor);

        $('#user_id_mentor').val(user_id_mentor);

        showFullCalendar(user_id_mentor);        
    }
    
    
    function changeCourse(course_id_from_url) {
        var mentor_type = $('#user_type').val();
        var user_id_mentor = $('#user_id_mentor').val();
        var course_id = $('#course_id').val();

        if(course_id_from_url == '') {
            course_id = $('#course_id').val();
            //window.location.href = 'form_mentor_schedule.php?mentor_type='+mentor_type+'&user_id_mentor='+user_id_mentor+'&course_id='+course_id;
        }
        else {
            course_id = course_id_from_url;
            $('#course_id').val(course_id);
        }
    }
               
    function createAppointment(time_start, time_end) {
        var user_id_protege = $('#user_id_protege').val();
        var user_id_mentor = $('#user_id_mentor').val();
        var appointment_name = $('#appointment_name').val();
        var appointment_type = $('#appointment_type').val();
        var status = $('#status option:selected').val();
        var ass_id = $('#assignment_id').val();
        var data = 'appointment_name=' + appointment_name + '&appointment_type=' + appointment_type + '&time_start=' + time_start + '&time_end=' + time_end + '&user_id_protege=' + user_id_protege + '&user_id_mentor=' + user_id_mentor + '&status='+status+'&assignment_id='+ass_id;
        console.log('createAppointment: ' + data);
/*
        $.ajax({
            url: 'http://modernguild.com/sites/all/modules/guild/mg/meeting_create_cal.php',
            data: data,
            type: 'POST',
            success: function(json) {   
    //            $('#calendar').fullCalendar('refetchEvents');
            }
        })*/
    }
    
    
        function createFormDialog() {
             $('#createForm').dialog({
                modal: true,
                position: 'top',
                show: {
                    effect: "explode",
                    duration: 500
                },
                hide: {
                    effect: "explode",
                    duration: 500
                },
                buttons: {
                    Save: function () {
                        //createForm();
                        $( this ).dialog( "close" );
                    },
                    Cancel: function() {
                        $( this ).dialog( "close" );
                    },                        
                }
            });
        }
        
        
        function createForm() {
            var form_name = $('#form_name').val();
            var form_type = $('#form_type').val();
            var user_type = $('#user_type').val();
            var user_id_mentor = $('#user_id_mentor').val();
            var course_id = $('#course_id').val();
            var form_url = 'form_mentor_schedule.php?mentor_type='+user_type+'&user_id_mentor='+user_id_mentor+'&course_id='+course_id;
            form_url = escape(form_url);
            var data = 'form_name='+form_name+'&form_type='+form_type+'&form_url='+form_url+'&user_type='+user_type+'&status=5';
            
            $.ajax({
                url: 'http://modernguild.com/sites/all/modules/guild/mg/form_create.php',
                data: data,
                type: 'POST',
                success: function(msg) {   
                    console.log('before: '+data+' after: '+msg);
                    //alert('before: '+data+' after: '+msg);
                }
            })
        }
</script>

<? //if($is_admin) 
    if(false) { 
    ?>
    <form>

    <div class="panel panel-primary" id="adminOptions" style="width: 1150px">
        <div class="panel-heading">
            <h3 class="panel-title">Admin Options</h3>
        </div>
        <div class="panel-body">
            <div class="alert alert-info">
                <table>
                    <tr>
                        <td>Mentor Type</td>
                        <td>
                            <select id="mentor_type" onchange="generateUserList('')">
                                <option value="2">2 - Career Coach</option>
                                <option value="3">3 - Industry Expert</option>
                                <option value="4">4 - LMI</option>
                            </select>
                        </td>
                        <td width="20px"></td>
                        <td>Mentor</td> 
                        <td>
                            <select id="user_id_choose" onchange="chooseMentor('');">
                                <option value="0">Choose one...</option>
                            </select>
                        </td>
                        <td width="20px"></td>
                        <td>Courses</td>
                        <td>
                            <select id="course_id" class="course_id" onchange="changeCourse('');">
                                <option value="0">All Courses</option> 
                            </select>
                        </td>
                        <td width="20px"></td>
                        <td>
                            <button class="btn btn-warning" onclick="createFormDialog();">Save Form</button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
            
    </form>
    <? }?>


      <!-- Admin Update Appointment -->
<!--            <form id="updateApptForm" title="Update Appointment" style="display: none;">
                <label>Time Start</label>
                <input type="text" name="time_picker_start" id="time_picker_start" size="7" /><br />
                <br /><br />
                
                <label>Appointment Name</label><br />
                <input type="text" id="appointment_name" value="<?= $appt_name ?>" />
                <br /><br />

                <label>Appointment Type</label><br />
                <input type="text" id="appointment_type" value="<?= $appt_type ?>" />
                <br /><br />

                <label>Status</label> <select id="status">
                   <option>Proposed</option>
                    <option>Scheduled</option>
                </select>
            </form>-->


<!-- Admin Create Appointment -->
            <form id="addApptForm" title="Create Appointment">
           <!--     <label>Time Start</label>
                <input type="text" name="time_picker_start" id="time_picker_start" size="7" /><br />
                <br /><br />
                Time End<br /> <input type="text" id="time_end" /><br /><br />
                    -->

             <!--   <label>Appointment Name</label><br />
                <input type="text" id="appointment_name" value="<?= $appt_name ?>" />
                <br /><br />

                <label>Appointment Type</label><br />
                <input type="text" id="appointment_type" value="<?= $appt_type ?>" />
                <br /><br />

                <label>Status</label> <select id="status">
                    <option>Proposed</option>
                    <option>Scheduled</option>
                </select>
                <br />
                <input type="hidden" id="user_id_protege" value="<?= $user_id_protege ?>" />
                <input type="hidden" id="user_id_mentor2" value="<?= $user_id_mentor ?>" /> 
                
                <input type="hidden" id="assignment_id" />-->
            </form>

    <form id="upMeetFormCal" title="Reschedule Meeting" style="display: none; font-size: 9; width:500px !important;">
    <label>Meeting ID</label>&nbsp;<input type="button" name="appointment_id_display" />
    

    <label>Meeting Name</label><br />
    <input type="text" name="meeting_name_cal_u" id="meeting_name_cal_u" />
    <br /><br />
    <div id="date_1_contain">
        <div id="date_1" style="float: left;width: 70%;">
            <label for="time">Alternate Date & Time 1</label><br />
            <input name="date_u" id="time_start_cal_u" class="required" size="10" /> 
            <input name="time_u" id="time_picker_cal_u" size="8" /><br />
        </div>

        <div id="pick_1" style=margin-left:20%;">

            <label for="time">Accept</label><br />
            <input type="radio" name="accept_1" value="male">

        </div>
    </div>
    <br /><br />

    <div id="date_2_contain">
        <div id="date_2" style="float: left;width: 70%;">    
            <label for="time">Alternate Date & Time 2</label><br />
            <input name="date_u2" id="time_start_cal_u2" class="required" size="10" /> 
            <input name="time_u2" id="time_picker_cal_u2" size="8" /><br />
        </div>
        <div id="pick_2" style=margin-left:20%;">
            <label for="time2">Accept</label><br />
            <input type="radio" name="accept_2" value="male">
        </div>
    </div>
    <br /><br />
    <div id="date_3_contain">
        <div id="date_3" style="float: left;width: 70%;">    
            <label for="time">Alternate Date & Time 3</label><br />
            <input name="date_u3" id="time_start_cal_u3" class="required" size="10" /> 
            <input name="time_u3" id="time_picker_cal_u3" size="8" /><br />
        </div>
        <div id="pick_2" style=margin-left:20%;">

            <label for="time3">Accept</label><br />
            <input type="radio" name="accept_3" value="male">
        </div>

    </div>
    <br /><br />

    <label>Include a note to your mentor</label><br />

    <textarea name="meeting_note_to_mentor_cal_u" id="meeting_note_to_mentor_cal_u" rows="4" cols="25">
    </textarea>
    <br /><br />
</form>