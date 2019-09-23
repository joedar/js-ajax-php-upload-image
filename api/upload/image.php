<?php
header("Content-type: text/html; charset=utf-8");
include '../RETURN.DATA.php';

/**
 * 在上传图片之前，应该有后端判断是否用户有权限来操作
 * 否则，任何人都可以来上传
 * 权限比如：是否登录
 * 此处只是简单实现图片的上传，所以判断就不写了...
 */


/**
 * var_dump($_REQUEST);
 * array(2) {
 *   ["path"]=> string(7) "article"
 *   ["size"]=> string(7) "5242880"
 * }
 * 表明参数已经传给了后端
 * 
 * var_dump($_FILES);
 * 我选择了两张图片，打印出如下：
 * array(2) {
 *   ["upload_file_0"]=> array(5) {
 *     ["name"]=> string(27) "tooopen_sy_181506922838.jpg"
 *     ["type"]=> string(10) "image/jpeg"
 *     ["tmp_name"]=> string(22) "C:\Windows\php48DC.tmp"
 *     ["error"]=> int(0)
 *     ["size"]=> int(184253)
 *   }
 *   ["upload_file_1"]=> array(5) {
 *     ["name"]=> string(34) "tooopen_sy_181506922839.jpg"
 *     ["type"]=> string(10) "image/jpeg"
 *     ["tmp_name"]=> string(22) "C:\Windows\php48ED.tmp"
 *     ["error"]=> int(0)
 *     ["size"]=> int(75961)
 *   }
 * }
 * 表明图片文件已经传给了后端
 */

function UPLOADIMAGE() {
  // 获取传过来的path和size
  $path = !empty($_REQUEST['path']) ? $_REQUEST['path'] : 'temp';
  $size = !empty($_REQUEST['size']) ? (int)$_REQUEST['size'] : 5 * 1024 * 1024;
  // 上传图片绝对路径
  $dir = '/UPLOAD/images/' . $path . '/';
  $ROOT = $_SERVER['DOCUMENT_ROOT'] . $dir;

  //-----------------------------------------
  // 定义自增长变量
  // 批量上传的时候，不至于因为同名而只上传一张
  //-----------------------------------------
  $index = 0;

  // 用来存放图片列表与错误提示列表
  $imageArr = array();
  $errorArr = array();

  // 循环遍历数据
  foreach ($_FILES as $key => $file) {
    //---------------------
    // 获取文件扩展名
    // 并随机生成文件名
    //---------------------
    $type = '';
    if (preg_match('/image\/jpeg/', $file['type'])) { $type = '.jpg'; }
    elseif (preg_match('/image\/gif/', $file['type'])) { $type = '.gif'; }
    elseif (preg_match('/image\/png/', $file['type'])) { $type = '.png'; }
    // 随机数
    $randomStr = substr(md5(time()), 0, 8) . '!!' . $index . time();
    $filename = $randomStr . $type;
    // 转码 把utf-8转成gb2312,返回转换后的字符串，或者在失败时返回FALSE
    $gb_filename = iconv('utf-8', 'gb2312', $filename);

    // 文件不存在才上传
    if (!file_exists($ROOT.$gb_filename)) {
      // 默认上传失败
      $isMoved = false;

      // 限定图片类型
      $type_is_true = preg_match('/image\/(jpeg|gif|png)/', $file['type']);

      // 如果图片类型错误
      if (!$type_is_true) { $errorArr[] = $file['name'] . ' is wrong type'; }
      // 如果图片大小错误
      elseif ($file['size'] > $size) { $errorArr[] = $file['name'] . ' is exceed 5M'; }
      // 否则
      else {
        $isMoved = @move_uploaded_file($file['tmp_name'], $ROOT.$gb_filename);
        $imageArr[] = $dir . $gb_filename;
      }

    } else {
      // 已存在文件设置为上传成功
      $isMoved = true;
    }

    $index++;
  }

  // 定义返回给前端的数据
  $data = array('images' => $imageArr, 'errors' => $errorArr);

  // 如果有上传成功的图片
  if (count($imageArr)) { RETURNDATA(array('success' => true, 'data' => $data)); }
  else { RETURNDATA(array('success' => false, 'msg' => 'Images are all inconsistent with the rules')); }
}
UPLOADIMAGE();

?>
