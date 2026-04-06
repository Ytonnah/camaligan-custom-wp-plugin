<?php
/*
** Created By: Patrick James O. De Leon
** Website Developer of LGU Camaligan, Camarines Sur
*/

// Uploader Code
if(isset($_POST['but_submit']))
{
	if($_FILES['file']['size'] != '' && $_POST['datePublished'] != '' && $_POST['Classification'] != 'noSelection')
	{
		?><style>
			div#FormHolder
			{
				display: none;
			}
		</style><?php
		$classification = $_POST['Classification']; // Retrives the selected Classification
		$fileTrueCheck = finfo_open(FILEINFO_MIME_TYPE); // Retrieves the file type of the uploaded file
		$fileName = $_FILES['file']['name'];
		$fileName = preg_replace("/[^A-Za-z0-9_\s\.]/", "", $fileName);
		$fileName = preg_replace("/[\s-]+/", " ", $fileName);
		$fileName = preg_replace("/[\s_]/", "-", $fileName);
		$fileSize = $_FILES['file']['size'];
		$checkResult = finfo_file($fileTrueCheck, $_FILES['file']['tmp_name']);
		$datePublished = date('Y-m-d', strtotime($_POST['datePublished']));
		$custom_upload_directory = wp_upload_dir()['basedir'] . '/ordinances';
		$classification_upload_directory = $custom_upload_directory .'/' .$classification;
		$datePublished_upload_directory = $classification_upload_directory .'/' .$datePublished;
		$generated_path = $datePublished_upload_directory .'/';
		//Cleaning Empty Folders First
		EmptyFolder_Remover($custom_upload_directory);

		if(!is_dir($custom_upload_directory))
				{
					mkdir($custom_upload_directory, 0755);
				}
		if(SameFileName_Finder($custom_upload_directory, $fileName) == TRUE)
		{
			$infoHolder = 'ordinances' . '/' . $classification . '/' . $datePublished;
			$tempName = basename($_FILES['file']['tmp_name']);
			$tempLoc = wp_upload_dir()['basedir'] . '/temp-ord/'; // Temporary location of the file
			if(!is_dir($tempLoc))
			{
					mkdir(wp_upload_dir()['basedir'] . '/temp-ord', 0755);
			}
			move_uploaded_file($_FILES["file"]["tmp_name"], $tempLoc .'/' .$tempName);
		?><style>
				div#ContinueHolder
				{
					display: block;
				}
			</style><?php
		}
		else
		{
			if($checkResult == 'application/pdf' && $classification != '' && $classification != NULL)
			{
				if(!is_dir($classification_upload_directory))
				{
					mkdir($classification_upload_directory, 0755);

				}
				if(!is_dir($datePublished_upload_directory))
				{
					mkdir($datePublished_upload_directory, 0755);
				}

				$finalLoc = $datePublished_upload_directory . '/' .$fileName;
				if(move_uploaded_file($_FILES["file"]["tmp_name"], $finalLoc))
					$ordinance_attachement = array
					(
						"post_mime_type" => $checkResult,
						"post_title" => preg_replace( '/\.[^.]+$/', '', basename($_FILES['file']['name'])),
						"post_date" => $datePublished,
						"guid" => $finalLoc,
						"post_content" => "",
						"post_status" => "inherit",
						"size" => $fileSize,
						"post_type" => "attachment"
					);
					$id = wp_insert_attachment($ordinance_attachement, $finalLoc);
					$attach_data = wp_generate_attachment_metadata( $id, $finalLoc );
					wp_update_attachment_metadata( $id, $attach_data );
					wp_set_post_terms( $id, $classification, 'post_tag', FALSE );?>
				<style>
					div#ConfirmationHolder
					{
						display: block;
					}
					div#ContinueHolder
					{
						display: none;
					}
					div#FormHolder
					{
						display: none;
					}
				</style><?php
			}
			else if($_FILES['file']['name'] != '' && $checkResult != 'application/pdf')
			{
				echo "<br>You dirty bastard attempting to hack me?..(If no, then sorry.)<br>Uploading a file that you changed the file type to .pdf via rename!!!!.<br> <u>Shame on <b>YOU</b></u> and hope you go to hell!!!!!!!.";
			}
		}
	}
	elseif($_FILES['file']['size'] != '' && $_POST['datePublished'] == '' && $_POST['Classification'] == 'noSelection')
	{
		echo "<br>You attached a file but forgot to select its Classification and the Date it was created.<br>File Upload Denied.";
	}

	elseif($_FILES['file']['size'] == '' && $_POST['datePublished'] != '' && $_POST['Classification'] == 'noSelection')
	{
		echo "<br>You only filled up the date this ordinance published.<br>The file is nowhere to be found and also no Classification selected.<br>File Upload Denied.";
	}

	elseif($_FILES['file']['size'] == '' && $_POST['datePublished'] == '' && $_POST['Classification'] != 'noSelection')
	{
		echo "<br>You only Selected the Classification of the Ordinance.<br>No file was selected and no date was inputed.<br>File Upload Denied.";
	}

	elseif($_FILES['file']['size'] != '' && $_POST['datePublished'] != '' && $_POST['Classification'] == 'noSelection')
	{
		echo "<br>You forgot to select the Classification of the Ordinance.<br>File Upload Denied.";
	}

	elseif($_FILES['file']['size'] != '' && $_POST['datePublished'] == '' && $_POST['Classification'] != 'noSelection')
	{
		echo "<br>You forgot to input the date it was created.<br>File Upload Denied.";
	}

	elseif($_FILES['file']['size'] == '' && $_POST['datePublished'] != '' && $_POST['Classification'] != 'noSelection')
	{
		echo "<br>No file was attached.<br>File Upload Denied.";
	}

	else
	{
		echo "<br>The submit button is not a doorbell.<br>This is a button for submitting the file attached, date created and classification of the Ordinance File.";
	}
}

