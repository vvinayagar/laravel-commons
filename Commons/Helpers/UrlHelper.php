<?php

namespace Commons\Helpers;


class UrlHelper
{
    public static function stripQueryString($url) {
        $urlInfo = parse_url($url);
        $urlNoQuery = $urlInfo['scheme'] . "://" . $urlInfo['host'] . $urlInfo['path'];
        return $urlNoQuery;
    }

    public static function getQueryString($url) {
        return parse_url($url, PHP_URL_QUERY);
    }

    public static function getQueryStringAsArray($url) {
        $queryStringData = [];
        parse_str(self::getQueryString($url), $queryStringData);
        return $queryStringData;
    }

    public static function appendQueryString($url, array $queryStringToAppend) {
        $parsedUrl = parse_url($url);

        $queryStrings = [];

        isset($parsedUrl['query']) ? parse_str($parsedUrl['query'], $queryStrings) : $queryStrings = [];
        
        $queryStrings = http_build_query(
            array_merge(
                $queryStrings,
                $queryStringToAppend
            )
        );
        
        if (isset($parsedUrl['scheme'])) {
            $scheme = $parsedUrl['scheme'];
        } else {
            $scheme = 'http';
        }
        
        $url = '';
        $url .= "$scheme://";
        if (isset($parsedUrl['host'])) {
            $url .= $host = $parsedUrl['host'];
        }
        
        $port = '';
        
        if (isset($parsedUrl['port'])) {
            if ('http' === $scheme && 80 != $parsedUrl['port']) {
                $port = ':'.$parsedUrl['port'];
            } elseif ('https' === $scheme && 443 != $parsedUrl['port']) {
                $port = ':'.$parsedUrl['port'];
            }
            $url .= $port;
        }
        
        return $url . data_get($parsedUrl, 'path') . '?' . $queryStrings;
    }
}