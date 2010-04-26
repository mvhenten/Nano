<?php
class Nano_Ftp{
    private $_fp;

    public function __construct(){

    }

    public function connect( $host, $username, $password ){
        $fp = ftp_connect($host);


        if( $fp && ftp_login($fp, $username, $password ) ){
            $this->_fp = $fp;
            return $fp;
        }

        return false;
    }

    public function pasv(){
        ftp_pasv( $this->_fp, true );
    }

    public function quit(){
        ftp_quit( $this->_fp );
    }

    public function chDir( $path ){
        ftp_chdir( $this->_fp, ftp_pwd($this->_fp) . '/' . $path );
    }

    public function listDir(){
        if( $this->_fp == null ){
            return null;
        }

        $list = ftp_nlist( $this->_fp, '.' );

        $collect = array();

        foreach( $list as $file ){
            //echo $file;
            //$match = array();
            //preg_match('/\d\d:\d\d\s|\d\d\d\d\s(\w+)/', $file, $match );
            //var_dump( $match );
            //
            //$collect[$file] = ($this->isDir($file) ? 'directory':'file');
            //echo ftp_size( $this->_fp, $file );
            $collect[$file] = ftp_size($this->_fp, $file) == -1 ? 'dir' : 'file';
        }

        return $collect;

    }

    private function isDir( $file ){
        if( @ftp_chdir( $this->_fp, basename($file) ) ){
            ftp_chdir( $this->_fp, '..' );
            return true;
        }
        return false;
    }
}
