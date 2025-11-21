<?php
print "start of cleanup" . PHP_EOL;
print "". PHP_EOL;
require_once "C:/inetpub/wwwroot/wx/website/ftpCredentials.php";
require_once "rrwParam.php";
print DoReferences() . PHP_EOL;
    print DoCardRename() . PHP_EOL;
    print mergeImages() . PHP_EOL;
    print DoFtp("images-nudges") . PHP_EOL;
    print DoFtp("images-combined") . PHP_EOL;
exit;
function DoReferences()
{
    $msg = "";
    try {
        $msg .= "Starting reference image processing..." . PHP_EOL;
        $imageDir = "E:/OldD/resch/retrospective/25 things/Roy's workings/images-renamed/";
        $outputDir = "E:/OldD/resch/retrospective/25 things/Roy's workings/images-nudges/";
        $msg .= "Image directory: $imageDir" . PHP_EOL;
        $msg .= "Output directory: $outputDir" . PHP_EOL;
        // Create output directory if it doesn't exist
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        if (! is_dir($imageDir)) {
            throw new Exception( "Image directory $imageDir does not exist!".PHP_EOL );
        }
        // Get all reference files
        $referenceFiles = glob($imageDir . "reference-*.png");
        foreach ($referenceFiles as $referenceFile) {
            $filename = basename($referenceFile);
            $number = str_replace(['reference-', '.png'], '', $filename);
            $outputFile = $outputDir . "nudge-" . $number . ".png";
            // Copy reference file to output directory as nudge file
            copy($referenceFile, $outputFile);
            $msg .= "Copied reference-$number.png to nudge-$number.png" . PHP_EOL;
        }
        $msg .= "Reference image processing complete!" . PHP_EOL;
    } catch (Exception $e) {
        $msg .= "Error: " . $e->getMessage() . PHP_EOL;
    }
    $msg .= " end of the DoReferences" . PHP_EOL . PHP_EOL;
    return $msg;
} // end DoReferences
function mergeImages (){
    $msg = "";
    try {
        $msg .= "Starting image merging process..." . PHP_EOL;
        $imageDir = "E:/OldD/resch/retrospective/25 things/Roy's workings/images-renamed/";
        $outputDir = "E:/OldD/resch/retrospective/25 things/Roy's workings/images-combined/";
        $msg .= "Image directory: $imageDir" . PHP_EOL;
        $msg .= "Output directory: $outputDir" . PHP_EOL;
        // Create output directory if it doesn't exist
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        if (! is_dir($imageDir)) {
            throw new Exception( "Image directory $imageDir does not exist!".PHP_EOL );
        }
        // Get all nudge files
        rename ($imageDir . "nudge Card#.png", $imageDir . "nudge-qr1-code.png");
        rename ($imageDir . "nudge Card#0.png", $imageDir . "nudge-qr2-code.png");
        $nudgeFiles = glob($imageDir . "nudge-*.png");
        foreach ($nudgeFiles as $nudgeFile) {
            $filename = basename($nudgeFile);
            $number = str_replace(['nudge-', '.png'], '', $filename);
            $referenceFile = $imageDir . "reference-" . $number . ".png";
            if (file_exists($referenceFile)) {
                $outputFile = $outputDir . "combined-" . $number . ".png";
                // Use ImageMagick to combine images side by side
                // +append combines horizontally, -append would combine vertically
                $command = "magick \"$nudgeFile\" \"$referenceFile\" +append \"$outputFile\"";
                exec($command, $output, $returnCode);
                if ($returnCode === 0) {
                    $msg .= "Combined nudge-$number.png with reference-$number.png -> combined-$number.png" . PHP_EOL;
                } else {
                    $msg .= "Error combining images for number $number" . PHP_EOL;
                }
            } else {
                $msg .= "Reference file not found for nudge-$number.png" . PHP_EOL;
            }
        }
        $msg .= self::RightHandParagraphs();
        $msg .= "Image merging complete!" . PHP_EOL;
    } catch (Exception $e) {
        $msg .= "Error: " . $e->getMessage() . PHP_EOL;
    }
    $msg .= " end of the mergeImages" . PHP_EOL . PHP_EOL;
    return $msg;
} // end mergeImages
function DoCardRename()
{
    $msg = "";
	$debugRename = false;
    try {
        $msg = "";
        $msg .= "Start of DoCardRename" . PHP_EOL;
        $sourceDire = "E:/OldD/resch/retrospective/25 things/Roy's workings/cards pngs all";
        //             E:\OldD\resch\retrospective\25 things\Roy's workings\cards pngs all\
        $renameDire = "E:/OldD/resch/retrospective/25 things/Roy's workings/images-renamed/";
        $wedDire = "E:/OldD/resch/retrospective/25 things/25 things web";
        $msg .= "Source directory: $sourceDire" . PHP_EOL;
        $msg .= "Rename directory: $renameDire" . PHP_EOL;
         //            E:\OldD\resch\retrospective\25 things\25 things cards\25 thing png
        if (! is_dir($sourceDire)) {
            throw new Exception( "Local directory $sourceDire does not exist!".PHP_EOL );
        }
     if (! is_dir($renameDire)) {
            throw new Exception( "Local directory $renameDire does not exist!".PHP_EOL );
        }
		$cntCopied = 0;
        foreach (new DirectoryIterator($sourceDire) as $fileInfo) {
             if ( $debugRename )$msg .= "processing file " . $fileInfo->getFilename() . PHP_EOL;
            if($fileInfo->isDot())
                continue;
            if($fileInfo->isFile()) {
                $oldFilePath = $fileInfo->getPathname();
                if ( $debugRename ) $msg .= "Processing file: $oldFilePath" . PHP_EOL;
                $oldFilename = $fileInfo->getFilename();
                $iiPound = strpos($oldFilename, "#");
				$fileNumber = substr( $oldFilename, $iiPound + 1 );
				$fileNumber = str_replace( ".png", "", $fileNumber );;
                if (is_numeric($fileNumber)) {
                    $nudgeNumber = intval($fileNumber/2);
                    if ( 2 > $nudgeNumber ) {
                        $msg .= "Skipping file number $oldFilename (nudge number $nudgeNumber)" . PHP_EOL;
                        continue;
                    }
                    $nudgeNumber = $nudgeNumber -2;
                    if (0 == $fileNumber %2 )
                        $newFilename = "nudge-$nudgeNumber.png";
                    else
                        $newFilename = "reference-$nudgeNumber.png";
                    $msg .= $oldFilePath . " to " . $newFilename . PHP_EOL;
                    $newFilePath = "$renameDire$newFilename";
                    if (file_exists($newFilePath)) {
                        unlink($newFilePath);
                        continue;
                    }
	                $msg .= "copy ($oldFilePath, $newFilePath);" . PHP_EOL;
                    $worked = copy($oldFilePath, $newFilePath);
                    if ($worked) {
                        $cntCopied++;
					} else {
						$msg .= "Failed to copy $oldFilePath to $newFilePath" . PHP_EOL;
					}
				} else {
					$msg .= "Filename $oldFilename is not numeric after cleanup, skipping." . PHP_EOL;
				}
            }// end if
        }// end foreach
        copy ("$wedDire/25 things cards v5.0m.404front#.png", $renameDire . "/nudge-404.png");
        copy ("$wedDire/25 things cards v5.0m.404back#.png", $renameDire . "/reference-404.png");
        $msg .= "Total files copied: $cntCopied" . PHP_EOL;
    } catch ( Exception $e ) {
        $msg .= "Exception caught in DoCardRename: " . $e->getMessage() . PHP_EOL;
    }
       $msg .= "End of DoCardRename" . PHP_EOL . PHP_EOL;
    return $msg;
} // DoCardRename
function RightHandParagraphs()
{
    return "Note: The right-hand paragraphs in each combined image are for reference only and are not part of the actual nudges." . PHP_EOL;
}
function DoFTP($directoryToUpload)
{
    $msg = "";
	try {
        $msg = "";
        $msg .= "Start of DoFTP for directory: $directoryToUpload" . PHP_EOL;
		$debugDownloads = rrwParam::Boolean( "debugDownloads", array(), false );
		$credentials = new FreewheelingEasy_website_ftpcredentials;
		$local_dire = "E:/OldD/resch/retrospective/25 things/Roy's workings/$directoryToUpload";
		$remoteDire = "/www-maryshaw-creative/wp-content/plugins/creative-nudges/$directoryToUpload";
        $server = $credentials->getFtpServer();
		$msg .= "FTP Server: " . $server . PHP_EOL;
		$conn_id = ftp_connect( $server );
		$login_result = ftp_login( $conn_id, $credentials->getFtpUser(), $credentials->getFtpPass() );
		if ( ( ! $conn_id ) || ( ! $login_result ) ) {
			$msg .= "E#1863 1FTP connection has failed!" . PHP_EOL;
			$msgErr= "E#1864 Attempted to connect to " . $credentials->getFtpServer() . " for user " . $credentials->getFtpUser();
			throw new Exception( $msgErr );
		} else {
			if ( $debugDownloads )
				echo "E#1865 Connected to " . $credentials->getFtpServer() . " for user " . $credentials->getFtpUser() . PHP_EOL;
		}
		$result = ftp_pasv( $conn_id, true );
		if ( ! $result ) {
			throw new Exception( "FTP passive mode has failed!" . PHP_EOL );
		} else {
			if ( $debugDownloads )
				$msg .= "Passive mode set" . PHP_EOL;
		}
       if (! is_dir($local_dire)) {
            throw new Exception( "Local directory $local_dire does not exist!".PHP_EOL );
        }
		$cntMoved = 0;
        foreach (new DirectoryIterator($local_dire) as $fileInfo) {
            $cntMoved++;
            if($cntMoved > 200) {
                break;
            }
            if($fileInfo->isDot()) continue;
            if($fileInfo->isFile()) {
                $localFilePath = $fileInfo->getPathname();
                $localFilePath = str_replace("\\", "/", $localFilePath);
                if ( $debugDownloads ) $msg .= "Processing file: $localFilePath" . PHP_EOL;
                $remoteFilePath = $remoteDire . '/' . $fileInfo->getFilename();
                $upload = ftp_put( $conn_id, $remoteFilePath, $localFilePath, FTP_BINARY );
                if ( ! $upload ) {
                    $msg .= "E#1866 FTP upload local file '$localFilePath' to remote file '$remoteFilePath' has failed!";
                } else {
                        $msg .= "uploaded $remoteFilePath".  PHP_EOL;
                }
            }
        }
		ftp_close( $conn_id );
        $msg .= "Upload $cntMoved file to $remoteDire" . PHP_EOL;
	} catch ( Exception $e ) {
        $msg .= "$msg " . PHP_EOL . PHP_EOL . "E#9999 Exception caught in DoFTP: " . $e->getMessage() . PHP_EOL;
    }
    $msg .= " end of DoFTP" . PHP_EOL . PHP_EOL;
	return $msg;
} // DoFTP
