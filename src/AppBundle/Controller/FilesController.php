<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use AppBundle\Entity\Directory;
use AppBundle\Entity\FileMetaData;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\FileParam;

class FilesController extends Controller
{

    /**
     * @Rest\View(statusCode=200)
     * @Rest\Get("/")
     */
    public function indexAction()
    {
        return array("code" => "200", "response" => "Hello! Seems to api works fine");
    }

    /**
     * @Rest\View(statusCode=200)
     * @Rest\Get("/list")
     */
    public function showlistAction()
    {

        function get_a_tree($folder, &$arr_directories, $index_dir, $previous_dir)
        {    // рекурсивно получаем имена файлов и папок из начального каталога
            $files = scandir($folder);
            foreach ($files as $file) {
                if (($file == '.') || ($file == '..')) continue;
                $f0 = $folder . DIRECTORY_SEPARATOR . $file;
                if (is_dir($f0)) {
                    $Directory = new Directory();
                    $Directory->setPath($previous_dir . DIRECTORY_SEPARATOR . $file);
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
        $folder_path = $this->get('kernel')->getRootDir() . DIRECTORY_SEPARATOR . $folder_name;

        if (!file_exists($folder_path))
            throw new NotFoundHttpException('Folder named ' . $folder_name . ' doesnt exists');

        $arr_directories = array();

        $Directory = new Directory();
        $Directory->setPath($folder_name);
        $Directory->setFiles(array());

        array_push($arr_directories, $Directory);

        get_a_tree($folder_path . DIRECTORY_SEPARATOR, $arr_directories, 0, $folder_name);

        return  array("code" => "200", "response" => $arr_directories);

    }

    /**
     * @Rest\View(statusCode=200)
     * @Rest\Get("file/{filename}/metadata")
     */

    public function showmetadataAction($filename)
    {
        $folder_name = $this->container->getParameter('folder_with_files');
        $filepath = $this->get('kernel')->getRootDir() . DIRECTORY_SEPARATOR . $folder_name . DIRECTORY_SEPARATOR . $filename;
        if (!file_exists($filepath))
            throw new NotFoundHttpException('File ' . $folder_name . '/'. $filename. ' doesnt exists');

        $FileMetaData = new FileMetaData();
        $FileMetaData->setFilename($filename);
        $FileMetaData->setBytes(filesize($filepath));
        $FileMetaData->setModified(date ("F d Y H:i:s.", filemtime($filepath)));
        $FileMetaData->setMimetype(mime_content_type($filepath));

        return array("code" => "200", "response" => $FileMetaData);
    }

    /**
     * @Rest\View(statusCode=200)
     * @Rest\Get("file/{filename}/download")
     */

    public function downloadAction($filename)
    {
        $folder_name = $this->container->getParameter('folder_with_files');
        $filepath = $this->get('kernel')->getRootDir() . DIRECTORY_SEPARATOR . $folder_name . DIRECTORY_SEPARATOR . $filename;
        if (!file_exists($filepath))
            throw new NotFoundHttpException('File ' . $folder_name . '/'. $filename. ' doesnt exists');
        $response = new BinaryFileResponse($filepath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        return $response;
    }

    /**
     * @Rest\View(statusCode=201)
     * @Rest\Post("upload")
     * @param ParamFetcher $paramFetcher
     * @FileParam(name="file", requirements={"maxSize"="2M"})
     * лимит размера файла - 2МБ
     *
     */
    public function uploadAction(ParamFetcher $paramFetcher)
    {
        $file = $paramFetcher->get('file');

 /*       if (!$file->isValid)
/            throw new ConflictHttpException("File upload failed");
*/
        $name = $file->getClientOriginalName();

        $folder_name = $this->container->getParameter('folder_with_files');
        $max_same_names = $this->container->getParameter('max_same_names');
        $folder_path = $this->get('kernel')->getRootDir() . DIRECTORY_SEPARATOR . $folder_name;

        $i = 1;
        $name_to_save = $name;

        while (file_exists($folder_path. DIRECTORY_SEPARATOR .$name_to_save) && $i < $max_same_names){
            $name_to_save = "(".$i.")".$name;
            $i = $i + 1;
        }
        if ($i == $max_same_names)
            throw new ConflictHttpException("You can save only ". $max_same_names . " files with similar name");

        $file->Move($folder_path, $name_to_save);

        $FileMetaData = new FileMetaData();
        $FileMetaData->setFilename($name_to_save);
        $FileMetaData->setBytes(filesize($folder_path . DIRECTORY_SEPARATOR . $name_to_save));
        $FileMetaData->setModified(date ("F d Y H:i:s.", filemtime($folder_path . DIRECTORY_SEPARATOR . $name_to_save)));
        $FileMetaData->setMimetype(mime_content_type($folder_path . DIRECTORY_SEPARATOR . $name_to_save));

        return array("code" => "201", "message" => "File Saved", "response" => $FileMetaData);


    }




}
