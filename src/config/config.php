<?php

// 🔐 DB Config
define("DB_HOST", getenv("DB_HOST") ?: "mokkoproject.biz.id");
define("DB_NAME", getenv("DB_NAME") ?: "mokkopro_bisnis");
define("DB_USER", getenv("DB_USER") ?: "mokkopro_root");
define("DB_PASS", getenv("DB_PASS") ?: "l0IGvxx%?_fg?o1G");

// 🔐 JWT Secret (hasil generate random pakai "openssl rand -hex 64")
define("JWT_SECRET", "3e5a8838a64ea85757d53a597571b7d133c850b60ee70e815902306cef6f94945a331f1c1d69403ce7ac143c6a4f84b84b03c8421e9791acece8cfe04afa6ec6");

// 🔐 API-X-KEY (hasil generate random pakai "openssl rand -hex 64")
define("API_KEY", "1ee34e9824617bb465cc92c7ccdcdb04ad2303f16560b8ee68cf0609517cbafd51828c39a45ad57f3bb4e3532a1da6a11fc30bd571528a161d9f1b8e2bceec8d");
?>