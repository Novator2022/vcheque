#include <FileConstants.au3>
#include <MsgBoxConstants.au3>
#include <Array.au3>
#include <String.au3>
#include <WinAPIShPath.au3>
#include <WinAPIFiles.au3>
#include <Date.au3>
#include <file.au3>

Func getQueue()
   Dim $hCurl = Run(@ComSpec & " /c " & "curl.exe -s " & $WATCH_URL, $WORK_FOLDER, @SW_HIDE,2 + 4)
   Dim $curlOut;
   While 1
	  $curlOut &= StdoutRead($hCurl)
	  If @error Then ExitLoop
   WEnd

   Local $orderId = Number($curlOut, $NUMBER_32BIT)

   return $orderId;
EndFunc

Func getFile($order_id)
   FileDelete($WORK_FOLDER & 'tmp.pdf');

   Dim $hCurl = Run(@ComSpec & " /c " & "curl.exe -s -o tmp.pdf " & $PDF_URL & $order_id, $WORK_FOLDER, @SW_HIDE, 2 + 4)
   Dim $curlOut;
   While 1
	  $curlOut &= StdoutRead($hCurl)
	  If @error Then ExitLoop
   WEnd

   If (FileExists($WORK_FOLDER & 'tmp.pdf')) Then
	  return $WORK_FOLDER & "tmp.pdf"
   Else
	  return false
   EndIf
EndFunc

Func completeOrder($order_id)
   Dim $hCurl = Run(@ComSpec & " /c " & "curl.exe -s -X POST " & $COMPLETE_URL & $order_id, $WORK_FOLDER, @SW_HIDE,2 + 4)
   Dim $curlOut;
   While 1
	  $curlOut &= StdoutRead($hCurl)
	  If @error Then ExitLoop
   WEnd

   If ($curlOut == 'OK') Then
	  return true
   Else
	  return false
   EndIf
EndFunc

Func printFile($filename)
   RunWait(@ComSpec & " /c " & "sumatra_pdf.exe -print-to-default -exit-when-done " & $filename, $WORK_FOLDER, @SW_HIDE, 2 + 4)
EndFunc

Const $WORK_FOLDER = 'C:\queue\' ;рабочая директория
Const $WATCH_URL = 'https://vcheque.bazatoday.com/printer/iNgsTiCuNTrAYAotcHALIaTKIwAWNBRe/queue'
Const $PDF_URL = 'https://vcheque.bazatoday.com/printer/iNgsTiCuNTrAYAotcHALIaTKIwAWNBRe/pdf/'
Const $COMPLETE_URL = 'https://vcheque.bazatoday.com/printer/iNgsTiCuNTrAYAotcHALIaTKIwAWNBRe/complete/'
Const $PIDFILE = @ScriptDir & '\autoit.pid' ;pid файл
Const $LOGFILE = @ScriptDir & '\log.txt' ;log файл

;Const $WATCH_FOLDER = $WORK_FOLDER & 'queue\' ;директория с заданиями
;Const $PIDFILE = $WORK_FOLDER & 'autoit.pid' ;pid файл
;Const $DISK = 'X:' ;временный файл
;Const $DISK_FOLDER = $WORK_FOLDER & 'disk' ;ОБЯЗАТЕЛЬНО БЕЗ КОНЕЧНОГО СЛЕША!
;Const $DOWNLOAD_URL = $WATCH_URL & '/download?f='
;Const $LOG_URL = $WATCH_URL & '/log?'
;Const $STATUS_URL = $WATCH_URL & '/status?'

; если файл существует - копия уже запущена
If (FileExists($PIDFILE)) Then
   MsgBox($MB_SYSTEMMODAL, "", "Копия программы уже запущена")
   Exit(1)
EndIf

; делаем файл для отслеживания запуска
Local $hFileOpen = FileOpen($PIDFILE, $FO_OVERWRITE)
If $hFileOpen = -1 Then
   MsgBox($MB_SYSTEMMODAL, "", "An error occurred while writing the pid file.")
   FileDelete($PIDFILE)
   Exit (1)
EndIf

FileWrite($hFileOpen, _NowDate())
FileClose($hFileOpen)

; основная работа
While 1
   $orderId = getQueue();

   If ($orderId > 0) AND ($orderId <= 999999) Then
	  ConsoleWrite($orderId & @CRLF)

	  $file = getFile($orderId)

	  ConsoleWrite('getFile OK! (' & $file & ')' & @CRLF)

	  $is_complete = completeOrder($orderId);

	  If ($is_complete) Then
		 ConsoleWrite('start printFile' & @CRLF)
		 printFile($file)
		 ConsoleWrite('end printFile' & @CRLF)

		 ConsoleWrite('completeOrder OK!' & @CRLF)
	  Else
		 ConsoleWrite('completeOrder ERR!' & @CRLF)
	  EndIf
   EndIf

   Sleep(10000);
WEnd