<?php
/**
 * The template for the page covid-vaccine with a url of
 * https://camaligan.gov.ph/surveys/covid-vaccine
 */
 get_header();
 include_once('inc/banner.php');
 ?><script>
    $(document).ready( function() {
        var empty_count_input = 8;
        var empty_count_select = 24;
        var input_modified = false;
        var select_modified = false;
        var submit_btn_was_hovered = false;
        var empty_field_count = empty_count_input + empty_count_select;
        
        $(window).bind("load", function() {
            $( '.if_covid_positive' ).attr( 'disabled', 'disabled' );
        });
        
        $( '#gender' ).on( 'change', function() {
            var gndr = $( '#gender' ).prop( 'selectedIndex' );
            var is_pregnant = $( '#is_pregnant' )
            if ( gndr == 1 ) {
                is_pregnant.prop( 'selectedIndex', 2 );
                is_pregnant.attr( 'disabled', 'disabled' );
            }
            else {
                is_pregnant.prop( 'selectedIndex', 0 );
                is_pregnant.removeAttr( 'disabled' );
            }
        });
        
        $('#response_submit').hover( function() {
            if ( input_modified && select_modified ) {
                if ( empty_field_count > 0 ) {
                    alert( 'You still need to fill up ' + empty_field_count + ' Blank Fields' );
                }
            }
            
            else {
                if ( empty_field_count > 0 ) {
                    alert( 'Fill up the all the REQUIRED DETAILS first before you hover your mouse in the SUBMIT Button' );
                }
                input_modified = true;
                select_modified = true;
            }
            
            if( !submit_btn_was_hovered ) {
                $('div.c19-container > form * input').each( function() {
                    if( $(this).attr( 'name' ) == 'date_positive' ) {
                        if( !$(this).is( ':disabled' ) ) {
                            if ( $(this).val() == '' ) {
                                $( this ).css( 'border', '1px solid red' );
                            }
                            else {
                                $( this ).css( 'border', '1px solid #cacaca' );
                            }
                        }
                    }
                    else {
                        if( $(this).attr( 'name' ) != 'name_suffix' && $(this).attr( 'name' ) != 'response_submit' ) {
                            if ( $(this).val() == '' ) {
                                $( this ).css( 'border', '1px solid red' );
                            }
                            else {
                                $( this ).css( 'border', '1px solid #cacaca' );
                            }
                        }
                    }
                });
                
                $('div.c19-container > form * select').each( function() {
                    if( $(this).attr( 'name' ) == 'covid_classification' ) {
                        if( !$(this).is( ':disabled' ) ) {
                            if ( $(this).prop( 'selectedIndex' ) == 00 ) {
                                $( this ).css( 'border', '1px solid red' );
                            }
                            else {
                                $( this ).css( 'border', '1px solid #cacaca' );
                            }
                        }
                    }
                    else {
                        if( $(this).prop( 'selectedIndex' ) == 0 ) {
                            if ( $(this).prop( 'selectedIndex' ) == 00 ) {
                                $( this ).css( 'border', '1px solid red' );
                            }
                            else {
                                $( this ).css( 'border', '1px solid #cacaca' );
                            }
                        }
                    }
                });
            }
            if (empty_field_count != 0) {
                $( '#response_submit' ).attr( 'disabled', 'disabled' );
            }
            submit_btn_was_hovered = true;
        });
        
        $( '#barangay' ).on( 'change', function() {
            var zoneLists = [ ["--SELECT--"], ["--SELECT--", "Zone 1", "Zone 2", "Zone 3", "Zone 4", "Zone 5", "Zone 6", "Zone 7"],
                ["--SELECT--", "Zone 1", "Zone 2", "Zone 3", "Zone 4", "Zone 5", "Zone 6", "Zone 7"], ["--SELECT--", "Zone 1", "Zone 2", "Zone 3"],
                ["--SELECT--", "Zone 1", "Zone 2"], ["--SELECT--", "Zone 1", "Zone 2", "Zone 3", "Zone 4", "Zone 5", "Zone 6"],
                ["--SELECT--", "Zone 1", "Zone 2"], ["--SELECT--", "Zone 1", "Zone 2", "Zone 3"], ["--SELECT--", "Zone 1", "Zone 2", "Zone 3", "Zone 4"],
                ["--SELECT--", "Zone 1", "Zone 2", "Zone 3", "Zone 4", "Zone 5", "Zone 6", "Zone 7", "Zone 8"],
                ["--SELECT--", "Zone 1", "Zone 2", "Zone 3", "Zone 4"], ["--SELECT--", "Zone 1", "Zone 2", "Zone 3"], ["--SELECT--", "Zone 1", "Zone 2", "Zone 3", "Zone 4", "Zone 5", "Zone 6", "Zone 7A", "Zone 7B"],
                ["--SELECT--","Zone 1", "Zone 2", "Zone 3", "Zone 4"] ];
            var select_index = $(this).prop( 'selectedIndex' );
            $( '#zone' ).children().remove();
            for(var i = 0; i < zoneLists[select_index].length; i++) {
                var zone_name = zoneLists[select_index][i];
                $( '#zone' ).append('<option value="' + zone_name.toUpperCase() + '">' + zone_name + '</option>' );
            }
        });
        
        $( '#covid_positive' ).on( 'change', function() {
            if ( $(this).prop( 'selectedIndex' ) == 1 ){
                $( '.if_covid_positive' ).removeAttr( 'disabled' );
            }
            else {
                $( '.if_covid_positive' ).attr( 'disabled', 'disabled' );
                $( '#covid_classification' ).prop( 'selectedIndex', 0 );
                $( '#date_positive' ).val( '' );
            }
        });
        
        $('div.c19-container > form * input').keyup( function() {
            empty_count_input = 0;
            $('div.c19-container > form * input').each( function() {
                if( $(this).attr( 'name' ) == 'date_positive' ) {
                    if( !$(this).is( ':disabled' ) ) {
                        if ( $(this).val() == '' ) {
                            have_empty_fields = true;
                            empty_count_input++;
                            if ( submit_btn_was_hovered ) { $( this ).css( 'border', '1px solid red' ); }
                        }
                        else {
                            if ( submit_btn_was_hovered ) { $( this ).css( 'border', '1px solid #cacaca' ); }
                        }
                    
                    }
                    else {
                        $( this ).css( 'border', '1px solid #cacaca' );
                    }
                }
                else {
                    if( $(this).attr( 'name' ) != 'name_suffix' && $(this).attr( 'name' ) != 'response_submit' ) {
                        if( $(this).val() == '' ) {
                            have_empty_fields = true;
                            empty_count_input++;
                            if ( submit_btn_was_hovered ) { $( this ).css( 'border', '1px solid red' ); }
                        }
                        else {
                            if ( submit_btn_was_hovered ) { $( this ).css( 'border', '1px solid #cacaca' ); }
                        }
                    }
                }
            });
            
            empty_field_count = empty_count_input + empty_count_select;
            
            if (empty_field_count == 0) {
                $( '#response_submit' ).removeAttr( 'disabled' );
            }
        });
        
        $('div.c19-container > form * select').on( 'change', function() {
            empty_count_select = 0;
            select_modified = true;
            $('div.c19-container > form * select').each( function() {
                if( $(this).attr( 'name' ) == 'covid_classification' ) {
                    if( !$(this).is( ':disabled' ) ) {
                        if ( $(this).prop( 'selectedIndex' ) == 00 ) {
                            have_empty_fields = true;
                            empty_count_select++;
                            if ( submit_btn_was_hovered ) { $( this ).css( 'border', '1px solid red' ); }
                        }
                        else {
                            if ( submit_btn_was_hovered ) { $( this ).css( 'border', '1px solid #cacaca' ); }
                        }
                    }
                    else {
                        $( this ).css( 'border', '1px solid #cacaca' );
                    }
                }
                else {
                    if( $(this).prop( 'selectedIndex' ) == 0 ) {
                        have_empty_fields = true;
                        empty_count_select++;
                        if ( submit_btn_was_hovered ) { $( this ).css( 'border', '1px solid red' ); }
                    }
                    else {
                        if ( submit_btn_was_hovered ) { $( this ).css( 'border', '1px solid #cacaca' ); }
                    }
                }
            });
            
            empty_field_count = empty_count_input + empty_count_select;
            
            if (empty_field_count == 0) {
                $( '#response_submit' ).removeAttr( 'disabled' );
            }
        });
    });
