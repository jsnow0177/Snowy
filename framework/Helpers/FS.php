<?php
namespace Snowy\Helpers;

/**
 * Class FS
 * Хелпер для работы с файловой системой
 * @package Snowy\Helpers
 */
class FS{

    /**
     * Возвращает массив файлов, находящихся в директории $dir
     * @param string $dir Директория, из которой нужно получить файлы
     * @param bool $directories Если установлено в true, будет возвращён массив с индексами files и directories
     * @param bool $fullPath Если установлено в true, будут возвращены пути к этим файлам
     * @throws \InvalidArgumentException
     * @return array
     */
    public static function getFiles($dir, $directories = false, $fullPath = false){
        if(!is_dir($dir))
            throw new \InvalidArgumentException();
        $dirIterator = new \DirectoryIterator($dir);
        $files = [];
        if($directories){
            $files['directories'] = [];
            $files['files'] = [];
        }
        foreach($dirIterator as $fileInfo){
            if(!$fileInfo->isDot()){
                if($directories){
                    $files[(($fileInfo->isDir())?"directories":"files")][] = ($fullPath)?$fileInfo->getPathname():$fileInfo->getFilename();
                }else{
                    $files[] = ($fullPath)?$fileInfo->getPathname():$fileInfo->getFilename();
                }
            }
        }

        return $files;
    }

    /**
     * Возвращает массив путей к файлам, находящимся в директории $dir и вложённых директориях
     * @param string $dir Директория, из которой нужно рекурсивно получить файлы
     * @throws \InvalidArgumentException
     * @return array
     */
    public static function getFilesRecursive($dir){
        $baseDir = $dir;
        if(!is_dir($dir))
            throw new \InvalidArgumentException();

        $files = [];
        //Получаем файлы и директории для текущей директории
        $items = FS::getFiles($dir, true, true);
        $files = array_merge($files, $items['files']);
        foreach($items['directories'] as $dir){
            try{
                $items = FS::getFilesRecursive($dir);
                $files = array_merge($files, $items);
            }catch(\InvalidArgumentException $ex){
                continue;
                //TODO: Возможно, нужна обработка ошибки
            }
        }

        return $files;
    }

}
?>