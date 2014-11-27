<?php

define("SERIAL_DEVICE_NOTSET", 0);
define("SERIAL_DEVICE_SET", 1);
define("SERIAL_DEVICE_OPENED", 2);

class phpSerial {

    var $_device = null;
    var $_windevice = null;
    var $_dHandle = null;
    var $_dState = SERIAL_DEVICE_NOTSET;
    var $_buffer = "";
    var $_os = "";

    /**
     * This var says if buffer should be flushed by sendMessage (true) or manualy (false)
     *
     * @var bool
     */
    var $autoflush = true;

    /**
     * Constructor. Perform some checks about the OS and setserial
     *
     * @return phpSerial
     */
    function phpSerial() {
        setlocale(LC_ALL, "en_US");

        $sysname = php_uname();
    }

    //
    // OPEN/CLOSE DEVICE SECTION -- {START}

    //

	/**
     * Device set function : used to set the device name/address.
     * -> linux : use the device address, like /dev/ttyS0
     * -> windows : use the COMxx device name, like COM1 (can also be used
     *     with linux)
     *
     * @param string $device the name of the device to be used
     * @return bool
     */
    function deviceSet($device) {
        $this->_device=$device;
    }

    /**
     * Opens the device for reading and/or writing.
     *
     * @param string $mode Opening mode : same parameter as fopen()
     * @return bool
     */
    function deviceOpen($mode = "r+b") {
        
    }

    /**
     * Closes the device
     *
     * @return bool
     */
    function deviceClose() {
    }

    //
    // OPEN/CLOSE DEVICE SECTION -- {STOP}
    //

	//
	// CONFIGURE SECTION -- {START}

    //

	/**
     * Configure the Baud Rate
     * Possible rates : 110, 150, 300, 600, 1200, 2400, 4800, 9600, 38400,
     * 57600 and 115200.
     *
     * @param int $rate the rate to set the port in
     * @return bool
     */
    function confBaudRate($rate) {
        
    }

    /**
     * Configure parity.
     * Modes : odd, even, none
     *
     * @param string $parity one of the modes
     * @return bool
     */
    function confParity($parity) {
        
    }

    /**
     * Sets the length of a character.
     *
     * @param int $int length of a character (5 <= length <= 8)
     * @return bool
     */
    function confCharacterLength($int) {
        
    }

    /**
     * Sets the length of stop bits.
     *
     * @param float $length the length of a stop bit. It must be either 1,
     * 1.5 or 2. 1.5 is not supported under linux and on some computers.
     * @return bool
     */
    function confStopBits($length) {
        
    }

    /**
     * Configures the flow control
     *
     * @param string $mode Set the flow control mode. Availible modes :
     * 	-> "none" : no flow control
     * 	-> "rts/cts" : use RTS/CTS handshaking
     * 	-> "xon/xoff" : use XON/XOFF protocol
     * @return bool
     */
    function confFlowControl($mode) {
        
    }

    /**
     * Sets all appropriate parameters for communicating with Relay board in one fell swoop
     * (9600 8-N-1 no flow control)
     */
    function initRlyBoard() {
        
    }

    /**
     * Sets a setserial parameter (cf man setserial)
     * NO MORE USEFUL !
     * 	-> No longer supported
     * 	-> Only use it if you need it
     *
     * @param string $param parameter name
     * @param string $arg parameter value
     * @return bool
     */
    function setSetserialFlag($param, $arg = "") {
        
    }

    //
    // CONFIGURE SECTION -- {STOP}
    //

	//
	// I/O SECTION -- {START}

    //

	/**
     * Sends a string to the device
     *
     * @param string $str string to be sent to the device
     * @param float $waitForReply time to wait for the reply (in seconds)
     */
    function sendMessage($str, $waitForReply = 0.1) {
    $address = $this->_device;
    $port = '9100';
      if (isset($port) and
      ($socket=socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) and
      (socket_connect($socket, $address, $port)))
    {
      $text="Connection successful on IP $address, port $port";
    }
    
        $msg = $str;

        $length = strlen($msg);
        while(true) {
            $sent = socket_write($socket,$msg,$length);
            if($sent === false) {
                return false; 
            }
            if($sent < $length) {
                $msg = substr($msg, $sent);
                $length -= $sent;
            } else {
                return true; 
            }
        }
        return false;
      socket_close($socket);
    }

    /**
     * Reads the port until no new datas are availible, then return the content.
     *
     * @pararm int $count number of characters to be read (will stop before
     * 	if less characters are in the buffer)
     * @return string
     */
    function readPort($count = 0) {
        
    }

    /**
     * Flushes the output buffer
     *
     * @return bool
     */
    function flush() {
        
    }

    //
    // I/O SECTION -- {STOP}
    //

	//
	// INTERNAL TOOLKIT -- {START}
    //

	function _ckOpened() {
        
    }

    function _ckClosed() {
        
    }

    function _exec($cmd, &$out = null) {
        
    }

    //
    // INTERNAL TOOLKIT -- {STOP}
//
}

?>
