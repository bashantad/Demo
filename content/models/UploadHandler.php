<?php

class Content_Model_UploadHandler
{

    protected $_contentFolder = array();
    protected $_packageFolder = array();
    protected $_content_id;
    protected $_package_id;
    protected $_caption;
    protected $_description;
    protected $_content_file_id;
    protected $_title = array();
    protected $options;
    protected $_id;
    protected $_timestamp;

    function __construct($options = null)
    {
        $this->_timestamp = Zend_Date::now()->toValue();
        $this->setOptions($options);
        if ($this->_content_id) {
            $script_url = '/content/index/render/';
        }
        if ($this->_package_id) {
            $script_url = '/package/index/render/';
        }

        $this->options = array(
            'script_url' => (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') .
            (isset($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'] . '@' : '') .
            (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'] .
                    (isset($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] === 443 ||
                    $_SERVER['SERVER_PORT'] === 80 ? '' : ':' . $_SERVER['SERVER_PORT']))) .
            substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/')) . $script_url,
            'upload_dir' => $this->getDirectoryUrl() . DIRECTORY_SEPARATOR . 'images',
            'upload_url' => $this->getFullUrl() . DIRECTORY_SEPARATOR . 'images',
            'param_name' => 'files',
            // Set the following option to 'POST', if your server does not support
// DELETE requests. This is a parameter sent to the client:
            'delete_type' => 'DELETE',
            // The php.ini settings upload_max_filesize and post_max_size
// take precedence over the following max_file_size setting:
            'max_file_size' => null,
            'min_file_size' => 1,
            'min_width' => 700,
            'min_height' => 333,
            'accept_file_types' => '/.+$/i',
            'max_number_of_files' => null,
            // Set the following option to false to enable resumable uploads:
            'discard_aborted_uploads' => true,
            // Set to true to rotate images based on EXIF meta data, if available:
            'orient_image' => false,
            'image_versions' => array(
// Uncomment the following version to restrict the size of
// uploaded images. You can also add additional versions with
// their own upload directories:

                '700x400' => array(
                    'upload_dir' => $this->getDirectoryUrl() . '/thumbnails/700x400/images',
                    'upload_url' => $this->getFullUrl() . '/thumbnails/700x400/images/',
                    'max_width' => 700,
                    'max_height' => 333,
                    'aspect_ratio' => '2.1:1',
                ),
                '190x105' => array(
                    'upload_dir' => $this->getDirectoryUrl() . '/thumbnails/190x105/images',
                    'upload_url' => $this->getFullUrl() . '/thumbnails/190x105/images/',
                    'max_width' => 190,
                    'max_height' => 105,
                    'aspect_ratio' => '1.8:1',
                ),
                '120x120' => array(
                    'upload_dir' => $this->getDirectoryUrl() . '/thumbnails/120x120/images',
                    'upload_url' => $this->getFullUrl() . '/thumbnails/120x120/images/',
                    'max_width' => 120,
                    'max_height' => 120,
                    'aspect_ratio' => '1:1',
                ),
                '40x40' => array(
                    'upload_dir' => $this->getDirectoryUrl() . '/thumbnails/40x40/images',
                    'upload_url' => $this->getFullUrl() . '/thumbnails/40x40/images/',
                    'max_width' => 40,
                    'max_height' => 40,
                    'aspect_ratio' => '1:1',
                ),
                'thumbnails' => array(
                    'upload_dir' => $this->getDirectoryUrl() . '/thumbnails/images',
                    'upload_url' => $this->getFullUrl() . '/thumbnails/images/',
                    'max_width' => 75,
                    'max_height' => 75,
                    'aspect_ratio' => '1:1',
                )
            )
        );
    }

    /**
     * Overloading: allow property access
     * 
     * @param  string $name 
     * @param  mixed $value 
     * @return void
     */
    public function __set($name, $value)
    {
        $method = 'set' . ucfirst($name);
        if ('mapper' == $name || !method_exists($this, $method)) {
            throw new Exception('Invalid property specified');
        }
        $this->$method($value);
    }

    /**
     * Overloading: allow property access
     * 
     * @param  string $name 
     * @return mixed
     */
    public function __get($name)
    {
        $method = 'get' . ucfirst($name);
        if ('mapper' == $name || !method_exists($this, $method)) {
            throw new Exception('Invalid property specified');
        }
        return $this->$method();
    }

    /**
     * Set object state
     * 
     * @param  array $options 
     * @return Content_Model_Content
     */
    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    public function setTitle($title)
    {
        $this->_title = $title;
        return $this;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function setDescription($description)
    {
        $this->_description = $description;
        return $this;
    }

    public function getDescription()
    {
        return $this->_description;
    }

    public function setCaption($caption)
    {
        $this->_caption = $caption;
        return $this;
    }

    public function getCaption()
    {
        return $this->_caption;
    }

    public function setPackage_id($package)
    {
        $this->_package_id = $package;
        return $this;
    }

    public function getPackage_id()
    {
        return $this->_package_id;
    }

    public function setContent_file_id($id)
    {
        $this->_content_file_id = (int) $id;
        return $this;
    }

    public function getContent_file_id()
    {
        return $this->_content_file_id;
    }

    public function setContent_id($id)
    {
        $this->_content_id = (int) $id;
        return $this;
    }

    public function getContent_id()
    {
        return $this->_content_id;
    }

    public function setContentFolder()
    {
        try {
            if (!$this->getContent_id()) {
                throw new Exception('Content ID not found.');
            }

            $model = new Content_Model_ContentMapper();
            $folders = $model->getBreadCrumb($this->getContent_id());
            $this->_contentFolder = $folders;

            return $this;
        } catch (Zend_Db_Exception $e) {
            throw new Zend_Db_Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getContentFolder()
    {
        try {
            if (empty($this->_contentFolder)) {
                $this->setContentFolder();
            }
            return $this->_contentFolder;
        } catch (Zend_Db_Exception $e) {
            
        } catch (Exception $e) {
            
        }
    }

    public function setPackageFolder()
    {
        try {
            if (!$this->getPackage_id()) {
                throw new Exception('Package ID not found.');
            }
            $model = new Package_Model_Mapper_NadPackageMst();
            $folders = $model->getPackageName($this->_package_id);
            $this->_packageFolder = $folders;
            return $this;
        } catch (Zend_Db_Exception $e) {
            throw new Zend_Db_Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getPackageFolder()
    {
        try {
            if (empty($this->_packageFolder)) {
                $this->setPackageFolder();
            }
            return $this->_packageFolder;
        } catch (Zend_Db_Exception $e) {
            
        } catch (Exception $e) {
            
        }
    }

    /**
     * @return the $_id
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param field_type $_id
     */
    public function setId($_id)
    {
        $this->_id = $_id;
    }

    protected function getDirectoryUrl()
    {
        if ($this->_content_id != null) {
            $folder = "uploads";
        } elseif ($this->_package_id != null) {
            $folder = "package";
        }
        $uploadPath = realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . DIRECTORY_SEPARATOR . $folder;

        return $uploadPath;
    }

    public function getFullUrl()
    {
        if ($this->_content_id != null) {
            $folder = "uploads";
        } elseif ($this->_package_id != null) {
            $folder = "package";
        }
        return
                (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') .
                (isset($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'] . '@' : '') .
                (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'] .
                        (isset($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] === 443 ||
                        $_SERVER['SERVER_PORT'] === 80 ? '' : ':' . $_SERVER['SERVER_PORT']))) .
                substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/')) . '/' . $folder;
    }

    /* protected function getDirectoryUrl()
      {
      if ($this->_content_id != null) {
      $folder = "uploads";
      $folders = $this->getContentFolder();
      } elseif ($this->_package_id != null) {
      $folder = "package";
      $folders[] = $this->getPackageFolder();
      }
      $uploadPath = realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . DIRECTORY_SEPARATOR .
      $folder . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR .
      implode(DIRECTORY_SEPARATOR, $folders);
      return $uploadPath;
      }

      public function getFullUrl()
      {
      if ($this->_content_id != null) {
      $folder = "uploads";
      $folders = implode('/', $this->getContentFolder());
      } elseif ($this->_package_id != null) {
      $folder = "package";
      $folders = $this->getPackageFolder();
      //$model = new Package_Model_Mapper_NadPackageMst();
      //$folders = $model->getPackageName($this->_package_id);
      }
      return
      (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') .
      (isset($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'] . '@' : '') .
      (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'] .
      (isset($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] === 443 ||
      $_SERVER['SERVER_PORT'] === 80 ? '' : ':' . $_SERVER['SERVER_PORT']))) .
      substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/')) . '/' . $folder . '/images/' . $folders;
      } */

    protected function set_file_delete_url($file)
    {
        if (!isset($file->file_name)) {
            return false;
        }
        if (!isset($file->id)) {
            return false;
        }
        if (!($file->name)) {
            return false;
        }
        if (!($file->id)) {
            return false;
        }
        $url_id = $this->_content_id ? $this->_content_id : $this->_package_id;
        $file->delete_url = $this->options['script_url'] . '?file=' . rawurlencode($file->file_name) . '&& url_id=' . $url_id . '&& id=' . rawurlencode($file->id);
        $file->delete_type = $this->options['delete_type'];
        if ($file->delete_type !== 'DELETE') {
            $file->delete_url .= '&_method=DELETE';
        }
    }

    protected function get_file_object($file_name)
    {
        $file_path = $this->options['upload_dir'] . DIRECTORY_SEPARATOR . $file_name;
        if (is_file($file_path) && $file_name[0] !== '.') {
            $file = new stdClass();
            $title = explode('.', $file_name);
            $file->renamefile = $title[0];   // Name of the image in display
            $file->name = $file_name;
            $file->file_name = $file_name;
            $file->size = filesize($file_path);
            $file->url = $this->options['upload_url'] . DIRECTORY_SEPARATOR . rawurlencode($file->name);
            foreach ($this->options['image_versions'] as $version => $options) {
                if (is_file($options['upload_dir'] . DIRECTORY_SEPARATOR . $file_name)) {
                    $file->{$version . '_url'} = $options['upload_url'] . rawurlencode($file->name);
                }
            }
// $file->title = 'adsf';
            $p = false;
            if ($this->_content_id) {
                $p = $this->getContentDbInfo($file);
            }if ($this->_package_id) {
                $p = $this->getPackageDbInfo($file);
            }
            if (!$p) {
                return null;
            }

            $this->set_file_delete_url($file);
            return $file;
        }
        return null;
    }

    protected function get_file_objects()
    {
        return array_values(array_filter(array_map(
                                        array($this, 'get_file_object'), scandir($this->options['upload_dir'])
                                )));
    }

    protected function getContentDbInfo($file)
    {
        $table = new Zend_Db_Table('nad_content_file');
        $select = $table->select()
                ->from(array('nad_content_file'), array('description', 'copyright', 'caption', 'content_file_id', 'original_file_name')
                )
                ->where('content_id = ' . $this->_content_id . ' AND file_name = "' . $file->name . ' "');
        $list = $table->fetchAll($select)->toArray();
        if (!empty($list)) {
            $file->caption = $list[0]['caption'];
            $file->description = $list[0]['description'];
            $file->id = $list[0]['content_file_id'];
            $file->copyright = $list[0]['copyright'] == "Y" ? "checked" : '';
            $file->original_file_name = $list[0]['original_file_name'];
            return $file;
        } else {
            return false;
        }
    }

    protected function getPackageDbInfo($file)
    {
        $table = new Zend_Db_Table('nad_package_media');
        $select = $table->select()
                ->from(array('nad_package_media'), array('description', 'copyright', 'caption', 'package_id', 'package_media_id', 'original_file_name')
                )
                ->where('package_id = ' . $this->_package_id . ' AND file_name = "' . $file->name . '"AND type = "IMAGE"');
        $list = $table->fetchAll($select)->toArray();

        if (!empty($list)) {
            $file->caption = $list[0]['caption'];
            $file->description = $list[0]['description'];
            $file->id = $list[0]['package_media_id'];
            $file->copyright = $list[0]['copyright'] == "Y" ? "checked" : '';
            $file->original_file_name = $list[0]['original_file_name'];
            return $file;
        } else {
            return false;
        }

//        print"<pre>";print_r($list->toArray());exit;
//        $file->description = $list[0]['description'];
//        $file->id = $list[0]['package_media_id'];
    }

    protected function create_scaled_image($file_name, $options)
    {
        $file_path = realpath($this->options['upload_dir'] . '/' . $file_name);
        $new_file_path = $options['upload_dir'];
        if (!is_dir($new_file_path)) {
            mkdir($new_file_path, 0777, true);
        }

        $new_file_path .= DIRECTORY_SEPARATOR . $file_name;
        list($img_width, $img_height) = @getimagesize($file_path);
        if (!$img_width || !$img_height) {
            return false;
        }

        $scale = min(
                $options['max_width'] / $img_width, $options['max_height'] / $img_height
        );

        if ($scale >= 1) {
            if ($file_path !== $new_file_path) {
                return copy($file_path, $new_file_path);
            }
            return true;
        }
// $new_width = $img_width * $scale;
// $new_height = $img_height * $scale;
        $new_width = $options['max_width'];
        $new_height = $options['max_height'];
        $new_img = @imagecreatetruecolor($new_width, $new_height);
        switch (strtolower(substr(strrchr($file_name, '.'), 1))) {
            case 'jpg':
            case 'jpeg':
                $src_img = @imagecreatefromjpeg($file_path);
                $write_image = 'imagejpeg';
                $image_quality = isset($options['jpeg_quality']) ?
                        $options['jpeg_quality'] : 75;
                break;
            case 'gif':
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                $src_img = @imagecreatefromgif($file_path);
                $write_image = 'imagegif';
                $image_quality = null;
                break;
            case 'png':
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                @imagealphablending($new_img, false);
                @imagesavealpha($new_img, true);
                $src_img = @imagecreatefrompng($file_path);
                $write_image = 'imagepng';
                $image_quality = isset($options['png_quality']) ?
                        $options['png_quality'] : 9;
                break;
            default:
                $src_img = null;
        }
        $success = $src_img && @imagecopyresampled(
                        $new_img, $src_img, 0, 0, 0, 0, $new_width, $new_height, $img_width, $img_height
                ) && $write_image($new_img, $new_file_path, $image_quality);

// Free up memory (imagedestroy does not delete files):
        @imagedestroy($src_img);
        @imagedestroy($new_img);
        return $success;
    }

    protected function has_error($uploaded_file, $file, $error)
    {
        if ($error) {
            return $error;
        }
        if (!preg_match($this->options['accept_file_types'], $file->name)) {
            return 'acceptFileTypes';
        }
        if ($uploaded_file && is_uploaded_file($uploaded_file)) {
            $file_size = filesize($uploaded_file);
            /*  $files = getimagesize($uploaded_file);
              $dimension = explode(" ", $files['3']);
              if (is_array($dimension)) {
              $widthData = split('=', $dimension['0']);
              $width = str_replace("\"", " ", $widthData['1']);
              $heightData = split('=', $dimension['1']);
              $height = str_replace("\"", " ", $heightData['1']);
              } */
        } else {
            $file_size = $_SERVER['CONTENT_LENGTH'];
        }
        if ($this->options['max_file_size'] && (
                $file_size > $this->options['max_file_size'] ||
                $file->size > $this->options['max_file_size'])
        ) {
            return 'maxFileSize';
        }
        if ($this->options['min_file_size'] &&
                $file_size < $this->options['min_file_size']) {
            return 'minFileSize';
        }
        if (is_int($this->options['max_number_of_files']) && (
                count($this->get_file_objects()) >= $this->options['max_number_of_files'])
        ) {
            return 'maxNumberOfFiles';
        }

        /*  if ($height != NULL && $width != NULL) {
          /*if ($height > $width) {
          return 'heightError';
          } */
        /*  if (is_int($this->options['min_height']) && ($height <= $this->options['min_height'])
          ) {
          return 'minHeight';
          }

          if (is_int($this->options['min_width']) && ($width <= $this->options['min_width'])
          ) {
          return 'minWidth';
          }
          } */
        return $error;
    }

    protected function upcount_name_callback($matches)
    {
        $index = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        $ext = isset($matches[2]) ? $matches[2] : '';
        return ' (' . $index . ')' . $ext;
    }

    protected function upcount_name($name)
    {
        return preg_replace_callback(
                        '/(?:(?: \(([\d]+)\))?(\.[^.]+))?$/', array($this, 'upcount_name_callback'), $name, 1
        );
    }

    protected function trim_file_name($name, $type)
    {
// Remove path information and dots around the filename, to prevent uploading
// into different directories or replacing hidden system files.
// Also remove control characters and spaces (\x00..\x20) around the filename:
        $file_name = trim(basename(stripslashes($name)), ".\x00..\x20");
// Add missing file extension for known image types:
        if (strpos($file_name, '.') === false &&
                preg_match('/^image\/(gif|jpe?g|png)/', $type, $matches)) {
            $file_name .= '.' . $matches[1];
        }
        if ($this->options['discard_aborted_uploads']) {
            while (is_file($this->options['upload_dir'] . $file_name)) {
                $file_name = $this->upcount_name($file_name);
            }
        }
        return $file_name;
    }

    protected function orient_image($file_path)
    {
        $exif = exif_read_data($file_path);
        $orientation = intval(@$exif['Orientation']);
        if (!in_array($orientation, array(3, 6, 8))) {
            return false;
        }
        $image = @imagecreatefromjpeg($file_path);
        switch ($orientation) {
            case 3:
                $image = @imagerotate($image, 180, 0);
                break;
            case 6:
                $image = @imagerotate($image, 270, 0);
                break;
            case 8:
                $image = @imagerotate($image, 90, 0);
                break;
            default:
                return false;
        }
        $success = imagejpeg($image, $file_path);
// Free up memory (imagedestroy does not delete files):
        @imagedestroy($image);
        return $success;
    }

    protected function handle_file_upload($uploaded_file, $name, $size, $type, $error)
    {

        if (isset($this->_content_id)) {
            $contentMapperModel = new Content_Model_ContentMapper();
            $heading = $contentMapperModel->getContentName($this->_content_id);
        } elseif (isset($this->_package_id)) {
            $packageMapperModel = new Package_Model_Mapper_NadPackageMst;
            $heading = $packageMapperModel->getPackageName($this->_package_id);
        }
        $file = new stdClass();
        $file->name = $this->trim_file_name($name, $type);
        $file->size = intval($size);
        $file->type = $type;
        $file->title = $this->_title[$file->name];
        $error = $this->has_error($uploaded_file, $file, $error);
        if (!$error && $file->name) {
            $ext = end(explode('.', $file->name));
            if ($this->_title[$file->name] != $file->name) {
                if (strpos($this->_title[$file->name], '.' . $ext)) {
                    $rename = $this->_title[$file->name];
                } else {
                    $rename = $this->_title[$file->name] . '.' . $ext;
                }
            } else {
                $date = (int) $this->_timestamp + 1;
                $rename = $heading . "_" . $date . '.' . $ext;
                $rename = urlencode($rename);
            }
            $file_path = $this->options['upload_dir'];
            if (!is_dir($file_path)) {
                mkdir($file_path, 777, true);
            }

            $file_path .= DIRECTORY_SEPARATOR . $rename;
            $file->file_name = $rename;
            $file->renamefile = $rename;
            $file->description = $this->_description[$file->name];
            $file->caption = $this->_caption[$file->name];
            $append_file = !$this->options['discard_aborted_uploads'] &&
                    is_file($file_path) && $file->size > filesize($file_path);
            clearstatcache();
            if ($uploaded_file && is_uploaded_file($uploaded_file)) {
// multipart/formdata uploads (POST method uploads)
                if ($append_file) {
                    file_put_contents(
                            $file_path, fopen($uploaded_file, 'r'), FILE_APPEND
                    );
                } else {
                    move_uploaded_file($uploaded_file, $file_path);
                }
            } else {
// Non-multipart uploads (PUT method support)
                file_put_contents(
                        $file_path, fopen('php://input', 'r'), $append_file ? FILE_APPEND : 0
                );
            }
            $file_size = filesize($file_path);
            if ($file_size === $file->size) {
                if ($this->options['orient_image']) {
                    $this->orient_image($file_path);
                }
// $file->url = $this->options['upload_url'] . rawurlencode($file->name);
                $file->url = $this->options['upload_url'] . DIRECTORY_SEPARATOR . $rename;
                if (extension_loaded('gd') && function_exists('gd_info')) {
                    foreach ($this->options['image_versions'] as $version => $options) {
// if ($this->create_scaled_image($rename, $options)) {                           
                        if ($this->image($rename, $options['aspect_ratio'], $options)) {
                            if ($this->options['upload_dir'] !== $options['upload_dir']) {
                                $file->{$version . '_url'} = $options['upload_url'] . $rename;
                            } else {
                                clearstatcache();
                                $file_size = filesize($file_path);
                            }
                        }
                    }
                }
            } else if ($this->options['discard_aborted_uploads']) {
                unlink($file_path);
                $file->error = 'abort';
            }
            $this->dbInsert($file);
            $file->size = $file_size;
            $file->name = $rename;
            $this->set_file_delete_url($file);
        } else {
            $file->error = $error;
        }
        return $file;
    }

    public function get()
    {
        $file_name = isset($_REQUEST['file']) ? basename(stripslashes($_REQUEST['file'])) : null;
        if ($file_name) {
            $info = $this->get_file_object($file_name);
        } else {
            $info = $this->get_file_objects();
        }
        header('Content-type: application/json');
        echo json_encode($info);
    }

    public function post()
    {
        if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
            return $this->delete();
        }
        $upload = isset($_FILES[$this->options['param_name']]) ? $_FILES[$this->options['param_name']] : null;
        $info = array();

        if ($upload && is_array($upload['tmp_name'])) {
            foreach ($upload['tmp_name'] as $index => $value) {
                $info[] = $this->handle_file_upload(
                        $upload['tmp_name'][$index], isset($_SERVER['HTTP_X_FILE_NAME']) ? $_SERVER['HTTP_X_FILE_NAME'] : $upload['name'][$index], isset($_SERVER['HTTP_X_FILE_SIZE']) ? $_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'][$index], isset($_SERVER['HTTP_X_FILE_TYPE']) ? $_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'][$index], $upload['error'][$index]);
            }
        } elseif ($upload || isset($_SERVER['HTTP_X_FILE_NAME'])) {
            $info[] = $this->handle_file_upload(
                    isset($upload['tmp_name']) ? $upload['tmp_name'] : null, isset($_SERVER['HTTP_X_FILE_NAME']) ?
                            $_SERVER['HTTP_X_FILE_NAME'] : (isset($upload['name']) ?
                                    isset($upload['name']) : null), isset($_SERVER['HTTP_X_FILE_SIZE']) ?
                            $_SERVER['HTTP_X_FILE_SIZE'] : (isset($upload['size']) ?
                                    isset($upload['size']) : null), isset($_SERVER['HTTP_X_FILE_TYPE']) ?
                            $_SERVER['HTTP_X_FILE_TYPE'] : (isset($upload['type']) ?
                                    isset($upload['type']) : null), isset($upload['error']) ? $upload['error'] : null);
        }


        $json = json_encode($info);

        $redirect = isset($_REQUEST['redirect']) ?
                stripslashes($_REQUEST['redirect']) : null;
        if ($redirect) {
            header('Location: ' . sprintf($redirect, rawurlencode($json)));
            return;
        }
        if (isset($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            header('Content-type: application/json');
        } else {
            header('Content-type: text/plain');
        }
        echo $json;
    }

    public function delete()
    {
        $file_name = isset($_REQUEST['file']) ? basename(stripslashes($_REQUEST['file'])) : null;
        $id = isset($_REQUEST['id']) ? basename(stripslashes($_REQUEST['id'])) : null;
        $file_path = $this->options['upload_dir'] . DIRECTORY_SEPARATOR . $file_name;
        $success = is_file($file_path) && $file_name[0] !== '.' && unlink($file_path);
        $db = Zend_Db_Table::getDefaultAdapter();
        if ($success) {
            if ($this->_content_id) {
                $where = $db->quoteInto('content_file_id = ?', $id);
                $db->delete('nad_content_file', $where);
            }
            if ($this->_package_id) {
                $where = $db->quoteInto('package_media_id = ?', $id);
                $db->delete('nad_package_media', $where);
            }
            foreach ($this->options['image_versions'] as $version => $options) {
                $file = $options['upload_dir'] . DIRECTORY_SEPARATOR . $file_name;
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        header('Content-type: application/json');
        return json_encode($success);
    }

    public function dbInsert($file)
    {
        if (!is_file($this->options['upload_dir'] . DIRECTORY_SEPARATOR . $file->file_name)) {
            return FALSE;
        }
        foreach ($this->options['image_versions'] as $version => $options) {
            $version = $options['upload_dir'] . DIRECTORY_SEPARATOR . $file->file_name;
            if (!is_file($version)) {
                return FALSE;
            }
        }

        //exit;

        /* $largeUrl = "700x400_url";
          $mediumUrl = "190x105_url";
          $smallUrl = "120x120_url";
          $tinyUrl = "40x40_url";

          if (!file_exists($file->url))
          echo $file->url . " doesn't exist";
          return false;
          if (!file_exists($file->$largeUrl))
          echo $file->$largeUrl . " doesn't exist";
          return false;
          if (!file_exists($file->$mediumUrl))
          echo $file->$mediumUrl . " doesn't exist";
          return false;
          if (!file_exists($file->$smallUrl))
          echo $file->$smallUrl . " doesn't exist";
          return false;
          if (!file_exists($file->thumbnails_url))
          echo $file->thumbnails_url . " doesn't exist";
          return false; */

        if ($this->_content_id != NULL) {
//$folders = implode('/', $this->getContentFolder());
            $info = array(
                'original_file_name' => $file->name,
                'content_file_type_id' => '1',
                'content_id' => $this->getContent_id(),
                'file_name' => $file->file_name,
                'file_size' => $file->size,
                'file_path' => '/uploads/',
                'mime_type' => $file->type,
                'status' => "D",
                'caption' => $file->caption,
                'description' => $file->description,
                'entered_dt' => date('Y-m-d'),
            );

            $objModel = new Content_Model_NadContentFileMapper();
            try {
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $objModel->getDbTable()->insert($info);
                $file->id = $db->lastInsertId();
                $file->original_file_name = $file->name;
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
        if ($this->_package_id != NULL) {
// $folders = $this->getPackageFolder();
            $info = array(
                'original_file_name' => $file->name,
                'package_id' => $this->_package_id,
                'file_name' => $file->file_name,
                'file_size' => $file->size,
                'file_path' => '/package/',
                'type' => "IMAGE",
                'status' => "D",
                'description' => $file->description,
                'caption' => $file->caption,
                'entered_dt' => date('Y-m-d H:i:s'),
            );

            $objModel = new Package_Model_Mapper_NadPackageMedia();
            try {
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $objModel->getDbTable()->insert($info);
                $file->id = $db->lastInsertId();
                $file->original_file_name = $file->name;
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
    }

    function image($file_name, $crop = null, $options = null)
    {
        $size = $options['max_width'];
        $image = imagecreatefromstring(file_get_contents(realpath($this->options['upload_dir'] . '/' . $file_name)));
        $new_file_path = $options['upload_dir'];
        if (!is_dir($new_file_path)) {
            mkdir($new_file_path, 0777, true);
        }

        $new_file_path .= DIRECTORY_SEPARATOR . $file_name;
        if (is_resource($image) === true) {
            $x = 0;
            $y = 0;
            $width = imagesx($image);
            $height = imagesy($image);

            /*
              CROP (Aspect Ratio) Section
             */

            if (is_null($crop) === true) {
                $crop = array($width, $height);
            } else {
                $crop = array_filter(explode(':', $crop));
                if (empty($crop) === true) {
                    $crop = array($width, $height);
                } else {
                    if ((empty($crop[0]) === true) || (is_numeric($crop[0]) === false)) {
                        $crop[0] = $crop[1];
                    } else if ((empty($crop[1]) === true) || (is_numeric($crop[1]) === false)) {
                        $crop[1] = $crop[0];
                    }
                }

                $ratio = array(0 => $width / $height, 1 => $crop[0] / $crop[1]);
                if ($ratio[0] > $ratio[1]) {
                    $width = $height * $ratio[1];
                    $x = (imagesx($image) - $width) / 2;
                } else if ($ratio[0] < $ratio[1]) {
                    $height = $width / $ratio[1];
                    $y = (imagesy($image) - $height) / 2;
                }
            }

            /*
              Resize Section
             */
            if (is_null($size) === true) {
                $size = array($width, $height);
            } else {
                $size = array_filter(explode('x', $size));

                if (empty($size) === true) {
                    $size = $size = array($width, $height); //array(imagesx($image), imagesy($image));
                } else {
                    if ((empty($size[0]) === true) || (is_numeric($size[0]) === false)) {
                        $size[0] = round($size[1] * $width / $height);
                    } else if ((empty($size[1]) === true) || (is_numeric($size[1]) === false)) {
                        $size[1] = round($size[0] * $height / $width);
                    }
                }
            }
            $new_img = imagecreatetruecolor($size[0], $size[1]);
            switch (strtolower(substr(strrchr($file_name, '.'), 1))) {
                case 'jpg':
                case 'jpeg':
                    $src_img = @imagecreatefromjpeg($image);
                    $write_image = 'imagejpeg';
                    $image_quality = isset($options['jpeg_quality']) ?
                            $options['jpeg_quality'] : 75;
                    break;
                case 'gif':
                    @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                    $src_img = @imagecreatefromgif($image);
                    $write_image = 'imagegif';
                    $image_quality = null;
                    break;
                case 'png':
                    @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                    @imagealphablending($new_img, false);
                    @imagesavealpha($new_img, true);
                    $src_img = @imagecreatefrompng($image);
                    $write_image = 'imagepng';
                    $image_quality = isset($options['png_quality']) ?
                            $options['png_quality'] : 9;
                    break;
                default:
                    $src_img = null;
            }
            if (is_resource($new_img) === true) {
// imagesavealpha($new_img, true);
// imagealphablending($new_img, true);
                imagefill($new_img, 0, 0, imagecolorallocate($new_img, 255, 255, 255));
                $success = $image && @imagecopyresampled($new_img, $image, 0, 0, $x, $y, $size[0], $size[1], $width, $height) && $write_image($new_img, $new_file_path, $image_quality);
                imageinterlace($new_img, true);
//imagejpeg($new_img, null, 90);
            }
        }
        @imagedestroy($image);
        @imagedestroy($new_img);
        return TRUE;
    }

}

