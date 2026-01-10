#include <FileConstants.au3>
#include <MsgBoxConstants.au3>
#include <Array.au3>
#include <String.au3>
#include <WinAPIShPath.au3>
#include <WinAPIFiles.au3>
#include <Date.au3>
#include <file.au3>

Const $WORK_FOLDER = 'C:\queue\' ;рабочая директория
Const $PIDFILE = @ScriptDir & '\autoit.pid' ;pid файл

If ProcessExists("PdfPrinter.exe") Then ; Check if the Notepad process is running.
   ; все ок, он запущен
Else
   FileDelete($PIDFILE)
   Run("PdfPrinter.exe", $WORK_FOLDER, @SW_HIDE, 2 + 4)
EndIf