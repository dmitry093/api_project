<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use AppBundle\Entity\Directory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FilesController extends Controller
{
    /**
     * @Rest\View(statusCode=200)
     * @Rest\Get("/list")
     */
    public function listAction()
    {

        function get_a_tree($folder, &$arr_directories, $index_dir, $previous_dir)
        {    // рекурсивно получаем имена файлов и папок из начального каталога
            $files = scandir($folder);
            foreach ($files as $file) {
                if (($file == '.') || ($file == '..')) continue;
                $f0 = $folder.DIRECTORY_SEPARATOR.$file;
                if (is_dir($f0)) {
                    $Directory = new Directory();
                    $Directory->setPath($previous_dir.DIRECTORY_SEPARATOR.$file);
                    $Directory->setFiles(array());
                    array_push($arr_directories, $Directory);
                    get_a_tree($f0, $arr_directories, $index_dir + 1, $arr_directories[$index_dir]->getPath());
                } else {
                    $curr_files = $arr_directories[$index_dir]->getFiles();
                    array_push($curr_files, $file);
                    $arr_directories[$index_dir]->setFiles($curr_files);
                }
            }
        }
        $folder_name = $this->container->getParameter('folder_with_files');
        $folder_path = $this->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.$folder_name;

        if (!file_exists($folder_path))
                throw new NotFoundHttpException('Folder named '.$folder_name. ' doesnt exists');

        $arr_directories = array();

        $Directory = new Directory();
        $Directory->setPath($folder_name);
        $Directory->setFiles(array());

        array_push($arr_directories, $Directory);

        get_a_tree($folder_path.DIRECTORY_SEPARATOR, $arr_directories, 0, $folder_name);

        return array("code" => "200", "response" => $arr_directories);

    }
}
