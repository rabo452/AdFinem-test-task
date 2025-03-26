<?php 

$DB_HOST = getenv('DB_HOST');
$DB_PORT = getenv('DB_PORT');
$DB_USERNAME = getenv('DB_USERNAME');
$DB_PASSWORD = getenv('DB_PASSWORD');
$JWT_SIGN_KEY = getenv('JWT_SIGN_KEY');
$IS_DEV = (getenv('IS_DEV') ?: 'true') === 'true';