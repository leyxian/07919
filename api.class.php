<?php
  set_time_limit(0);
  class _MzgLock {
      var $Setting = array();
      private function Read($filename) {
          if (function_exists('file_get_contents')) {
              $filedata = @file_get_contents($filename);
          } else {
              $filedata = implode('', file($filename));
          }
          return $filedata;
      }
      private function Write($filename, $filedata) {
          if (!$fp = @fopen($filename, "w")) {
              $this->Err('Not Fp');
          }

          if (fwrite($fp, $filedata) === false) {
              $this->Err('Not Write');
          }

          fclose($fp);

          return 1;

      }
      public function Enfile($writefile = '') {

          if (!defined('MZGAPI_URL')) {
              $this->Err('Not Api_Url');
          }
          if (!defined('MZGAPI_USER')) {
              $this->Err('Not Api_User');
          }
          if (!defined('MZGAPI_KEY')) {
              $this->Err('Not Api_Key');
          }
          if (!$this->Setting['filename']) {
              $this->Err('Not File');
          }
          $typearr = explode(".", $this->Setting['filename']);
          if (!$this->Setting['fileid'] and $typearr[count($typearr) - 1] == 'zip') {
              require dirname(__file__). DIRECTORY_SEPARATOR.'./zip.class.php';
              $unzip = new SimpleUnzip($this->Setting['filename']);
              $enids = '';
              foreach ($unzip->Entries as $fd) {
                  if (substr($fd->Name, -3) == 'php') {
                      $enids .= ($enids ? ',' : '') . ($fd->Path ? $fd->Path . '/' : '') . $fd->Name;
                  }
              }
              $this->Setting['fileid'] = $enids;
          }
          $this->Setting['user'] = MZGAPI_USER;
          $this->Setting['var'] = MZGAPI_VAR;
          $this->Setting['time'] = time();
          $this->Setting['sign'] = $this->sign();
          return $this->Exec();
      }
      private function Sign() {
          $post_Setting = array('user', 'lang', 'end', 'host', 'time', 'font', 'powered',
              'siteurl', 'fileid', 'density','obfuscate', 'var');
          $str = "";
          foreach ($post_Setting as $k => $v) {

              if (!empty($this->Setting[$v]) and $this->Setting[$v] != '') {
                  $str .= $this->Setting[$v];
              }

          }
          return md5(md5($str . MZGAPI_KEY));

      }
      private function Exec() {
          if (function_exists('curl_init')) {
              $outinfo = $this->Pcurl();
          } elseif (function_exists('pfsockopen')) {
              $outinfo = $this->Pfopen();
          } else {
              $this->Err('Error:Not Link');
          }
          if (!$outinfo) {
              $this->Err('Not Data');
          }
          if (substr($outinfo, 0, 3) == '100') {

              $this->Err($outinfo);
          }
          return $outinfo;

      }
      function Pcurl() {
          $tmpfile = realpath($this->Setting['filename']);
          if (is_file($tmpfile)) {
              $this->Setting['filename'] = '@' . $tmpfile;
          } else {
              $this->Err('Not File');
          }
          $ch = curl_init(MZGAPI_URL);
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $this->Setting);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          $result = curl_exec($ch);
          curl_close($ch);
          return $result;
      }
      function Pfopen() {
          $tmpfile = realpath($this->Setting['filename']);
          if (!is_file($tmpfile)) {
              $this->Err('Not File');
          }
          unset($this->Setting['filename']);
          $post_query = '';
          foreach ($this->Setting as $key => $value) $post_query .= ($post_query ? "&" :
                  "") . $key . '=' . urlencode($value);
          $urlarr = parse_url(MZGAPI_URL);
          $host = $urlarr['host'];
          $port = ($urlarr['port'] == 80 or !$urlarr['port']) ? 80 : $urlarr['port'];
          $path = $urlarr['path'] . '?' . $post_query;
          srand((double)microtime() * 1000000);
          $boundary = "---------------------------" . substr(md5(rand(0, 32000)), 0, 10);
          $content_file = $this->read($tmpfile);
          $filearr = array();
          $filearr[] = "--$boundary\r\n";
          $filearr[] = "Content-Disposition: form-data; name=\"zipfile\"; filename=\"" .
              basename($tmpfile) . "\"\r\n";
          $filearr[] = "Content-Type: text/plain\r\n\r\n";
          $filearr[] = "$content_file\r\n";
          $filearr[] = "--$boundary--\r\n\r\n";
          $filedata = implode("", $filearr);
          $herarr = array();
          $herarr[] = "POST $path HTTP/1.0\r\n";
          $herarr[] = "Host: $host\r\n";
          $herarr[] = "Content-Type: multipart/form-data; boundary=$boundary\r\n";
          $herarr[] = "Content-Length: " . strlen($filedata) . "\r\n";
          $herarr[] = "Connection: close\r\n\r\n";
          $herdata = implode("", $herarr);
          $fp = @pfsockopen($host, $port, $errno, $errstr, 180);
          if (!$fp) die($errno . ':' . $errstr);
          fputs($fp, $herdata . $filedata);
          $result = "";
          $iswfp = false;
          while (!feof($fp)) {
              $line = fgets($fp, 512);
              if ($iswfp) {
                  $result .= $line;
              }
              if (!$iswfp and strstr("\r\n\r\n", $line)) {
                  $iswfp = true;
              }
          }
          fclose($fp);
          return $result;
      }
      private function Err($str) {
          die('Err:' . trim($str));
      }

  }
?>