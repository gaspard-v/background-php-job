<?php
class ConnectionToClientBuilder
{
    /**
     * @var ?string
     */
    protected $dataToSend = null;
    /**
     * @var ?string
     */
    protected $contentType = null;

    /**
     * @var int
     */
    protected $httpResponseCode = 200;

    /**
     * @var bool
     */
    protected $closeConnection = true;
    public function __construct() {
        ignore_user_abort(true);
        set_time_limit(0);
    }
    public function setDataToSend(string $dataToSend): self
    {
        trigger_error("Sending data may not close the connection between client and server", E_USER_WARNING);
        $this->dataToSend = $dataToSend;
        return $this;
    }
    public function setContentType(string $contentType): self
    {
        $this->contentType = $contentType;
        return $this;
    }
    public function setHttpResponseCode(int $httpResponseCode): self
    {
        $this->httpResponseCode = $httpResponseCode;
        return $this;
    }
    public function setCloseConnection(bool $closeConnection): self
    {
        $this->closeConnection = $closeConnection;
        return $this;
    }
    protected function sendConnectionHeader(): void
    {
        if($this->closeConnection)
            header('Connection: close');
    }
    protected function sendContentEncodingHeader(): void
    {
        if(!$this->dataToSend)
            header('Content-Encoding: none');
    }
    protected function sendContentTypeHeader(): void
    {
        if($this->contentType && $this->dataToSend)
            header("Content-Type: {$this->contentType}");
    }
    protected function sendHeaders(): void
    {
        $this->sendConnectionHeader();
        $this->sendContentEncodingHeader();
        $this->sendContentTypeHeader();
    }
    /**
     * @param bool $keepSessionOpens WARNING, keeping sessions open may causes issues
     */
    public function closeConnection(bool $keepSessionOpens = false): void
    {
        if ($keepSessionOpens)
            trigger_error("Keeping sessions open may causes issues", E_USER_WARNING);
        ob_start();
        $this->sendHeaders();
        if ($this->dataToSend)
            echo $this->dataToSend;
        $obLength = ob_get_length();
        if ($obLength !== false)
            header('Content-Length: '. $obLength);
        http_response_code($this->httpResponseCode);
        ob_end_flush();
        flush();
        if (is_callable("fastcgi_finish_request")) {
            fastcgi_finish_request();
        }
        if (!$keepSessionOpens && session_id()) {
            session_write_close();
        }
    }
}
// example
//$clientConnection = new ConnectionToClientBuilder();
//$clientConnection->closeConnection();
