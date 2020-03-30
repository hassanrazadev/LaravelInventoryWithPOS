<?php


namespace App\Utils;


class AppUtils {

    /**
     * @param $errors
     * @param $fieldName
     * @return string
     */
    public static function inputFieldError($errors, $fieldName){
        if ($errors->has($fieldName)){
            return 'is-invalid';
        }
        return '';
    }

    /**
     * @param $errors
     * @param $fieldName
     * @return string
     */
    public static function formGroupError($errors, $fieldName){
        if ($errors->has($fieldName)){
            return 'has-error';
        }
        return '';
    }

    /**
     * @param $image
     * @return bool|mixed|string
     */
    public static function base64ToImage($image){
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $image = base64_decode($image);
        return $image;
    }

    /**
     * @param $base64
     * @return bool
     */
    public static function isImage($base64){
        $img = imagecreatefromstring(base64_decode($base64));
        imagepng($img, 'test_image.png');
        $data = getimagesize('test_image.png');
        if ($data['mime'] == "image/png"){
            return true;
        }

        return false;
    }
}