if(isset($_POST['Overwrite_trigger']))
{
	$classification = $_POST['Classification'];
	$datePublished = $_POST['datePublished'];
	$fileName = $_POST['fileName'];
	$checkResult = $_POST['checkResult'];
	$fileSize = $_POST['fileSize'];
	$tempLoc = wp_upload_dir()['basedir'] . '/temp-ord/' . $_POST['tempName'];
	$finalLoc = wp_upload_dir()['basedir'] . '/' . $_POST['FinalPath'] . '/' .$fileName;
	$custom_upload_directory = wp_upload_dir()['basedir'] . '/ordinances';
	$classification_upload_directory = $custom_upload_directory .'/' .$classification;
	$datePublished_upload_directory = $classification_upload_directory .'/' .$datePublished;
	if(!is_dir($custom_upload_directory))
	{
		mkdir($custom_upload_directory, 0755);
	}
	if(!is_dir($classification_upload_directory))
	{
		mkdir($classification_upload_directory, 0755);

	}
	if(!is_dir($datePublished_upload_directory))
	{
		mkdir($datePublished_upload_directory, 0755);
	}
	rename($tempLoc, $finalLoc);
	rmdir(wp_upload_dir()['basedir'] . '/temp-ord');
	$ordinance_attachement = array(
		"post_mime_type" => $checkResult,
		"post_title" => preg_replace( '/\.[^.]+$/', '', $fileName),
		"post_date" => $datePublished,
		"guid" => $finalLoc,
		"post_content" => "",
		"post_status" => "inherit",
		"size" => $fileSize,
		"post_type" => "attachment"
	);
	$id = wp_insert_attachment($ordinance_attachement, $finalLoc);
	$attach_data = wp_generate_attachment_metadata( $id, $finalLoc );
	wp_update_attachment_metadata( $id, $attach_data );
	wp_set_post_terms( $id, $classification, 'post_tag', FALSE );
	?><style>
		div#ConfirmationHolder
		{
			display: block;
		}
		div#ContinueHolder
		{
			display: none;
		}
		div#FormHolder
		{
			display: none;
		}
	</style><?php
}
if(isset($_POST['Overwrite_Cancel']))
{
	$tempLoc = wp_upload_dir()['basedir'] . '/temp-ord';
	$tempFile = $tempLoc . '/' . $_POST['tempName'];
	unlink($tempFile);
	rmdir($tempLoc);
}

function SameFileName_Finder($dir, $file)
{
	static $Counter = 1;
	$Files = scandir($dir);
	foreach($Files as $key => $value)
	{
		$Path = realpath($dir.DIRECTORY_SEPARATOR.$value);
		$File_dir = realpath($dir.DIRECTORY_SEPARATOR);
		if(!is_dir($Path))
		{
			if($file == $value)
			{

				if($Counter == 1)
				{?><span style="color: #5e0514; font-size: 32px; font-weight: 900; text-decoration: underline;"><br>WARNING<br></span>
					<span style="color: Darkblue; font-size: 16px; font-weight: 700;"><br>FILE ALREADY EXIST/S IN THE FF. DIRECTORY:<br></span><?php
				}
				?><span style="color: black; font-size: 16px; font-weight: 700;"><?php echo "$Counter.$File_dir<br>";?></span><?php
				$Counter ++;
				break;
			}
		}
		else if($value != "." && $value != "..")
		{
			SameFileName_Finder($Path, $file);
		}
	}
	if($Counter > 1)
	{
		return TRUE;
	}
}

