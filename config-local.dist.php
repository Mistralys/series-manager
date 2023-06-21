<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager;

/**
 * The password that can be used to connect to the site.
 */
const APP_PASSWORD = '';
    
/**
 * Salt for hashing the password in the session. This can be any arbitrary string.
 */
const APP_SALT = '';

/**
 * The subscriber PIN, created with a subscription to
 * TheTVDB API service.
 */
const APP_SUBSCRIBER_PIN = '';

/**
 * The API key, also created with a subscription.
 */
const APP_API_KEY = '';

/**
 * OPTIONAL
 *
 * List of folders where downloaded files for series are
 * stored. If set, this is used to determine whether an
 * episode has been downloaded.
 */
const APP_LIBRARY_PATHS = array(
    '/path/to/library'
);
