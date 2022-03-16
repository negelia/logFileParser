<?php
/**
 * https://docstore.mik.ua/orelly/webprog/pcook/ch11_14.htm
 */
/**
 * @param string $file
 * @return mixed
 */
function logParser(string $file) {

    $result = [
        'views'    => 0,
        'urls'     => 0,
        'traffic'  => 0,
        'crawlers' => [
            'Google' => 0
        ],
        'statusCodes' => [
            '200' => 0,
            '301' => 0
        ],
    ];

    $remoteHosts = [];
    $pattern = '/^([^ ]+) ([^ ]+) ([^ ]+) (\[[^\]]+\]) "(.*) (.*) (.*)" ([0-9\-]+) ([0-9\-]+) "(.*)" "(.*)"$/';

    $openFile = fopen($file, 'r');
    if ($openFile) {
        $views = 1;
        while (!feof($openFile)) {
            $line = trim(fgets($openFile));
            if ($line) {
                $isMatched = preg_match($pattern, $line, $matches);
                if ($isMatched) {
                    list(
                        $line,
                        $remoteHost,
                        $logname,
                        $user,
                        $time,
                        $method,
                        $request,
                        $protocol,
                        $status,
                        $trafficBytes,
                        $referer,
                        $userAgent
                    ) = $matches;

                    $countTraffic = count($remoteHosts);

                    $hostSearch = array_search($remoteHost, $remoteHosts);
                    if (!$hostSearch) {
                        $remoteHosts[] = $remoteHost;
                    }
    
                    $googleSearch = stristr($userAgent, 'Googlebot');
                    if($googleSearch) {
                        $result['crawlers']['Google'] ++;
                    }

                    $code200Search = stristr($status, '200');
                    if($code200Search) {
                        $result['statusCodes']['200'] ++;
                    }

                    $code301Search = stristr($status, '301');
                    if($code301Search) {
                        $result['statusCodes']['301'] ++;
                    }

                    $result['views'] = $views;
                    $result['urls'] = $countTraffic;
                    $result['traffic'] += $trafficBytes;
                }
            }
            $views++;
        }
    }
    fclose($openFile);
    return json_encode($result, JSON_PRETTY_PRINT);
}
$file = 'access.log';

$json = logParser($file);
$filename = 'statistics.json';
$payload = file_exists($filename) ? ",{$json}]" : "[{$json}]"; 
$fileHandler = fopen($filename, "w");
fseek($fileHandler, -1, SEEK_END);
fwrite($fileHandler, $payload);
fclose($fileHandler);

echo $json;
?>