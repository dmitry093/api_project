<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use AppBundle\Entity\Directory;
use AppBundle\Entity\FileMetaData;
use AppBundle\Entity\ApiResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\FileParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;


class FilesController extends Controller
{
    /**
     * @Rest\View(statusCode=200, template="response_api.html.twig")
     * @Rest\Get("/")
     * @ApiDoc(
     *     section="Others",
     *     description="Use it to get a simple response from API",
     *     output="AppBundle\Entity\ApiResponse",
     *     statusCodes={
     *         200="Returned when successful"
     *      }
     * )
     * @return ApiResponse
     */
    public function indexAction()
    {
        $response = new ApiResponse();
        $response->setCode(200);
        $response->setParameters("Hello! Seems to api is works fine");
        return $response;
    }

    /**
     * @Rest\View(statusCode=200, template="response_api.html.twig")
     * @Rest\Get("/list")
     * @ApiDoc(
     *     section="Get some information",
     *     description="Use it to show a structure of  existing directories and files",
     *     output="AppBundle\Entity\ApiResponse",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Returned when the folder is not found"
     *      }
     * )
     * @return ApiResponse
     */
    public function showlistAction()
    {

        function get_a_tree($folder, &$arr_directories, $index_dir, $previous_dir)
        {    // рекурсивно получаем имена файлов и папок из начального каталога
            $files = scandir($folder);
            foreach ($files as $file) {
                if (($file == '.') || ($file == '..')) continue;
                $file =  mb_convert_encoding($file, "UTF-8", "auto"); // винда + кириллица в названии папок и файлов
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

        $response = new ApiResponse();
        $response->setCode(200);
        $response->setParameters($arr_directories);
        return $response;
    }

    /**
     * @Rest\View(statusCode=200, template="response_api.html.twig")
     * @Rest\Get("file/{filename}/metadata")
     * @ApiDoc(
     *     section="Get some information",
     *     description="Use it to get metadata for some file",
     *     requirements={
     *          {
     *              "name" = "filename",
     *              "dataType" = "string",
     *              "description" = "Filename on the server"
     *          }
     *     },
     *     output={
     *     "class"="AppBundle\Entity\FileMetaData",
     *     "name"="parameters"
     *  },
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Returned when the file with such name doesnt found on the server"
     *      }
     * )
     * @return ApiResponse
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

        $response = new ApiResponse();
        $response->setCode(200);
        $response->setParameters($FileMetaData);
        return $response;
    }

    /**
     * @Rest\View(statusCode=200, template="response_api.html.twig")
     * @Rest\Get("file/{filename}/download")
     * @ApiDoc(
     *     section="Download/Upload",
     *     description="Use it to download some file",
     *     requirements={
     *          {
     *              "name" = "filename",
     *              "dataType" = "string",
     *              "description" = "Filename on the server"
     *          }
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Returned when the file with such name doesnt found on the server"
     *      }
     * )
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
     * @Rest\View(statusCode=201, template="response_api.html.twig")
     * @Rest\Post("upload")
     * @param ParamFetcher $paramFetcher
     * @FileParam(name="file", requirements={"maxSize"="2M"})
     * лимит размера файла - 2МБ
     ** @ApiDoc(
     *     section="Download/Upload",
     *     description="Use it to upload some file",
     *     requirements={
     *          {
     *              "name" = "file",
     *              "dataType" = "File",
     *              "description" = "Upload File"
     *          }
     *     },
     *     output={
     *          "class"="AppBundle\Entity\FileMetaData",
     *          "name"="parameters"
     *     },
     *     statusCodes={
     *         201="Returned when successful",
     *         409="Returned when the server has more than maximum of files with similar names"
     *      }
     * )
     * @return ApiResponse
     */
    public function uploadAction(ParamFetcher $paramFetcher)
    {
        $file = $paramFetcher->get('file');

         $name = $file->getClientOriginalName();

        $folder_name = $this->container->getParameter('folder_with_files');
        $max_same_names = $this->container->getParameter('max_same_names');
        $folder_path = $this->get('kernel')->getRootDir() . DIRECTORY_SEPARATOR . $folder_name;

        $i = 1;
        $name_to_save = $name;

        while (file_exists($folder_path. DIRECTORY_SEPARATOR .$name_to_save) && $i <= $max_same_names){
            $name_to_save = "(".$i.")".$name;
            $i = $i + 1;
        }
        if ($i >= $max_same_names)
            throw new ConflictHttpException("You can save only ". $max_same_names . " files with similar name");

        $file->Move($folder_path, $name_to_save);

        $FileMetaData = new FileMetaData();
        $FileMetaData->setFilename($name_to_save);
        $FileMetaData->setBytes(filesize($folder_path . DIRECTORY_SEPARATOR . $name_to_save));
        $FileMetaData->setModified(date ("F d Y H:i:s.", filemtime($folder_path . DIRECTORY_SEPARATOR . $name_to_save)));
        $FileMetaData->setMimetype(mime_content_type($folder_path . DIRECTORY_SEPARATOR . $name_to_save));

        $response = new ApiResponse();
        $response->setCode(201);
        $response->setParameters($FileMetaData);
        return $response;
    }

    /**
     * @Rest\View(statusCode=201, template="response_api.html.twig")
     * @Rest\Post("replace")
     * @param ParamFetcher $paramFetcher
     * @QueryParam(name="replace_it", default = "", description = "Filename on the server to replace")
     * @FileParam(name="file", requirements={"maxSize"="2M"})
     * лимит размера файла - 2МБ
     *
     * @ApiDoc(
     *     section="Download/Upload",
     *     description="Use it to replace some file",
     *     requirements={
     *          {
     *              "name" = "file",
     *              "dataType" = "File",
     *              "description" = "Upload File"
     *          },
     *
     *     },
     *     output={
     *          "class"="AppBundle\Entity\FileMetaData",
     *          "name"="parameters"
     *     },
     *     statusCodes={
     *         201="Returned when successful",
     *         409="Returned when the server has more than maximum of files with similar names"
     *      }
     * )
     * @return ApiResponse
     */
    public function replaceAction(ParamFetcher $paramFetcher)
    {

        $file = $paramFetcher->get('file');
        $replace_it = $paramFetcher->get('replace_it');
        $name = $file->getClientOriginalName();


        $folder_name = $this->container->getParameter('folder_with_files');
        $folder_path = $this->get('kernel')->getRootDir() . DIRECTORY_SEPARATOR . $folder_name;


        if (empty($replace_it))
            $name_to_save = $name;
        else {
            if (!file_exists($folder_path . DIRECTORY_SEPARATOR .$replace_it))
                throw new NotFoundHttpException('File ' . $folder_path . '/'. $replace_it. ' doesnt exists');
            $name_to_save = $replace_it;
        }

        $file->Move($folder_path, $name_to_save);

        $FileMetaData = new FileMetaData();
        $FileMetaData->setFilename($name_to_save);
        $FileMetaData->setBytes(filesize($folder_path . DIRECTORY_SEPARATOR . $name_to_save));
        $FileMetaData->setModified(date ("F d Y H:i:s.", filemtime($folder_path . DIRECTORY_SEPARATOR . $name_to_save)));
        $FileMetaData->setMimetype(mime_content_type($folder_path . DIRECTORY_SEPARATOR . $name_to_save));

        $response = new ApiResponse();
        $response->setCode(201);
        $response->setParameters($FileMetaData);
        return $response;

    }


}
