<?php


namespace ridvanaltun\JsonPatchGenerator;

class Utils
{
    function createPatch(string $op, string $path, $value = null) {
        $patch = [
            'op'   => $op,
            'path' => $path,
        ];

        if (isset($value)) {
            $patch['value'] = $value;
        }

        return $patch;
    }

    function isArraySequential(array $arr) {
        if(array_keys($arr) !== range(0, count($arr) - 1)) {
            // associative
            return false;
        } else {
            // sequential
            return true;
        }
    }

    function getValueByPath($data, $path) {
        $path = substr($path, 1); // remove first character, means '/' sign removes
        $temp = $data;
        foreach(explode("/", $path) as $ndx) {
            $temp = isset($temp[$ndx]) ? $temp[$ndx] : null;
        }
        return $temp;
    }

    function getAddAndReplacePatches(array $currSnap, array $oldSnap, string $path = '/', bool $isSequentialArr = false, array $iterationData = null) {
        $patches = [];
        $iterationData = isset($iterationData) ? $iterationData : $currSnap;
        foreach ($iterationData as $k => $v) {
            if (!is_array($v)) {

                // set path
                $fullpath = $isSequentialArr ? $path : $path.$k;

                // this is for get value by path
                $targetPath = $isSequentialArr ? $fullpath.'/'.$k : $fullpath;

                $isValueAdded = false;
                $isValueReplaced = false;
                $notChanged = false;

                // handle sequential array
                if ($isSequentialArr) {
                    // is value added
                    $seqVal = $this->getValueByPath($oldSnap, $targetPath);
                    $currArr = $this->getValueByPath($currSnap, $fullpath);
                    $isValueAdded = !in_array($seqVal, $currArr);

                    // is value replaced
                    $isValueReplaced = in_array($seqVal, $oldSnap);
                } else {
                    $oldVal = $this->getValueByPath($oldSnap, $targetPath);
                    $currVal = $this->getValueByPath($currSnap, $targetPath);

                    // detect null and not changed values
                    $notChanged = $oldVal === null && $currVal === null;

                    // is value added
                    $isValueAdded = $this->getValueByPath($oldSnap, $targetPath) === null;

                    // is value replaced
                    $isValueReplaced = !$isValueAdded && ($oldVal !== $currVal) && $currVal !== null;
                }

                if (!$notChanged) {
                    if ($isValueAdded) $patches[] = $this->createPatch('add', $fullpath, $v);
                    if ($isValueReplaced) $patches[] = $this->createPatch('replace', $fullpath, $v);
                }
            }
            else {
                if ($this->isArraySequential($v)) {
                    // sequential
                    $jsonPatch2 = $this->getAddAndReplacePatches($currSnap, $oldSnap, $path.$k, true, $v);
                    $patches = array_merge($patches, $jsonPatch2);
                } else {
                    // associative
                    $jsonPatch2 = $this->getAddAndReplacePatches($currSnap, $oldSnap, $path.$k.'/', false, $v);
                    $patches = array_merge($patches, $jsonPatch2);
                }
            }
        }
        return $patches;
    }

    function getRemovedPatches(array $currSnap, array $oldSnap, string $path = '/', bool $isSequentialArr = false, array $iterationData = null) {
        $patches = [];
        $iterationData = isset($iterationData) ? $iterationData : $oldSnap;
        foreach ($iterationData as $k => $v) {
            if (!is_array($v)) {

                // set path
                $fullpath = $isSequentialArr ? $path : $path.$k;

                // this is for get value by path
                $targetPath = $isSequentialArr ? $fullpath.'/'.$k : $fullpath;

                $isValueDeleted = false;
                $notChanged = false;

                // handle sequential array
                if ($isSequentialArr) {
                    // is value deleted
                    $seqVal = $this->getValueByPath($currSnap, $targetPath);
                    $oldArr = $this->getValueByPath($oldSnap, $fullpath);
                    $isValueDeleted = !in_array($seqVal, $oldArr);

                    if ($isValueDeleted) $patches[] = $this->createPatch('remove', $fullpath, $v);
                } else {
                    $oldVal = $this->getValueByPath($oldSnap, $targetPath);
                    $currVal = $this->getValueByPath($currSnap, $targetPath);

                    // detect null and not changed values
                    $notChanged = $oldVal === null && $currVal === null;

                    // is value deleted
                    $isValueDeleted = $this->getValueByPath($currSnap, $targetPath) === null;

                    if (!$notChanged) {
                        if ($isValueDeleted) $patches[] = $this->createPatch('remove', $fullpath);
                    }
                }
            }
            else {
                if ($this->isArraySequential($v)) {
                    // sequential
                    $jsonPatch2 = $this->getRemovedPatches($currSnap, $oldSnap, $path.$k, true, $v);
                    $patches = array_merge($patches, $jsonPatch2);
                } else {
                    // associative
                    $jsonPatch2 = $this->getRemovedPatches($currSnap, $oldSnap, $path.$k.'/', false, $v);
                    $patches = array_merge($patches, $jsonPatch2);
                }
            }
        }
        return $patches;
    }

    public function takeSnapshot($classObject) {
        return get_object_vars($classObject);
    }

    public function generateJsonPatch(array $currSnap, array $oldSnap) {
        $addedAndReplacedPatches = $this->getAddAndReplacePatches($currSnap, $oldSnap);
        $removedPatches = $this->getRemovedPatches($currSnap, $oldSnap);

        return array_merge($addedAndReplacedPatches, $removedPatches);
    }
}