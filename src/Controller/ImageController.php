<?php 
namespace Dog\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class ImageController extends AbstractActionController
{
    public function displayAction()
    {
        // Get the file name from GET variable
        $uuid = $this->params()->fromRoute('uuid', '');
        
        // Validate input parameters
        if (empty($uuid) || strlen($uuid)>36) {
            throw new \Exception('UUID is empty or too long');
        }
        $fileName = "./data/photos/$uuid.jpg";
        
        // Get image file info (size and MIME type).
        $fileInfo = $this->getImageFileInfo($fileName);
        if ($fileInfo===false) {
            // Set 404 Not Found status code
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Write HTTP headers.
        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine("Content-type: " . $fileInfo['type']);
        $headers->addHeaderLine("Content-length: " . $fileInfo['size']);
        
        // Write file content
        $fileContent = file_get_contents($fileName);
        if($fileContent!==false) {
            $response->setContent($fileContent);
        } else {
            // Set 500 Server Error status code
            $this->getResponse()->setStatusCode(500);
            return;
        }
        
        // Return Response to avoid default view rendering.
        return $this->getResponse();
    }
    
    public static function getImageFileInfo($filePath)
    {
        // Try to open file
        if (!is_readable($filePath)) {
            return false;
        }
        
        // Get file size in bytes.
        $fileSize = filesize($filePath);
        // Get MIME type of the file.
        $finfo = finfo_open(FILEINFO_MIME);
        
        $exif = exif_read_data($filePath);
        $mimeType = finfo_file($finfo, $filePath);
        if($mimeType===false)
            $mimeType = 'application/octet-stream';
            
            return [
                'size' => $fileSize,
                'type' => $mimeType,
                'exif' => $exif,
            ];
    }
}