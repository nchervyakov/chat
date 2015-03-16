<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nikolay Chervyakov 
 * Date: 16.03.2015
 * Time: 16:38
 */


namespace AppBundle\Http;

/**
 * Downloads the file into given path.
 * @package AppBundle\Http
 */
class Downloader
{
    /**
     * Downloads the url into given file and returns the path to file or false in case of failure.
     * @param $url
     * @param $path
     * @param bool $rewrite
     * @return bool|string
     */
    public function download($url, $path, $rewrite = true)
    {
        if (!$rewrite && file_exists($path)) {
            return false;
        } else if (file_exists($path)) {
            unlink($path);
        }

        $fp = fopen($path, 'w');
        if ($fp === false) {
            return false;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        return realpath($path);
    }
}