<?php
class AmazonFeeds
{
    private static $path = "../../downloads/";
    private static $file_API_amazon;   //singolo file
    private static $username = 'hdblit-21';
    private static $password = 'J-!35XN^f$bCH%k#';
    public static $options = array(

        CURLOPT_AUTOREFERER => true,
        CURLOPT_COOKIEFILE => '',
        CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_VERBOSE => 1
    );

    public static function fetchContent($url)
    {
        if ( ($curl = curl_init($url)) === false ) {
            throw new \RuntimeException("curl_init error for url $url.");
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, array(

            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36',
            'Sec-Fetch-Dest: document'));
        curl_setopt_array($curl, self::$options);
        curl_setopt($curl, CURLOPT_USERPWD, self::$username . ":" . self::$password);

        $content = curl_exec($curl);
        if ( $content === false ) {
            throw new Exception("curl_exec error for url $url.");
        }
        curl_close($curl);


        $content = preg_replace('#\n+#', ' ', $content);
        $content = preg_replace('#\s+#', ' ', $content);

        return $content;
    }

    public static function getItems($url)
    {
        try {
            $content = self::fetchContent($url);
        } catch (Exception $e) {
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($content);
        libxml_clear_errors();
        $xpath = new DOMXpath($dom);

        foreach ($xpath->query("//tr") as $element) {

            $els = $element->getElementsByTagName('td');

            if ( is_object($els->item(0)) ) {

                self::$file_API_amazon['name'] = $els->item(0)->nodeValue;
            }
            if ( is_object($els->item(1)) ) {

                self::$file_API_amazon['date'] = strtotime($els->item(1)->nodeValue);
            }
            if ( is_object($els->item(2)) ) {
                self::$file_API_amazon['code'] = str_replace("\"", "", $els->item(2)->nodeValue);

            }
            if ( is_object($els->item(3)) ) {
                self::$file_API_amazon['size'] = $els->item(3)->nodeValue;

            }
            if ( is_object($els->item(4)) ) {
                self::$file_API_amazon['link'] = "https://assoc-datafeeds-eu.amazon.com/datafeed/" . ($els->item(4)->firstChild->getAttribute('href'));

            }

            if ( self::$file_API_amazon ) {


                $riga[] = self::$file_API_amazon;
            }
        }
        return $riga;

    }


    public static function downloadFile($file_id, $file_size, $file_link, $file_cursor) // FUNZIONA!!!!!!!!!
    {


        $cursor = (int) $file_cursor;

        if ( $cursor ) {
            $init = $cursor + 1;
        } else $init = 0;
        $end = (($file_size - $cursor) > 499999) ? ($init + 499999) : ($file_size);
        $range = "$init-$end";
        $fileName = self::$path . $file_id . basename($file_link);
        if ( ($curl = curl_init($file_link)) === false ) {
            throw new Exception("curl_init error for url $file_link.");
        }
        curl_setopt_array($curl, self::$options);
        curl_setopt($curl, CURLOPT_USERPWD, self::$username . ":" . self::$password);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);//non cambia nulla
        curl_setopt($curl, CURLOPT_RANGE, $range);

        if ( ($fp = fopen($fileName, "ab")) === false ) {
            throw new Exception("fopen error for filename $fileName");
        }
        curl_setopt($curl, CURLOPT_FILE, $fp);

        echo "$init : $end of $file_size\n";

        if ( curl_exec($curl) === false ) {
            fclose($fp);
            unlink($fileName);
            throw new Exception("curl_exec error for url $file_link.");

        } else {
            fclose($fp);
        }
        curl_close($curl);
        return $end;
    }


}