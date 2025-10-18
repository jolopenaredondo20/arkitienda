<?php
error_reporting(E_ALL);

// === Auto-detect modem on Windows ===
function autoDetectModem($baud = 19200) {
    for ($i = 1; $i <= 20; $i++) {
        $port = "COM" . $i;
        try {
            $gsm = new gsm_send_sms();
            $gsm->debug = false;
            $gsm->port = $port;
            $gsm->baud = $baud;
            $gsm->init();
            $gsm->close();
            return $port; // Found working modem
        } catch (Exception $e) {
            continue; // Try next COM
        }
    }
    return false;
}

// === Try to auto connect right away ===
$connectedPort = autoDetectModem();
$message = $connectedPort ? "✅ Auto-connected to modem on " . $connectedPort 
                          : "❌ No modem detected. Check drivers & cable.";

// === Handle Send SMS ===
if (isset($_POST['send_sms']) && $connectedPort) {
    $gsm = new gsm_send_sms();
    $gsm->debug = false;
    $gsm->port = $connectedPort;
    $gsm->baud = 19200;

    try {
        $gsm->init();
        $status = $gsm->send($_POST['number'], $_POST['message']);
        $gsm->close();

        if ($status) {
            $message = "📤 Message sent successfully to {$_POST['number']}";
        } else {
            $message = "⚠️ Message failed to send.";
        }
    } catch (Exception $e) {
        $message = "❌ Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>SMS via GSM Modem</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f4f4f4; padding:20px; }
        .box { background:#fff; padding:20px; border-radius:10px; width:400px; margin:auto; box-shadow:0 0 10px rgba(0,0,0,0.2); }
        input, textarea, button { width:100%; padding:10px; margin:8px 0; border-radius:5px; border:1px solid #ccc; }
        button { background:#007bff; color:#fff; font-weight:bold; cursor:pointer; }
        button:hover { background:#0056b3; }
        .msg { margin:10px 0; font-weight:bold; }
    </style>
</head>
<body>
<div class="box">
    <h2>📡 SMS Sender</h2>

    <?php if (!empty($message)) echo "<p class='msg'>$message</p>"; ?>

    <form method="post">
        <input type="text" name="number" placeholder="Enter phone number" required>
        <textarea name="message" placeholder="Enter your message here..." required></textarea>
        <button type="submit" name="send_sms" <?php if(!$connectedPort) echo "disabled"; ?>>📤 Send SMS</button>
    </form>
</div>
</body>
</html>
<?php
// === GSM SMS Class ===
class gsm_send_sms {
    public $port = 'COM1';
    public $baud = 115200;
    public $debug = false;
    private $fp;
    private $buffer;

    public function init() {
        exec("MODE {$this->port}: BAUD={$this->baud} PARITY=N DATA=8 STOP=1", $output, $retval);
        if ($retval != 0) throw new Exception("Unable to setup COM port: {$this->port}");
        $this->fp = @fopen($this->port . ':', 'r+');
        if (!$this->fp) throw new Exception("Unable to open port {$this->port}");
        fputs($this->fp, "AT\r");
        if (!$this->wait_reply("OK\r\n", 5)) throw new Exception("No response from modem");
        fputs($this->fp, "AT+CMGF=1\r");
        if (!$this->wait_reply("OK\r\n", 5)) throw new Exception("Unable to set text mode");
    }

    private function wait_reply($expected, $timeout) {
        $this->buffer = '';
        $timeoutat = time() + $timeout;
        do {
            $this->buffer .= fread($this->fp, 1024);
            usleep(200000);
            if (preg_match('/'.preg_quote($expected, '/').'$/', $this->buffer)) return true;
            if (preg_match('/\+CMS ERROR\:/', $this->buffer)) return false;
        } while ($timeoutat > time());
        return false;
    }

    public function close() { if ($this->fp) fclose($this->fp); }

    public function send($tel, $message) {
        $tel = preg_replace("%[^0-9\+]%", '', $tel);
        fputs($this->fp, "AT+CMGS=\"{$tel}\"\r");
        if (!$this->wait_reply("\r\n> ", 5)) return false;
        fputs($this->fp, $message);
        fputs($this->fp, chr(26));
        return $this->wait_reply("OK\r\n", 30);
    }
}
?>
