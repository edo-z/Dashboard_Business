<?php
function callAPI($method, $url, $data = false) {
    $curl = curl_init();

    $headers = [
        "X-API-KEY: 1ee34e9824617bb465cc92c7ccdcdb04ad2303f16560b8ee68cf0609517cbafd51828c39a45ad57f3bb4e3532a1da6a11fc30bd571528a161d9f1b8e2bceec8d"
    ];

    if (!empty($_SESSION['jwt'])) {
        $headers[] = "Authorization: Bearer " . $_SESSION['jwt'];
    }

    // --- Try JSON first ---
    $jsonData = $data ? json_encode($data) : null;
    $headersJson = array_merge($headers, ["Content-Type: application/json"]);

    curl_setopt_array($curl, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headersJson,
    ]);

    switch ($method) {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
            break;
        case "PUT":
        case "DELETE":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
            break;
        default: // GET
            if ($data) $url = sprintf("%s?%s", $url, http_build_query($data));
            curl_setopt($curl, CURLOPT_URL, $url);
    }

    $result = curl_exec($curl);
    curl_close($curl);

    $response = json_decode($result, true);

    // --- Fallback: try x-www-form-urlencoded if JSON failed or incomplete data ---
    if (!$response || (isset($response['error']) && stripos($response['error'], "Data tidak lengkap") !== false)) {
        $curl = curl_init();
        $headersForm = array_merge($headers, ["Content-Type: application/x-www-form-urlencoded"]);

        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headersForm,
        ]);

        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                break;
            case "PUT":
            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
                if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                break;
            default: // GET
                if ($data) $url = sprintf("%s?%s", $url, http_build_query($data));
                curl_setopt($curl, CURLOPT_URL, $url);
        }

        $result = curl_exec($curl);
        curl_close($curl);

        $response = json_decode($result, true);
    }

    return $response;
}
