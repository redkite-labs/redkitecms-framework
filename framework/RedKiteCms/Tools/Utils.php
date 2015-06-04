<?php
/**
 * This file is part of the RedKite CMS Application and it is distributed
 * under the GPL LICENSE Version 2.0. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) RedKite Labs <info@redkite-labs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.redkite-labs.com
 *
 * @license    GPL LICENSE Version 2.0
 *
 */

namespace RedKiteCms\Tools;

use RedKiteCms\Bridge\Translation\Translator;

/**
 * Class Utils collects several generic methods
 *
 * @author  RedKite Labs <webmaster@redkite-labs.com>
 * @package RedKiteCms\Tools
 */
class Utils
{
    /**
     * Slugifies a path
     *
     * Based on http://php.vrana.cz/vytvoreni-pratelskeho-url.php
     *
     * @param  string $text
     * @return string
     */
    public static function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

        // trim
        $text = trim($text, '-');

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    public static function translateException($message, \Exception $exception = null)
    {
        $jsonMessage = json_decode($message, true);
        if (!is_array($jsonMessage)) {
            $jsonMessage = array(
                'message' => $message,
            );
        }

        $parameters = array(
            'message' => '',
            'parameters' => array(),
            'domain' => 'RedKiteCms',
            'locale' => null,
        );
        $cleanedParameters = array_intersect_key($jsonMessage, $parameters);
        $parameters = array_merge($parameters, $cleanedParameters);

        $message = Translator::translate(
            $parameters["message"],
            $parameters["parameters"],
            $parameters["domain"],
            $parameters["locale"]
        );

        if (null !== $exception && array_key_exists("show_exception", $jsonMessage) && $jsonMessage["show_exception"]) {
            $message = substr(strrchr(get_class($exception), '\\'), 1) . ": " . $message;
        }

        return $message;
    }

    /**
     * Finds recursively differences in two arrays
     *
     * @param $aArray1
     * @param $aArray2
     *
     * @return array
     */
    public static function arrayRecursiveDiff($aArray1, $aArray2) {
        $aReturn = array();

        foreach ($aArray1 as $mKey => $mValue) {
            if (array_key_exists($mKey, $aArray2)) {
                if (is_array($mValue)) {
                    $aRecursiveDiff = self::arrayRecursiveDiff($mValue, $aArray2[$mKey]);
                    if (count($aRecursiveDiff)) { $aReturn[$mKey] = $aRecursiveDiff; }
                } else {
                    if ($mValue != $aArray2[$mKey]) {
                        $aReturn[$mKey] = $mValue;
                    }
                }
            } else {
                $aReturn[$mKey] = $mValue;
            }
        }

        return $aReturn;
    }
}