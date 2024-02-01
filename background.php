<?php
class CloseConnectionBuilder
{
    protected ?string $dataToSend = null;

    protected ?string $contentType = null;

    protected int $httpResponseCode = 200;

    protected bool $closeConnection = true;

    protected string $contentEncoding = "none";

    public function __construct()
    {
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
    public function setContentEncoding(string $contentEncoding): self
    {
        $this->contentEncoding = $contentEncoding;
        return $this;
    }
    protected function sendConnectionHeader(): void
    {
        $allowedHttpVersion = [
            "HTTP/1.0",
            "HTTP/1.1"
        ];

        if (!$this->closeConnection)
            return;
        if (isset($_SERVER["SERVER_PROTOCOL"]) && in_array($_SERVER["SERVER_PROTOCOL"], $allowedHttpVersion))
            header('Connection: close');
    }
    protected function sendContentEncodingHeader(): void
    {
        if ($this->dataToSend) {

            // Leave the server managing content encoding
            if ($this->contentEncoding === "none")
                return;

            trigger_error("Content-Encoding header and data are set. This is not secure, 
            you should leave the server managing Content Encoding.", E_USER_WARNING);
        } else if ($this->contentEncoding !== "none") {
            trigger_error("Content-Encoding header is set but there's no data to send!", E_USER_WARNING);
        }

        header("Content-Encoding: {$this->contentEncoding}");
    }
    protected function sendContentTypeHeader(): void
    {
        if (!$this->contentType) {
            if ($this->dataToSend)
                trigger_error("Content-Type header is not set but there's data to send!", E_USER_NOTICE);
            else
                header_remove("Content-Type");
        } else if (!$this->dataToSend) {
            trigger_error("Content-Type header is set but there's no data to send!", E_USER_WARNING);
        }
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
            header('Content-Length: ' . $obLength);
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
