<?php
/**
 * Class stub, mainly here to implement the 'orientation' feature.
 *
 * Reads an image from path, and attempts to parse it's exif data.
 * Since it's somehow hard to determine the exact array key of certain data
 * points, we just loop over the data to try and find them.
 *
 */
class Nano_Exif {
    private $_processed_data;
    private $_exif_data;

    public function __construct( $path ){
        $this->_path = $path;
    }

    public function __get( $name ){
        if( method_exists( $this, $name ) ){
            return $this->$name();
        }
    }

    /**
     * Retrieves the image orientation, as saved by the camera.
     */
    public function orientation(){
        if( ! isset( $this->_processed_data['orientation'] ) ){
            $this->_processed_data['orientation'] = $this->_searchForKey('orientation');
        }

        return $this->_processed_data['orientation'];
    }

    private function _searchForKey( $search_key ){
        $data = (array) $this->_getExifData();
        $keys = array_keys($data);

        while( $data ){
            $key   = key($data);
            $value = array_shift($data);

            if( strtolower($key) == $search_key ){
                return $value;
            }

            if( is_array($value) ){
                $data = array_merge( $data, $value );
            }
        }
    }

    private function _getExifData(){
        if( null == $this->_exif_data
           && file_exists( $this->_path )
           && function_exists('exif_read_data') ){
            $this->_exif_data = exif_read_data( $this->_path );
        }

        return (array) $this->_exif_data;
    }

}
