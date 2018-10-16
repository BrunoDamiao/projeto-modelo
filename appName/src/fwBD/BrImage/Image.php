<?php 
namespace FwBD\BrImage;


class Image extends BaseImage
{
	
    #-- Attributes --#


    /**
     * [__construct description]
     * @param string $fileImage  [name input file]
     * @param string $dirRaiz    [name dir uploads default]
     * @param string $dirModel   [name dir model]
     * @param string $flgTmb     [0- not thumb, 1- existe thumb]
     * @param string $flgMerge   [0- not merge, 1- merge image]
     * @param string $confResize [500- resize image default]
     */
    public function __construct($fileImage='', $dirRaiz='uploads', $dirModel='', $pathMerge='', $flgTmb=1, $flgMerge=1, $resize='500', $resizeTmb='250')
    {
        
        $this->setDirRaiz($dirRaiz);
        $this->setDirModel($dirModel);

        $pathImage = (empty($pathMerge))? PATH_LOGO : $pathMerge;
        $this->setPathMerge($pathImage);

        $this->flagThumb = $flgTmb;
        $this->flagMerge = $flgMerge;
        
        if ( !empty($resize) ) {
            $this->sizeImage['cfresize'] = $resize;
            $this->sizeImage['cfresizeTmb'] = $resizeTmb;
        }

    }

    public function setFileImage($fileImage)
    {
        if ( $this->isImage($_FILES[$fileImage]) )
                return $this->setImage($_FILES[$fileImage]);

        $this->msgError = 'File empty image!';
    }
    
}