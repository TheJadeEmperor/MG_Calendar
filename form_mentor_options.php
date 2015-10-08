<div id="formLHS">
    
    <div class="whiteBox" style="margin-right: 20px;">
        <div class="whiteBoxInner" id="schedule_user">
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
                
    <div id="step1">
        <p><span class="ordinal large">1)</span> <span class="large" id="instructions_step1">Click on meeting blocks below, then click on Calendar, to schedule with your Career Coach.</p> 
    </div>
    
    <br />
    

    
    <div id="meetingList" class="whiteBox">
        <br /><br />
        
        <table>
            <tr>
                <td width="100px">Meeting</td><td width="80px">Date</td><td width="90px">Time</td>
                <td class="acceptColumn">Accept</td>
            </tr>
            <tr>
                <td>Option 1</td>
                <td><span id="opt1_date_display">-</span></td>
                <td><span id="opt1_time_display">-</span></td>
                <td class="acceptColumn">
                    <input type="radio" name="accept" id="accept" value="option1_accept" />
                </td>
            </tr>
            <tr>
                <td>Option 2</td>
                <td><span id="opt2_date_display">-</span></td>
                <td><span id="opt2_time_display">-</span></td>
                <td class="acceptColumn">
                    <input type="radio" name="accept" id="accept" value="option2_accept" />
                </td>
            </tr>
            <tr>
                <td>Option 3</td>
                <td><span id="opt3_date_display">-</span></td>
                <td><span id="opt3_time_display">-</span></td>
                <td class="acceptColumn">
                    <input type="radio" name="accept" id="accept" value="option3_accept" />
                </td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td class="acceptColumn">
                    <p>Reschedule</p> </td><td class="acceptColumn"><p><input type="radio" name="accept" id="accept" value="reject" /></p></td>
            </tr>
        </table>
        <br /><br />
    </div>
    
    <div id="meetingOptions" class="whiteBox">
        <p>Below are your proposed times</p>
        <table cellpadding="5">
            <tr>
                <td width="100px">Meeting</td><td width="80px">Date</td><td width="90px">Time</td>
            </tr>
            <tr id="option1" onclick="highlightOptionRow('option1');">
                <td>Option 1</td><td><span id="option1_date">-</span></td>
                <td><span id="option1_time">-</span></td>
            </tr>
            <tr id="option2" onclick="highlightOptionRow('option2');">
                <td>Option 2</td><td><span id="option2_date">-</span></td>
                <td><span id="option2_time">-</span></td>
            </tr>
            <tr id="option3" onclick="highlightOptionRow('option3');">
                <td>Option 3</td><td><span id="option3_date">-</span></td>
                <td><span id="option3_time">-</span></td>
            </tr>
        </table> 
    </div>
    <br /><br />

    
        <div id="ccInitialProposal">
        <table cellpadding="15" width="100%">
            <tr class="medium">
                <td width="100px">Meeting</td><td width="80px">Date</td><td width="90px">Time</td>
            </tr>
            
            <?
            if($course_id == 16) //JSF immersion
                $ccAssNames = array(
                    '1' => 'MTG #1',
                    '2' => 'MTG #2'
                );
            else
                $ccAssNames = array(
                '1' => 'INTRO',
                '2' => 'MTG #1',
                '3' => 'MTG #2',
                '4' => 'MTG #3',
                '5' => 'MTG #4',
                '6' => 'MTG #5' 
                );

            for ($m = 1; $m <= $cc_max_meetings; $m++) { 
                echo '<tr class="whiteBoxTop" id="cc'.$m.'" onclick="ccHighlightOptionRow(\'cc'.$m.'\');">
<td>'.$ccAssNames[$m].'</td><td id="cc'.$m.'_date">--</td><td id="cc'.$m.'_time">Schedule</td></tr>
<tr><td></td></tr>';
            }
        ?>
        </table>
    </div>

<?

function cc_reject_form($n) {
    return '<div id="cc' . $n . '_options">
        <table cellpadding="2">
        <tr id="cc'.$n.'_option1">
            <td width="150px" align="center">OPTION 1</td>
            <td><span id="cc' . $n . '_option1_date"></span></td>
            <td><span id="cc' . $n . '_option1_time">Schedule</span></td>
        </tr>
        <tr id="cc'.$n.'_option2">
            <td width="150px" align="center">OPTION 2</td>
            <td><span id="cc' . $n . '_option2_date"></span></td>
            <td><span id="cc' . $n . '_option2_time">Schedule</span></td>
        </tr>
        <tr id="cc'.$n.'_option3">
            <td width="150px" align="center">OPTION 3</td>
            <td><span id="cc' . $n . '_option3_date"></span></td>
            <td><span id="cc' . $n . '_option3_time">Schedule</span></td>
        </tr>
        </table>
        </div><br />';
}

?>

    <div id="ccAcceptReject">
        <table cellpadding="5" cellspacing="4">
            <tr class="whiteBoxTop">
                <td colspan="5">
                    <p><span class="lightBlue medium bold">PROPOSED DATES AND HOURS</span></p>
                </td>
            </tr>
            <tr class="whiteBoxTop medium">
                <td>Meeting</td><td>Date</td><td>Time</td><td colspan="2">Accept/Reject</td>
            </tr>
            <tr>
                <td><br /></td>
            </tr>
    <?
    $ccAssNames = array(
        '1' => 'INTRO',
        '2' => 'MTG 1',
        '3' => 'MTG 2',
        '4' => 'MTG 3',
        '5' => 'MTG 4',
        '6' => 'MTG 5' 
    );
    
    for ($m = 1; $m <= 6; $m++) { 
        echo '<tr class="whiteBoxTop lightGray small cc'.$m.'">
            <td><span class="small">' . $ccAssNames[$m] . '</span></td>
                <td><span class="small" id="cc' . $m . '_date">TBD</span></td>
                <td><span class="small" id="cc' . $m . '_time">TBD</span></td>
                <td><input type="radio" id="cc' . $m . '_acc" name="cc' . $m . '_acc" value="accept" /></td>
                <td><input type="radio" id="cc' . $m . '_acc" name="cc' . $m . '_acc" value="reject" /></td>
        </tr><tr class="whiteBoxBottom lightGray small cc'.$m.'">
            <td colspan="10">
                 ' . cc_reject_form($m) . ' 
            </td>
        </tr><tr class="cc'.$m.'">
            <td><br /></td>
        </tr>';
    }
    ?>
        </table>
    </div>
    
    <center>
    <div id="deleteMeetingButton">
        <br />
        <span id="deleteMeetingInstructions"></span>
        <input type="button" class="btn btn-danger" value="Delete Meeting" onclick="deleteMeeting();" />
    </div>
    </center>
    
    <p>&nbsp;</p>
    
    <div id="step2">
        <p><span class="ordinal large">2)</span> <span class="large" id="instructions_step2">Submit these meetings to your coach

</p>
    </div>

    <br /><br />
    
    <table width="100%">
        <tr>
            <td>
                <div id="most_recent_message"></div>
            </td>
        </tr>
    </table>
    
    <br /><br />
    
    <table width="100%" id="actionBox">
        <tr>
            <td colspan="2">
                <textarea id="message_body" rows="4" cols="50" placeholder="Feel free to send a comment"></textarea><br />
            </td>
        </tr>
        <tr valign="top" width="50%">
            <td align="left"><a href="index.php"><input class="grayButton" type="button" value="Cancel" /></a></td>
            <td align="right"><input type="button" class="grayButton" value="Submit" onclick="saveEverything();" /> </td>
        </tr>
    </table>
    <br />
</div>