</script>
    <div class="c19-container">
        <h1 id="c19-title">COVID VACCINE REGISTRATION FORM</h1>
        <form method='post' action='' name='covidForm' enctype='multipart/form-data'>
            <div class="tables-row1">
                <div class="table-row">
                    <table id="table1a">
                        <tr>
                            <td><h5 class="key_field">Last Name</h5></td>
                            <td><input id="name_last" class="value_field" type="text" name="name_last"></td>
                        </tr>
                        <tr>
                            <td><h5 class="key_field">First Name</h5></td>
                            <td><input id="name_first" class="value_field" type="text" name="name_first"></td>
                        </tr>
                        <tr>
                            <td><h5 class="key_field">Middle Name</h5></td>
                            <td><input id="name_middle" class="value_field" type="text" name="name_middle"></td>
                        </tr>
                        <tr>
                            <td><h5 class="key_field">Name Suffix (Jr, Sr, etc.)</h5></td>
                            <td><input id="name_suffix" class="value_field" type="text" name="name_suffix"></td>
                        </tr>
                        <tr>
                            <td><h5 class="key_field">Barangay Name</h5></td>
                            <td>
                                <select id="barangay" class="value_field" name="barangay">
                                    <option value="">--SELECT--</option>
                                    <option value="DUGCAL">Dugcal</option>
                                    <option value="MARUPIT">Marupit</option>
                                    <option value="SAN FRANCISCO">San Francisco</option>
                                    <option value="SAN JOSE - SAN PABLO">San Jose - San Pablo</option>
                                    <option value="SAN JUAN - SAN RAMON">San Juan - San Ramon</option>
                                    <option value="SAN LUCAS">San Lucas</option>
                                    <option value="SAN MARCOS">San Marcos</option>
                                    <option value="SAN MATEO">San Mateo</option>
                                    <option value="SAN ROQUE">San Roque</option>
                                    <option value="STO. DOMINGO">Sto. Domingo</option>
                                    <option value="STO. TOMAS">Sto. Tomas</option>
                                    <option value="SUA">Sua</option>
                                    <option value="TAROSANAN">Tarosanan</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><h5 class="key_field">Barangay Zone</h5></td>
                            <td><select id="zone" class="value_field" name="zone">
                                <option value="">--SELECT--</option>
                            </select></td>
                        </tr>
                    </table>
                    <table id="table1b">
                        <tr>
                            <td><h5 class="key_field">Gender</h5></td>
                            <td>
                                <select id="gender" class="value_field" name="gender">
                                    <option value="">--SELECT--</option>
                                    <option value="MALE">Male</option>
                                    <option value="FEMALE">Female</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><h5 class="key_field">Birth Date</h5></td>
                            <td><input id="birth_date" class="value_field" type="date" name="birth_date"></td>
                        </tr>
                        <tr>
                            <td><h5 class="key_field">Civil Status</h5></td>
                            <td>
                                <select id="civil_status" class="value_field" name="civil_status">
                                    <option value="">--SELECT--</option>
                                    <option value="SINGLE">Single</option>
                                    <option value="MARRIED">Married</option>
                                    <option value="DIVORCED">Divorced</option>
                                    <option value="SEPARATED">Separated</option>
                                    <option value="WIDOWED">Widowed</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><h5 class="key_field">Contact Number</h5></td>
                            <td><input id="contact_number" class="value_field" type="text" maxLength="11" name="contact_number"></td>
                        </tr>
                        <tr>
                            <td><h5 class="key_field">Are you Pregnant? (Answer No if Male)</h5></td>
                            <td>
                                <select id="is_pregnant" class="value_field" name="is_pregnant">
                                    <option value="">--SELECT--</option>
                                    <option value="YES">YES</option>
                                    <option value="NO">NO</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><h5 class="key_field">Occupation</h5></td>
                            <td><input id="occupation" class="value_field" type="text" name="occupation"></td>
                        </tr>
                    </table>
                </div>
                <div class="table-row">
                    <table id="table2a">
                        <tr>
                            <td><h5 class="key_field">Directly Interacting with COVID Patients?</h5></td>
                            <td>
                                <select id="direct_interact" class="value_field" name="direct_interact">
                                    <option value="">--SELECT--</option>
                                    <option value="YES">YES</option>
                                    <option value="NO">NO</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><h5 class="key_field">Have Drug Allergy?</h5></td>
                            <td>
                                <select id="have_drug_allergy" class="value_field" name="have_drug_allergy">
                                    <option value="">--SELECT--</option>
                                    <option value="YES">YES</option>
                                    <option value="NO">NO</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><h5 class="key_field">Have Food Allergy?</h5></td>
                            <td>
                                <select id="have_food_allergy" class="value_field" name="have_food_allergy">
                                    <option value="">--SELECT--</option>
                                    <option value="YES">YES</option>
                                    <option value="NO">NO</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><h5 class="key_field">Have Insect Allergy?</h5></td>
                            <td>
                                <select id="have_insect_allergy" class="value_field" name="have_insect_allergy">
                                    <option value="">--SELECT--</option>
                                    <option value="YES">YES</option>
                                    <option value="NO">NO</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><h5 class="key_field">Have Latex Allergy?</h5></td>
                            <td>
                                <select id="have_latex_allergy" class="value_field" name="have_latex_allergy">
                                    <option value="">--SELECT--</option>
                                    <option value="YES">YES</option>
                                    <option value="NO">NO</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><h5 class="key_field">Have Mold Allergy?</h5></td>
                            <td>
                                <select id="have_mold_allergy" class="value_field" name="have_mold_allergy">
                                    <option value="">--SELECT--</option>
                                    <option value="YES">YES</option>
                                    <option value="NO">NO</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><h5 class="key_field">Have Pet Allergy?</h5></td>
                            <td>
                                <select id="have_pet_allergy" class="value_field" name="have_pet_allergy">
                                    <option value="">--SELECT--</option>
                                    <option value="YES">YES</option>
                                    <option value="NO">NO</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><h5 class="key_field">Have Pollen Allergy?</h5></td>
                            <td>
                                <select id="have_pollen_allergy" class="value_field" name="have_pollen_allergy">
                                    <option value="">--SELECT--</option>
                                    <option value="YES">YES</option>
                                    <option value="NO">NO</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <table id="table2b">
                        <tr>
                            <td><h5 class="key_field">With Comorbidity?</h5></td>
                            <td>
                                <select id="with_comorbidity" class="value_field" name="with_comorbidity">
                                    <option value="">--SELECT--</option>
                                    <option value="YES">YES</option>
                                    <option value="NO">NO</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><h5 class="key_field">Have Hypertension?</h5></td>
                            <td>
                                <select id="have_hypertension" class="value_field" name="have_hypertension">
                                    <option value="">--SELECT--</option>
                                    <option value="YES">YES</option>
                                    <option value="NO">NO</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><h5 class="key_field">Have Heart Disease?</h5></td>
                            <td>
                                <select id="have_heart_disease" class="value_field" name="have_heart_disease">
                                    <option value="">--SELECT--</option>
                                    <option value="YES">YES</option>
                                    <option value="NO">NO</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><h5 class="key_field">Have Kidney Disease?</h5></td>
                            <td>
                                <select id="have_kidney_disease" class="value_field" name="have_kidney_disease">
                                    <option value="">--SELECT--</option>
                                    <option value="YES">YES</option>
                                    <option value="NO">NO</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><h5 class="key_field">Have Diabetes?</h5></td>
                            <td>
                                <select id="have_diabetes" class="value_field" name="have_diabetes">
                                    <option value="">--SELECT--</option>
                                    <option value="YES">YES</option>
                                    <option value="NO">NO</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><h5 class="key_field">Have Asthma?</h5></td>
                            <td>
                                <select id="have_asthma" class="value_field" name="have_asthma">
                                    <option value="">--SELECT--</option>
                                    <option value="YES">YES</option>
                                    <option value="NO">NO</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><h5 class="key_field">Have Immune Defeciency?</h5></td>
                            <td>
                                <select id="have_immune_defeciency" class="value_field" name="have_immune_defeciency">
                                    <option value="">--SELECT--</option>
                                    <option value="YES">YES</option>
                                    <option value="NO">NO</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><h5 class="key_field">Have Cancer?</h5></td>
                            <td>
                                <select id="have_cancer" class="value_field" name="have_cancer">
                                    <option value="">--SELECT--</option>
                                    <option value="YES">YES</option>
                                    <option value="NO">NO</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div>
                <table id="table3">
                    <tr>
                        <td class="key_field"><h5>Other Disease/s</h5></td>
                        <td class="value_field"><input id="other_disease" type="text" name="other_disease"></td>
                    </tr>
                </table>
            </div>
            <div>
                <table id="table4a">
                    <tr>
                        <td><h5 class="key_field">Were you diagnosed with COVID-19?</h5></td>
                        <td>
                            <select id="covid_positive" class="value_field" name="covid_positive">
                                <option value="">--SELECT--</option>
                                <option value="YES">YES</option>
                                <option value="NO">NO</option>
                            </select>
                        </td>
                        
                    </tr>
                    <tr>
                        <td><h5 class="if_covid_positive key_field">If yes, Indicate Date of Positive Result.</h5></td>
                        <td><input id="date_positive" class="if_covid_positive value_field" type="date" name="date_positive"></td>
                    </tr>
                </table>
                <table id="table4b">
                    <tr>
                        <td><h5 class="if_covid_positive key_field">If yes, Indicate the Classification of COVID-19</h5></td>
                        <td>
                            <select id="covid_classification" class="if_covid_positive value_field" name="covid_classification">
                                <option value="">--SELECT--</option>
                                <option value="1">ASYMPTOMATIC</option>
                                <option value="2">MILD</option>
                                <option value="3">MODERATE</option>
                                <option value="4">SEVERE</option>
                                <option value="5">VERY SEVERE OR CRITICAL</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><h5 class="key_field">Are you willing to be Vacinated?</h5></td>
                        <td>
                            <select id="willing_vaccine" class="valuefield" name="willing_vaccine">
                                <option value="">--SELECT--</option>
                                <option value="YES">YES</option>
                                <option value="UNSURE">UNSURE</option>
                                <option value="NO">NO</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
            <input id='response_submit' type='submit' name='response_submit' value='Submit Response'>
        </form>
    </div>
