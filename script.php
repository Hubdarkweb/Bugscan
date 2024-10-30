<?php

class BugScanner {
    protected $threads = 25;

    public function __construct($threads = 25) {
        $this->threads = $threads;
    }

    public function requestConnectionError() {
        return 1;
    }

    public function requestReadTimeout() {
        return 1;
    }

    public function requestTimeout() {
        return 1;
    }

    public function convertHostPort($host, $port) {
        return ($port == '80' || $port == '443') ? $host : "$host:$port";
    }

    public function getUrl($host, $port, $uri = null) {
        $protocol = $port == '443' ? 'https' : 'http';
        return "$protocol://" . $this->convertHostPort($host, $port) . ($uri ? "/$uri" : '');
    }

    public function init() {
        $this->threads = $this->threads ?? 25;
    }

    public function complete() {
        // Placeholder for completion logic
    }
}

class DirectScanner extends BugScanner {
    public $methodList = [];
    public $hostList = [];
    public $portList = [];
    private $ispRedirects = [
        "http://safaricom.zerod.live/?c=77",
        "http://91.220.208.30"
    ];

    public function logInfo($data) {
        // Adjust log information
        $data['status_code'] = $data['status_code'] ?? '';
        $data['server'] = $data['server'] ?? '';

        $location = $data['location'] ?? '';
        if ($location && !str_starts_with($location, "https://{$data['host']}")) {
            $data['host'] .= " -> $location";
        }

        $formatted = sprintf(
            "\033[36m%-6s\033[0m  \033[35m%-4s\033[0m  %-22s  \033[38;5;208m%-4s\033[0m  \033[92m%s\033[0m\n",
            $data['method'], $data['status_code'], $data['server'], $data['port'], $data['host']
        );
        echo $formatted;
    }

    public function getTaskList() {
        foreach ($this->methodList as $method) {
            foreach ($this->hostList as $host) {
                foreach ($this->portList as $port) {
                    yield [
                        'method' => strtoupper($method),
                        'host' => $host,
                        'port' => $port
                    ];
                }
            }
        }
    }

    public function task($payload) {
        $method = $payload['method'];
        $host = $payload['host'];
        $port = $payload['port'];
        $url = $this->getUrl($host, $port);

        try {
            $response = $this->request($method, $url);
            if ($response['status_code'] == 302 && in_array($response['location'], $this->ispRedirects)) {
                return;
            }

            if ($response['status_code'] && $response['status_code'] != 302) {
                $this->logInfo($response);
            }
        } catch (Exception $e) {
            // Handle error
        }
    }

    private function request($method, $url) {
        $options = [
            'http' => [
                'method' => $method,
                'timeout' => 3,
                'follow_location' => false,
            ]
        ];
        $context = stream_context_create($options);
        $content = @file_get_contents($url, false, $context);
        
        $response = [
            'status_code' => $http_response_header[0] ?? 'Error',
            'server' => $http_response_header['Server'] ?? '',
            'location' => $http_response_header['Location'] ?? '',
            'method' => $method,
            'host' => parse_url($url, PHP_URL_HOST),
            'port' => parse_url($url, PHP_URL_PORT)
        ];
        
        return $response;
    }
}

// Arguments parsing function
function getArguments() {
    $options = getopt("f:c:m:M:p:P:o:T:");
    return [
        'filename' => $options['f'] ?? null,
        'cdir' => $options['c'] ?? null,
        'mode' => $options['m'] ?? 'direct',
        'method_list' => explode(',', $options['M'] ?? 'head'),
        'port_list' => explode(',', $options['p'] ?? '80'),
        'proxy' => $options['P'] ?? '',
        'output' => $options['o'] ?? null,
        'threads' => $options['T'] ?? 25,
    ];
}

// Main function
function main() {
    $arguments = getArguments();

    if (empty($arguments['filename']) && empty($arguments['cdir'])) {
        echo "Usage: php script.php -f <filename> -c <CIDR> -m <mode>\n";
        exit();
    }

    $methodList = $arguments['method_list'];
    $hostList = [];

    if (!empty($arguments['filename'])) {
        $hostList = file($arguments['filename'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    } elseif (!empty($arguments['cdir'])) {
        $hostList = generateIpsFromCidr($arguments['cdir']);
    }

    $portList = $arguments['port_list'];
    $scanner = new DirectScanner();
    $scanner->methodList = $methodList;
    $scanner->hostList = $hostList;
    $scanner->portList = $portList;

    foreach ($scanner->getTaskList() as $task) {
        $scanner->task($task);
    }
}

function generateIpsFromCidr($cidr) {
    $hosts = [];
    try {
        $range = explode('/', $cidr);
        $ip = ip2long($range[0]);
        $mask = ~((1 << (32 - $range[1])) - 1);
        $start = ($ip & $mask) + 1;
        $end = ($ip | ~$mask) - 1;

        for ($i = $start; $i <= $end; $i++) {
            $hosts[] = long2ip($i);
        }
    } catch (Exception $e) {
        echo "Error: {$e->getMessage()}\n";
    }
    return $hosts;
}

main();
