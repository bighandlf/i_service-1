<?php

class Enum_Oss {

    const OBJ_NAME_SHINE = 'iservice';

    const OSS_PATH_IMAGE = 'img';

    const OSS_PATH_VOICE = 'voice';

    public static function allowExtension($type) {
        $allowList = array();
        switch ($type) {
            case self::OSS_PATH_IMAGE:
                $allowList = array(
                    'image/bmp' => 'bmp',
                    'image/gif' => 'gif',
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png'
                );
                break;
            case self::OSS_PATH_VOICE:
                $allowList = array(
                    'audio/mpeg' => 'mp3',
                    'audio/x-wav' => 'wav',
                );
                break;
        }
        return $allowList;
    }
}

?>