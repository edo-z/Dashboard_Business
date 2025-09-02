
<?php
// Load .env jika ada (robust: try/catch + fallback)
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
	require_once __DIR__ . '/../../vendor/autoload.php';
	if (class_exists('Dotenv\\Dotenv')) {
		try {
			$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
			// safeLoad tidak melempar jika file .env tidak ada
			$dotenv->safeLoad();
			error_log('[config] phpdotenv loaded, DB_HOST=' . var_export(getenv('DB_HOST'), true));
		} catch (Throwable $e) {
			error_log('[config] phpdotenv error: ' . $e->getMessage());
		}
	} else {
		error_log('[config] Dotenv class not found after autoload');
	}
} else {
	error_log('[config] vendor/autoload.php not found at ' . __DIR__ . '/../../vendor/autoload.php');
}

// Fallback manual: jika getenv belum terisi, coba parse .env secara manual
$envFile = __DIR__ . '/../../.env';
if ((getenv('DB_HOST') === false || getenv('DB_HOST') === null || getenv('DB_HOST') === '') && file_exists($envFile)) {
	$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	foreach ($lines as $line) {
		$line = trim($line);
		if ($line === '' || $line[0] === '#') continue;
		if (strpos($line, '=') === false) continue;
		list($k, $v) = explode('=', $line, 2);
		$k = trim($k);
		$v = trim($v);
		// remove optional surrounding quotes
		if ((strlen($v) >= 2) && (($v[0] === '"' && substr($v, -1) === '"') || ($v[0] === "'" && substr($v, -1) === "'"))) {
			$v = substr($v, 1, -1);
		}
		if (getenv($k) === false) {
			putenv("$k=$v");
			$_ENV[$k] = $v;
			$_SERVER[$k] = $v;
		}
	}
	error_log('[config] fallback .env parsed, DB_HOST=' . var_export(getenv('DB_HOST'), true));
}


// 🔐 DB Config
define("DB_HOST", getenv("DB_HOST") !== false ? getenv("DB_HOST") : "mokkoproject.biz.id");
define("DB_PORT", getenv("DB_PORT") !== false ? getenv("DB_PORT") : "3306");
define("DB_NAME", getenv("DB_NAME") !== false ? getenv("DB_NAME") : "mokkopro_bisnis");
define("DB_USER", getenv("DB_USER") !== false ? getenv("DB_USER") : "mokkopro_root");
define("DB_PASS", getenv("DB_PASS") !== false ? getenv("DB_PASS") : "l0IGvxx%?_fg?o1G");

// 🔐 JWT Secret (hasil generate random pakai "openssl rand -hex 64")
define("JWT_SECRET", "3e5a8838a64ea85757d53a597571b7d133c850b60ee70e815902306cef6f94945a331f1c1d69403ce7ac143c6a4f84b84b03c8421e9791acece8cfe04afa6ec6");

// 🔐 API-X-KEY (hasil generate random pakai "openssl rand -hex 64")
define("API_KEY", "1ee34e9824617bb465cc92c7ccdcdb04ad2303f16560b8ee68cf0609517cbafd51828c39a45ad57f3bb4e3532a1da6a11fc30bd571528a161d9f1b8e2bceec8d");
?>