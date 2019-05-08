<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 13. 3. 2019
 * Time: 14:29
 */

namespace Asyf\ApiBundle\Utils;

class Utils
{
    /**
     * @param array $arrays
     * @param bool $createKeys
     * @param array $bannedKeysFromCreating
     *
     * @return array
     */
    public static function mergeArrays(array $arrays, bool $createKeys = true, array $bannedKeysFromCreating = []): array
    {
        if (count($arrays) === 1) {
            return $arrays[0] ?: [];
        }

        $outputArray = [];
        foreach ($arrays as $array) {
            if (is_iterable($array)) {
                if (count($outputArray) === 0) {
                    $outputArray = $array;
                } else {
                    foreach ($array as $key => $value) {
                        if (is_array($value)) {
                            if (isset($outputArray[$key])) {
                                $outputArray[$key] = self::mergeArrays([$outputArray[$key], $value], !in_array($key, $bannedKeysFromCreating), $bannedKeysFromCreating);
                            } else {
                                if ($createKeys) {
                                    $outputArray[$key] = self::mergeArrays([[], $value], !in_array($key, $bannedKeysFromCreating), $bannedKeysFromCreating);
                                }
                            }
                        } else {
                            if ($createKeys) {
                                $outputArray[$key] = $value;
                            }
                        }
                    }
                }
            }
        }
        return $outputArray;
    }
}