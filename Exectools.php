<?php
// Run reminder.exe in front of system
pclose(popen("start /B \"\" \"C:\\Arki.Tienda\\Arkie Reminders.exe\"", "r"));
echo "SMS Program Launched!";
?>
