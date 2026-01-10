#include <File.au3>
#include <Array.au3>
#include <MsgBoxConstants.au3>

Global $SUMATRA = "C:\queue\sumatra_pdf.exe"
Global $SEVENZIP = @ScriptDir & "\7z.exe"
Global $UNRAR = @ScriptDir & "\unrar.exe"
Global $TEMP_FOLDER = @TempDir & "\pdfprint_" & @YEAR & @MON & @MDAY & "_" & @HOUR & @MIN & @SEC

; Выбор файла
Local $sFile = FileOpenDialog("Выберите PDF или архив", @ScriptDir, "PDF и архивы (*.pdf;*.zip;*.rar)", 1)
If @error Then Exit

; Расширение (без точки)
Local $sExt = StringLower(_GetExtension($sFile))

Func _GetExtension($sPath)
    Return StringTrimLeft($sPath, StringInStr($sPath, ".", 0, -1))
EndFunc

Switch $sExt
    Case "pdf"
        printFile($sFile)

    Case "zip"
        If Not FileExists($SEVENZIP) Then
            MsgBox($MB_ICONERROR, "Ошибка", "Не найден 7z.exe в папке скрипта.")
            Exit
        EndIf
        DirCreate($TEMP_FOLDER)
        Local $cmd = '"' & $SEVENZIP & '" x "' & $sFile & '" -o"' & $TEMP_FOLDER & '" -y'
        RunWait(@ComSpec & " /c " & $cmd, "", @SW_HIDE)
        processExtractedPDFs($TEMP_FOLDER)

    Case "rar"
        If Not FileExists($UNRAR) Then
            MsgBox($MB_ICONERROR, "Ошибка", "Не найден unrar.exe в папке скрипта.")
            Exit
        EndIf
        DirCreate($TEMP_FOLDER)
        Local $cmd = '"' & $UNRAR & '" x -o+ "' & $sFile & '" "' & $TEMP_FOLDER & '\*"'
        RunWait(@ComSpec & " /c " & $cmd, "", @SW_HIDE)
        processExtractedPDFs($TEMP_FOLDER)

    Case Else
        MsgBox($MB_ICONERROR, "Ошибка", "Поддерживаются только PDF и архивы ZIP/RAR.")
        Exit
EndSwitch

Func printFile($filename)
    RunWait(@ComSpec & " /c " & '"' & $SUMATRA & '" -print-to-default -exit-when-done "' & $filename & '"', "", @SW_HIDE)
EndFunc

Func processExtractedPDFs($folder)
    Local $aPDFs = _FileListToArrayRec($folder, "*.pdf", $FLTAR_FILES, $FLTAR_RECUR, $FLTAR_SORT, $FLTAR_NOSORT, 1, $FLTAR_FULLPATH)
    If @error Then
        MsgBox($MB_ICONERROR, "Ошибка", "PDF-файлы не найдены после распаковки.")
        Return
    EndIf

    For $i = 1 To $aPDFs[0]
        printFile($aPDFs[$i])
    Next
EndFunc