<?php
/*if ( !empty( $field_empty_list ) ) {
    $output = '<script>';
    $condition1 = 'if ( document.getElementById( "covid_positive" ).selectedIndex == 1 ) {' . PHP_EOL;
    $condition2 = 'else {' . PHP_EOL;
    foreach ( $field_empty_list as $itemNumber ) {
        if ( $field_ids[ $itemNumber ] != 'date_positive' && $field_ids[ $itemNumber ] != 'covid_classification' ) {
            $output .= 'document.getElementById( "' . $field_ids[ $itemNumber ] . '" ).style.border = "2px solid red";' . PHP_EOL;
        }
        else {
            $condition1 .= 'document.getElementById( "' . $field_ids[ $itemNumber ] . '" ).style.border = "2px solid red";' . PHP_EOL;
            $condition2 .= 'document.getElementById( "' . $field_ids[ $itemNumber ] . '" ).style.border = "1px solid #cacaca";' . PHP_EOL;
        }
    }
    $condition1 .= '}' . PHP_EOL;
    $condition = $condition1 . $condition2 . '}' . PHP_EOL;
    echo $output . $condition . '</script>';
}*/
if ( isset( $_POST[ 'response_submit' ] ) ) {
     global $wpdb;
     $table_name = $wpdb->prefix . 'camaliganVaccinationSurvey';
     $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );
     if ( $wpdb->get_var( $query ) == $table_name ) {
         $keyValue_Array = [];
         $errorIds = [];
         $have_errors = false;
         $posibleSelectAnswers = [ 'YES', 'NO', 'UNSURE', 'MALE', 'FEMALE',
            'SINGLE', 'MARRIED', 'DIVORCED', 'SEPARATED', 'WIDOWED', 'ZONE 1',
            'ZONE 2', 'ZONE 3', 'ZONE 4', 'ZONE 5', 'ZONE 6', 'ZONE 7', 'ZONE 7A',
            'ZONE 7B', 'DUGCAL', 'MARUPIT', 'SAN FRANCISCO', 'SAN JOSE - SAN PABLO',
            'SAN JUAN - SAN RAMON', 'SAN LUCAS', 'SAN MARCOS', 'SAN MATEO', 'SAN ROQUE',
            'STO. DOMINGO', 'STO. TOMAS', 'SUA', 'TAROSANAN', 'ASYMPTOMATIC', 'MILD',
            'MODERATE', 'SEVERE', 'VERY SEVERE OR CRITICAL' ];
         $colNames = [
            1  => 'name_last',
            2  => 'name_first', 
            3  => 'name_middle',
            4  => 'name_suffix',
            5  => 'gender',
            6  => 'birthday',
            7  => 'civil_status',
            8  => 'contact_number',
            9  => 'address_barangay',
            10 => 'address_zone',
            11 => 'occupation',
            12 => 'is_direct_contact',
            13 => 'is_pregnant',
            14 => 'have_drug_allergy',
            15 => 'have_food_allergy',
            16 => 'have_insect_allergy',
            17 => 'have_latex_allergy',
            18 => 'with_commorbidity',
            19 => 'have_heart_disease',
            20 => 'have_diabetes',
            21 => 'have_immunodeficiency',
            22 => 'have_mold_allergy',
            23 => 'have_pet_allergy',
            24 => 'have_pollen_allergy',
            25 => 'with_hypertension',
            26 => 'have_kidney_disease',
            27 => 'have_bronchial_asthma',
            28 => 'have_cancer',
            29 => 'have_other_disease',
            30 => 'was_diagnosed_with_c19',
            31 => 'if_yes_date_diagnosed',
            32 => 'if_yes_classification_of_c19',
            33 => 'is_willing_vaccine'
         ];
         $inputFieldArray = [
            1  => $_POST[ 'name_last' ],
            2  => $_POST[ 'name_first' ],
            3  => $_POST[ 'name_middle' ],
            4  => $_POST[ 'name_suffix' ] or '',
            6  => $_POST[ 'birth_date' ],
            8  => $_POST[ 'contact_number' ],
            11 => $_POST[ 'occupation' ],
            29 => $_POST[ 'other_disease' ],
            31 => $_POST[ 'date_positive' ] or ''
         ];
         $selectFieldArray = [
            9  => $_POST[ 'barangay' ],
            10 => $_POST[ 'zone' ],
            5  => $_POST[ 'gender' ],
            7  => $_POST[ 'civil_status' ],
            12 => $_POST[ 'direct_interact' ],
            13 => $_POST[ 'is_pregnant' ],
            14 => $_POST[ 'have_drug_allergy' ],
            15 => $_POST[ 'have_food_allergy' ],
            16 => $_POST[ 'have_insect_allergy' ],
            17 => $_POST[ 'have_latex_allergy' ],
            22 => $_POST[ 'have_mold_allergy' ],
            23 => $_POST[ 'have_pet_allergy' ],
            24 => $_POST[ 'have_pollen_allergy' ],
            18 => $_POST[ 'with_comorbidity' ],
            25 => $_POST[ 'have_hypertension' ],
            19 => $_POST[ 'have_heart_disease' ],
            26 => $_POST[ 'have_kidney_disease' ],
            20 => $_POST[ 'have_diabetes' ],
            27 => $_POST[ 'have_asthma' ],
            21 => $_POST[ 'have_immune_defeciency' ],
            28 => $_POST[ 'have_cancer' ],
            30 => $_POST[ 'covid_positive' ],
            32 => $_POST[ 'covid_classification' ] or '',
            33 => $_POST[ 'willing_vaccine' ]
         ];
         for( $i = 1; $i <= count( $colNames ); $i++ ) {
             if ( array_key_exists( $i, $inputFieldArray ) ) {
                 switch ( $i ) {
                     case 6 | 31:
                         $tmp_val = date_parse( $inputFieldArray[ $i ] );
                         if (checkdate( $tmp_val['month'], $tmp_val[ 'day' ], $tmp_val[ 'year' ])) {
                             $keyValue_Array[ $colNames[$i] ] = $tmp_val; 
                         }
                         else {
                             if ( $i == 31 ) {
                                if ( $selectFieldArray[ 30 ] == 'YES' ) {
                                    echo '<script>alert( "Cannot Upload Response. ' . $colNames[ $i ] . ' have invalid Value" )</script>' ;
                                }
                                else{ $keyValue_Array[ $colNames[$i] ] = ''; }
                             }
                             else {
                                 $have_errors = true;
                                 array_push( $i, $errorIds );
                             }
                         }
                         break;
                     default:
                         $tmp_val = filter_var( $inputFieldArray[ $i ], FILTER_SANITIZE_STRING );
                         $tmp_val = filter_var( $tmp_val, FILTER_SANITIZE_SPECIAL_CHARS );
                         $keyValue_Array[ $colNames[$i] ] = strtoupper($tmp_val);
                         break;
                 }
             }
             
             if ( array_key_exists( $i, $selectFieldArray ) ) {
                 if ( in_array(  $selectFieldArray[ $i ], $posibleSelectAnswers ) ) {
                     $keyValue_Array[ $colNames[$i] ] = $selectFieldArray[ $i ];
                 }
                 else {
                     if ( $i == 32 ) {
                        if ( $selectFieldArray[ 30 ] == 'YES' ) {
                            $have_errors = true;
                            array_push( $i, $errorIds );
                        }
                        else { $keyValue_Array[ $colNames[$i] ] = ''; }
                     }
                     else if( $i == 13 ) { 
                         if ( $keyValue_Array[ 'gender' ] == 'MALE' ) { $keyValue_Array[ $colNames[$i] ] = 'NO'; }
                         else {
                             $have_errors = true;
                             array_push( $i, $errorIds );
                         }
                     }
                 }
             }
         }
         if ( $have_errors ) {
             $tobe_echoed = '<script>alert("Cannot Upload Response, ';
             if ( count( $errorIds ) == 1 ) { $tobe_echoed .= $colNames[ $errorIds[ 0 ] ] . ' have unknown Field Value")</script>'; }
             else {
                 $tobe_echoed .= ' These Fields have an unknown values:' . PHP_EOL;
                 for( $i = 0; $i < count( $errorIds ); $i++ ) {
                     if ( $i < count - 1 ) {
                         $tobe_echoed .= 'Field Name: ' . $colNames[ $errorIds[ $i ] ] . ', Value: ';
                         if ( in_array( $errorIds[ $i ], $inputFieldArray ) ) { $tobe_echoed .= $inputFieldArray[ $errorIds[ $i ] ] . PHP_EOL; }
                         else { $tobe_echoed .= $selectFieldArray[ $errorIds[ $i ] ] . PHP_EOL; }
                     }
                     else {
                         if ( in_array( $errorIds[ $i ], $inputFieldArray ) ) { $tobe_echoed .= $inputFieldArray[ $errorIds[ $i ] ]; }
                         else { $tobe_echoed .= $selectFieldArray[ $errorIds[ $i ] ]; }
                     }
                 }
                 $tobe_echoed .= '" )</script>';
                 echo $tobe_echoed;
             }
         }
         if ( !$have_errors && count( $keyValue_Array ) == 33 ) {
             $wpdb->show_errors();
             if( $wpdb->insert( $table_name, $keyValue_Array ) == false ) { echo '<script>alert( "Failed to Upload your Response. Try again later" )</script>';
             }
             else { echo '<script>alert( "Successfully Uploaded your Response to Database. Thank you for the response" )</script>';
             }
         }
         else{
             if ( count( $keyValue_Array ) != 33 ) { echo '<script>alert( "Cannot Upload your response due Answer Count is not equal to Field Count." )</script>'; }
             else { echo '<script>alert( "Cannot Upload your response due to an unknown reason." )</script>'; }
         }
     }
     else {
         echo '<script>alert("Sorry.Failed to Upload Response. Cannot Communicate with the Database.")</script>';
     }
}
govph_displayoptions( 'govph_panel_bottom' );
get_footer();