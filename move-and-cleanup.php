<?php

print "start of cleanup" . PHP_EOL;
print "". PHP_EOL;

require_once "C:/inetpub/wwwroot/wx/website/ftpCredentials.php";
require_once "rrwParam.php";

    print DoCardRename() . PHP_EOL;

   print DoFtp() . PHP_EOL;

exit;

function DoCardRename()
{
    $msg = "";
	$debugRename = false;
    try {
         //            E:\OldD\resch\retrospective\25 things\25 things cards\25 thing png
        $sourceDire = "E:/OldD/resch/retrospective/25 things/25 things cards/25 thing png/";
        $renameDire = "E:/OldD/resch/retrospective/25 things/Roy's workings/images-renamed/";
        if (! is_dir($sourceDire)) {
            throw new Exception( "Local directory $sourceDire does not exist!".PHP_EOL );
        }

     if (! is_dir($renameDire)) {
            throw new Exception( "Local directory $renameDire does not exist!".PHP_EOL );
        }
		$cntCopied = 0;
        foreach (new DirectoryIterator($sourceDire) as $fileInfo) {
            if($fileInfo->isDot()) continue;
            if($fileInfo->isFile()) {
                $oldFilePath = $fileInfo->getPathname();
                if ( $debugRename ) $msg .= "Processing file: $oldFilePath" . PHP_EOL;
                $oldFilename = $fileInfo->getFilename();
                $filename = str_replace("25 things cards","", $oldFilename);
                $filename = str_replace(" v4.1","", $filename);
                $filename = str_replace(".png","", $filename);
                $fileNumber = trim($filename);
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
                    }
                }

            }// end if
        }// end foreach
        $msg .= "Total files copied: $cntCopied" . PHP_EOL;
    } catch ( Exception $e ) {
        $msg .= "Exception caught in DoCardRename: " . $e->getMessage() . PHP_EOL;
    }
    return $msg;

} // DoCardRename

function DoFTP()
{
    $msg = "";
	try {
		$debugDownloads = rrwParam::Boolean( "debugDownloads", array(), true );
		$credentials = new FreewheelingEasy_website_ftpcredentials;
		$local_dire = "E:/OldD/resch/retrospective/25 things/Roy's workings/images-renamed/";
		$remoteDire = "/www-maryshaw-creative/wp-content/plugins/creative-nudges/images-nudges/";

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

        foreach (new DirectoryIterator($local_dire) as $fileInfo) {
            if($fileInfo->isDot()) continue;
            if($fileInfo->isFile()) {
                $localFilePath = $fileInfo->getPathname();
                if ( $debugDownloads ) $msg .= "Processing file: $localFilePath" . PHP_EOL;
                $remoteFilePath = $remoteDire . '/' . $fileInfo->getFilename();
                $upload = ftp_put( $conn_id, $remoteFilePath, $localFilePath, FTP_BINARY );
                if ( ! $upload ) {
                    $msg .= "$errorBeg E#1866 FTP upload local file '$localFilePath' to remote file '$remoteFilePath' has failed!$errorEnd";
                } else {
                    if ( $debugDownloads )
                        $msg .= "uploaded $localFilePath".  PHP_EOL;
                }
            }
        }

		ftp_close( $conn_id );
	} catch ( Exception $e ) {
        $msg .= "$msg " . PHP_EOL . PHP_EOL . "E#9999 Exception caught in DoFTP: " . $e->getMessage() . PHP_EOL;
    }
	return $msg;

} // DoFTP
