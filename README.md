Here’s a user manual for each function in the PHP-based **BugScanner** tool, detailing the purpose of each function, its inputs, and expected outputs or behavior. This should help users understand how to use the script and customize it to their needs.

---

# BugScanner Tool - User Manual

This tool is a network scanner designed to perform various types of scans (direct, SSL, ping, UDP, WebSocket, etc.) on a specified list of hosts and ports. It supports different scanning methods, including basic HTTP requests, ping tests, and SSL/TLS checks.

---

## Table of Contents
1. **Classes Overview**
   - `BugScanner`
   - `DirectScanner`
2. **Functions by Class**
   - `BugScanner`
   - `DirectScanner`
3. **Helper Functions**
   - `getArguments`
   - `generateIpsFromCidr`
   - `main`
4. **Usage Instructions**
   - CLI Options and Arguments
   - Examples

---

## 1. Classes Overview

### `BugScanner` (Base Class)
The base class for various scanner types. It provides core functions, such as URL construction, error handling, and host-port conversion. Most other scanners (e.g., `DirectScanner`, `PingScanner`) inherit from this class.

### `DirectScanner` (Scanner Subclass)
Performs basic HTTP/HTTPS requests to scan for open hosts and ports. It logs results based on HTTP response codes and server headers.

---

## 2. Functions by Class

### `BugScanner` Functions

1. **`__construct($threads = 25)`**
   - **Purpose**: Initializes a scanner instance with a specified number of threads.
   - **Input**: 
     - `$threads` (integer) - Number of threads to run simultaneously. Default is 25.
   - **Output**: Initializes the `BugScanner` instance.

2. **`requestConnectionError()`**
   - **Purpose**: Stub function to simulate a connection error.
   - **Output**: Returns `1` to indicate a connection error (customizable for specific use cases).

3. **`requestReadTimeout()`**
   - **Purpose**: Stub function to simulate a read timeout.
   - **Output**: Returns `1` to indicate a read timeout (customizable for specific use cases).

4. **`requestTimeout()`**
   - **Purpose**: Stub function to simulate a generic request timeout.
   - **Output**: Returns `1` to indicate a timeout.

5. **`convertHostPort($host, $port)`**
   - **Purpose**: Formats host and port into a single string.
   - **Input**:
     - `$host` (string) - Hostname or IP address.
     - `$port` (string) - Port number.
   - **Output**: Returns a formatted string like `host:port` or `host` (if port is `80` or `443`).

6. **`getUrl($host, $port, $uri = null)`**
   - **Purpose**: Constructs a complete URL for HTTP/HTTPS requests.
   - **Input**:
     - `$host` (string) - Hostname or IP.
     - `$port` (string) - Port number.
     - `$uri` (string, optional) - Specific URI path.
   - **Output**: Returns a URL string in the form `http(s)://host:port/uri`.

7. **`init()`**
   - **Purpose**: Initializes threading and other setup tasks for scanning.
   - **Output**: Sets the default thread count if not provided.

8. **`complete()`**
   - **Purpose**: Placeholder for completion logic, e.g., cleaning up resources after a scan.
   - **Output**: None.

---

### `DirectScanner` Functions

1. **`logInfo($data)`**
   - **Purpose**: Logs HTTP response information (method, status code, server, etc.).
   - **Input**:
     - `$data` (array) - An associative array containing:
       - `method`, `status_code`, `server`, `port`, `host`, `location`.
   - **Output**: Prints a formatted log entry with color-coded status codes and server info.

2. **`getTaskList()`**
   - **Purpose**: Yields tasks by combining methods, hosts, and ports.
   - **Output**: An array with `method`, `host`, and `port` for each task in the list.

3. **`task($payload)`**
   - **Purpose**: Executes a scan request for each host, port, and method.
   - **Input**:
     - `$payload` (array) - An associative array containing `method`, `host`, and `port`.
   - **Output**: Logs HTTP response details if status code is not `302` (redirect).

4. **`request($method, $url)`**
   - **Purpose**: Sends an HTTP request to a given URL.
   - **Input**:
     - `$method` (string) - HTTP method (e.g., `GET`, `HEAD`).
     - `$url` (string) - Target URL.
   - **Output**: Returns response details, including `status_code`, `server`, `location`, etc.

---

## 3. Helper Functions

### `getArguments()`
- **Purpose**: Parses command-line arguments.
- **Output**: An associative array with command-line options (`filename`, `cdir`, `mode`, `method_list`, etc.).

### `generateIpsFromCidr($cidr)`
- **Purpose**: Generates a list of IP addresses within a specified CIDR range.
- **Input**:
  - `$cidr` (string) - A CIDR notation, e.g., `192.168.1.0/24`.
- **Output**: An array of IP addresses.

### `main()`
- **Purpose**: Initializes scanning based on command-line options and arguments.
- **Output**: Runs selected scan mode (`direct`, `ssl`, `ping`, etc.) with user-provided parameters.

---

## 4. Usage Instructions

### CLI Options and Arguments

The tool supports various command-line arguments for customization:

- **`-f, --filename`** - Path to a file with a list of hosts to scan.
- **`-c, --cdir`** - CIDR notation (e.g., `192.168.1.0/24`) to generate IPs.
- **`-m, --mode`** - Type of scan to perform (`direct`, `proxy`, `ssl`, `udp`, `ws`, `ping`).
- **`-M, --method`** - HTTP methods for the direct scanner, separated by commas.
- **`-p, --port`** - List of ports to scan, separated by commas.
- **`-P, --proxy`** - Proxy server in `host:port` format (for `proxy` mode).
- **`-o, --output`** - File to save results.
- **`-T, --threads`** - Number of concurrent threads to use.

### Examples

1. **Scan hosts in a file with HTTP methods**
   ```bash
   php script.php -f hosts.txt -m direct -M head,get -p 80,443
   ```

2. **Scan a CIDR range with SSL mode**
   ```bash
   php script.php -c 192.168.1.0/24 -m ssl
   ```

3. **Ping scan with output to a file**
   ```bash
   php script.php -f hosts.txt -m ping -o results.txt
   ```

4. **UDP scan with specific ports**
   ```bash
   php script.php -f hosts.txt -m udp -p 53,123
   ```

---

This user manual provides a comprehensive overview of each function, along with usage examples. For advanced configurations or modifications, users can refer to each function's purpose and tailor the script as needed.