function EmptyFolder_Remover($path)
{
	$IsEmpty = TRUE;
	foreach( glob($path.DIRECTORY_SEPARATOR."*") as $object)
	{
		if(is_dir($object))
		{
			if(!EmptyFolder_Remover($object))
			{
				$IsEmpty = FALSE;
			}
		}

		else
		{
			$IsEmpty = FALSE;
		}
	}

	if($IsEmpty)
	{
		rmdir($path);
	}
}

?><style>
	div.OverwriteQuestion
	{
		display: none;
	}

	div.FinishHolder
	{
		display: none;
	}

	#no_button
	{
		background-color: green;
		color: black;
		float: left;
		margin-right: 30px;
	}

	#yes_button
	{
		background-color: red;
		color: white;
	}
</style>


<div id="FormHolder">
	<h1 style="align: center; font-family: 'Times New Roman', Arial; font-size: 28px; font-weight: 900;">Upload Ordinances</h1>
	<h6 style="align: center; font-size: 16px; fotn-family: Helvetica, Arial;"> This plugin will only accept pdf form </h6>
<!-- Form -->

	<form method='post' action='' name='myform' enctype='multipart/form-data'>
		<table>
			<tr>
				<td><b>Upload File</b></td>
				<td><input type='file' name='file' accept="application/pdf"></td>
			</tr>
			<tr>
				<td><b>Classification of the Ordinance</b></td>
				<td>
					<select name="Classification">
						<option value="noSelection">--SELECT CLASSIFICATION--</option>
						<option value="Administrative">ADMINISTRATIVE</option>
						<option value="Development">DEVELOPMENT</option>
						<option value="Environment">ENVIRONMENT</option>
						<option value="HealthandSanitation">HEALTH AND SANITATION</option>
						<option value="LIT">LOCAL TAXATION AND INCENTIVES</option>
						<option value="PublicUtilities">PUBLIC UTILITIES</option>
						<option value="Social">SOCIAL</option>
					</select>
				</td>
			</tr>
			<tr>
				<td><b>Date Published</b></td>
				<td>
					<input type="date" name="datePublished">
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><input type='submit' name='but_submit' value='Upload'></td>
			</tr>
		</table>
	</form>
</div>

<div id="ContinueHolder" class="OverwriteQuestion">
	<span style="color: #c28d06; font-size: 32px; font-weight: 900;"><br>CONTINUE??<br></span>
	<div>
		<div>
				<form method='post'>
					<input type='submit' name='Overwrite_Cancel' value='NO' id="no_button" style="color: black; font-weight: 900; font-size: 24px">
					<input type='hidden' name='tempName' value='<?php echo $tempName; ?>'>
				</form>
				<form method='post'>
					<input type='submit' name='Overwrite_trigger' value='YES' id="yes_button" style="color: white; font-weight: 900; font-size: 24px">
					<input type='hidden' name='tempName' value='<?php echo $tempName; ?>'>
					<input type='hidden' name='FinalPath' value='<?php echo $infoHolder; ?>'>
					<input type='hidden' name='Classification' value='<?php echo $classification; ?>'>
					<input type='hidden' name='datePublished' value='<?php echo $datePublished; ?>'>
					<input type='hidden' name='fileName' value='<?php echo $fileName; ?>'>
					<input type='hidden' name='checkResult' value='<?php echo $checkResult; ?>'>
					<input type='hidden' name='fileSize' value='<?php echo $fileSize; ?>'>
				</form>
		</div>
	</div>
	<div>
		<span style="color: Darkblue; font-size: 16px; font-weight: 700;"><br>WHAT HAPPENS WHEN YOU CONTINUE?<br></span>
		<span style="color: black; font-size: 16px; font-weight: 700;">
			<ul>
				<li>It will cause a duplication of ordinance files in the homepage.<li>
				<li>If the generated path of your chosen CLASSIFICATION and DATE is in the list of Paths having the same Filename, the older file will be replaced.</li>
			</ul>
			<br><br>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<span style="color: darkblue"><u>Path Generated by your chosen Classification and Date</u></span> => <?php echo "$generated_path"; ?>
		</span>
	</div>
</div>

<div id='ConfirmationHolder' class='FinishHolder' style="position: absolute; left:0; top: 50%;" width="100%" height="100%">
	<span style="color: black; font-size: 16px; font-weight: 700;">Your Ordinance was uploaded to the Server without encountering any issues.
		<a onclick="history.go(0)" style="cursor: pointer;"><br>Go Back to Main Page</a>
	</span>

</div>