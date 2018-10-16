<?php
namespace FwBD\BrImage;



Abstract class BaseImage
{
	
	protected $dirRaiz;
	protected $dirModel;
	// protected $dirMerge=[];

	protected $flagThumb;
	protected $flagMerge;

	protected $image=[];
	protected $nameImage;
	protected $sizeImage=[];
	protected $mergeImage=[];

	protected $msgError;


	#-- Functions Abstract --#
	abstract protected function setFileImage($fileImage);
    // abstract protected function setPathImage();
    // abstract protected function setNameImage();
    // abstract protected function setSizeImage();
    // abstract protected function setMergeImage();
    
    // abstract protected function moveImage();
    // abstract protected function deleteImage();


    #-- Public --#
    public function setImage(array $image)
	{

		# Create pasta de upload padrão
		$this->createDirectory($this->dirRaiz);
		# Create pasta model se existir
		$this->createDirectory($this->dirRaiz.$this->dirModel);
		
		# Set IMAGENS na variavel $images
		$this->image = $image;
		# Set as dimensões da images
		$this->setSizeImage();
		# Set new name images
		$this->setNameImage($image['name']);
		
	}
	public function getImage()
	{
		return $this->image;
	}

	public function getMsgError()
	{
		return $this->msgError;
	}

    public function isImage($image)
	{
		$tipo = $this->mime($image['tmp_name']);
		$extimageImg = explode('/', $tipo);
		return ($extimageImg[0] == 'image')? true : false;
	}


	public function setflagMerge($flag='1')
	{
		$this->flagMerge = $flag;
	}

	public function setDirRaiz(string $dirRaiz)
	{
		$this->dirRaiz = (!empty($dirRaiz))? $dirRaiz.'/' : null;
	}
	public function getDirRaiz()
	{
		return $this->dirRaiz;
	}

	public function setDirModel(string $dirModel)
	{
		$this->dirModel = (!empty($dirModel))? $dirModel.'/' : null;
	}
	public function getDirModel()
	{
		return $this->dirModel;
	}

	public function setNameImage(string $name)
	{
		if ( empty($this->image) )
			return $this->msgError = 'File empty image!';

		$ext = str_replace('image/', '.', $this->mime($this->image['tmp_name']));
		$strName = $this->cleanString($name).'-'.date('YmdHms').$ext;

		$this->nameImage = $strName;
	}
	public function getNameImage()
	{
		return $this->nameImage;
	}

	public function setSizeImage($size='', $sizeThumb='')
	{

		if ( !empty($size) && $size !='' )
			$this->sizeImage['cfresize'] = $size;

			if ( !empty($sizeThumb) )
				$this->sizeImage['cfresizeTmb'] = $sizeThumb;
		
		if ( isset($this->image['tmp_name']) )
			$this->createResizeImage();

	}
	public function getSizeImage()
	{
		return $this->sizeImage;
	}



	public function setPathMerge($path)
	{
		
		# Valida se a logo existe e o dir é valido!
        if ( !is_file($path) && !file_exists($path) ){
        	$this->msgError = "Error, arquivo (logo/marca d'água) não existe! ".$path;
            // die("Error, arquivo (logo/marca d'água) não existe! ".$path);
        }

        // $this->flagMerge = 1;
		$this->mergeImage['pathMerge'] = $path;

	}
	public function getPathMerge()
	{
		return $this->mergeImage['pathMerge'];
	}

	public function setPathMergeThumb($pathTmb)
	{

		# Valida se a logo existe e o dir é valido!
        if ( !is_file($pathTmb) && !file_exists($pathTmb) ){
        	$this->msgError = "Error, arquivo thumb (logo/marca d'água) não existe! ".$pathTmb;
            // die("Error, arquivo thumb (logo/marca d'água) não existe! ".$pathTmb);
        }

        // $this->flagMerge = 2;
		$this->mergeImage['pathMergeThumb'] = $pathTmb;

	}
	public function getPathMergeThumb()
	{
		return $this->mergeImage['pathMergeThumb'];
	}

	public function setMergeImage($mergeTmb='0', $position='', $margin='10', $alpha='50')
	{

		if ( empty($this->image) )
			return $this->msgError = 'File empty image!';

		$this->flagMerge = 1;

		$this->setConfigMergeImage(
			['position' => $position, 'margin' => $margin, 'alpha' => $alpha]
		);

		if ( $mergeTmb == 1 ) {

			$this->flagThumb = 1;
			$this->setSizeImage();

			if ( empty($this->mergeImage['pathMergeThumb']) )
				$this->setPathMergeThumb($this->getPathMerge());

			$this->setConfigMergeThumb(
				['position' => '', 'margin' => '3', 'alpha' => '50']
			);

			$this->mergeImage['flagMergeThumb'] = 1;

		}

	}

	public function moveImage($thumb='')
	{

		if ( !empty($thumb) ) {
			$this->flagThumb = 1;
			$this->setSizeImage();
		}

		# Set Merge Image caso ainda não esteja setado!
		if ( $this->flagMerge == 1 && empty($this->mergeImage['alpha']) )
			$this->setMergeImage();

		# Criando a Image
		$this->createMoveImage();

		# Criando a Image Thumb
		if ( $this->flagThumb == 1)
			$this->createMoveImage(1);
	}


	public function deleteImage($image)
	{
		
		$dnsImage = $this->dirRaiz.$this->dirModel.$image;
		$dnsThumb = $this->dirRaiz.$this->dirModel.'thumb/'.$image;

		pp($dnsImage);

		if ( is_file($dnsImage) && file_exists($dnsImage) ) {

			if ( !	unlink($dnsImage) )
				return $this->msgError = 'Fails is delete image';

			if ( is_file($dnsThumb) && file_exists($dnsThumb) )
				if ( !unlink($dnsThumb) )
					return $this->msgError = 'Fails is delete thumb';

		}

	}

	public function deleteDirectory($directory)
	{
		if ( is_dir($directory) )
			if ( !rmdir($directory) )
				$this->msgError = 'Fails is delete dir '.$directory;
	}



	#-- Private --#
	private function createDirectory($directory)
	{
		if ( !is_dir($directory) && !file_exists($directory) )
			if ( !mkdir($directory, 0666) )
				$this->msgError = 'Fails is create dir '.$directory;
	}

	private function mime($arquivo)
    {
        if (!function_exists ('mime_content_type')){
            return (!ini_get('safe_mode')) ? trim(exec('image -bi '.escapeshellarg($arquivo))) : FALSE;
        } else {
            return mime_content_type($arquivo);
        }
    }

    private function getSizeImageDefault(string $dim)
    {	
    	if ( empty($this->image) )
			return $this->msgError = 'File empty image!';

    	# get size original
		list($w, $h) = getimagesize($this->image['tmp_name']);
		$this->sizeImage ['w']= $w;
		$this->sizeImage ['h']= $h;

		return ($dim == 'w')? $w : $h;
    }

    private function calcResize($size)
	{
		// pp($size);
		# definindo os dimensão w e h
		$w = $size;
		$h = $w;
		
		# recuperando size original da imagem
		$wsize = $this->getSizeImageDefault('w');
		$hsize = $this->getSizeImageDefault('h');
		$ratio = $wsize / $hsize;

		#calculando radio
		if ( ($w/$h) > $ratio )
            $w = ceil($h * $ratio);
        else
            $h = ceil($w / $ratio);

        # retornando array com as dimensões da image
        $resize = [ 'w' => $w, 'h' => $h ];
        return $resize;
	}

	private function createResizeImage()
	{		
		# get size original
		$w = $this->getSizeImageDefault('w');
		$h = $this->getSizeImageDefault('h');

		# get new size
		$newSize = $this->calcResize( $this->sizeImage['cfresize'] );
		$this->sizeImage['wnew'] = $newSize['w'];
		$this->sizeImage['hnew'] = $newSize['h'];

		# get thumb size
		if ( !empty($this->flagThumb) && $this->flagThumb = 1 ){
			$tmbSize = $this->calcResize( $this->sizeImage['cfresizeTmb'] );
			$this->sizeImage['wtmb'] = $tmbSize['w'];
			$this->sizeImage['htmb'] = $tmbSize['h'];
		}

	}

	private function cleanString($string)
    {
        $a = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜüÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ"!@#$%&*()_-+={[}]/?;:.,\\\'<>°ºª';
        $b = 'aaaaaaaceeeeiiiidnoooooouuuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr                                 ';
        $string = utf8_decode($string);
        $string = strtr($string, utf8_decode($a), $b);
        $string = strip_tags(trim($string));
        $string = str_replace(" ","-",$string);
        $string = str_replace(array("-----","----","---","--"),"-",$string);

        return strtolower(utf8_encode($string));
    }

    # Cria imagem de acordo com sua extensão
    private function createImages($image)
    {

        switch($this->mime($image)){
            case 'image/png': return imagecreatefrompng($image); break;
            case 'image/bmp': return imagecreatefromwbmp($image); break;
            case 'image/gif': return imagecreatefromgif($image);  break;
            case 'image/jpg': return imagecreatefromjpeg($image); break;
            case 'image/jpeg':return imagecreatefromjpeg($image); break;
        }

    }
    # Imprime a imagem final no diretorio escolhido
    private function outputImage($dist, $src, $image='', $quality='80')
    {
    	
    	$image = empty($image)? $this->image['tmp_name'] : $image;

        switch($this->mime($image)){
            case 'image/bmp': return imagewbmp($dist, $src); break;
            case 'image/gif': return imagegif($dist, $src);  break;
            case 'image/jpg': return imagejpeg($dist, $src, $quality); break;
            case 'image/jpeg':return imagejpeg($dist, $src, $quality); break;
            case 'image/png': return imagepng($dist, $src);  break;
        }

    }

    
	private function createMoveImage($flgTmb='')
	{
		if ( empty($this->image) )
			return $this->msgError = 'File empty image!';

		# recupera size original, size new e size thumb
		$w = $this->sizeImage['w'];
		$h = $this->sizeImage['h'];

		if ( $flgTmb == 1 ) {
			$new_w = $this->sizeImage['wtmb'];
			$new_h = $this->sizeImage['htmb'];
		}else{
			$new_w = $this->sizeImage['wnew'];
			$new_h = $this->sizeImage['hnew'];
		}

		# create new image
		$createImage = imagecreatetruecolor($new_w, $new_h);

		# generate new image
		$generateImage = $this->createImages($this->image['tmp_name']);
		// $generateImage = imagecreatefrompng($this->image['tmp_name']);

		# Resize: copia e redimensiona a image        
        imagecopyresampled(
        	$createImage,
        	$generateImage, 0, 0, 0, 0,
        	$new_w, $new_h, $w, $h
        );

        # add marca D'água (setMergeImage())
        if ( $this->flagMerge == 1 && $flgTmb == 1 && isset($this->mergeImage['flagMergeThumb']) ) 
        	$this->createMergeThumb($createImage);
        
        if ( $this->flagMerge == 1 && empty($flgTmb) )
        	$this->createMergeImage($createImage);

        # Output: saida da nova image criada
        if ( $flgTmb == 1 ) {
        	$directoryThumb = $this->dirRaiz.$this->dirModel.'thumb/';
        	$this->createDirectory($directoryThumb);
        }else
        	$directoryThumb = $this->dirRaiz.$this->dirModel;
        	
        $createName = $directoryThumb.$this->getNameImage();

        /*pp($directoryThumb);
        pp($createName,1);*/

        $this->outputImage($createImage, $createName);
        
        # destroy as possiveis imgs
        imagedestroy($createImage);
        imagedestroy($generateImage);

	}

	private function setConfigMergeImage(array $dataConfig)
	{
		/**
         * LISTA DE POSIÇÔES => $position
         * ET => Canto esquerdo top
         * EB => Canto esquerdo button
         * DT => Canto direito top
         * DB => Canto direito button
         * CT => Centro top
         * C  => Centro da image
         * CB => Centro button
         */

		# recuperando as dimensões da imagem de background
		$w = $this->sizeImage['wnew'];
		$h = $this->sizeImage['hnew'];

		$wImg = $w;
		$hImg = $h;

		# recuperando as dimensões da imagem marca d'água
		list($wlogo, $hlogo) = getimagesize( $this->mergeImage['pathMerge'] );
        
        # Seta os eixos x e y da logo;
        $position = $dataConfig['position'];
        $margin = $dataConfig['margin'];
        $alpha = $dataConfig['alpha'];

        /*pp("position: $position");
        pp("margin: $margin");*/

        switch ($position) {
            case 'ET': # canto esquerdo superior
                $x_logo = $margin;
                $y_logo = $margin;
                break;

            case 'EB': # canto esquerdo inferior
                $x_logo = $margin;
                $y_logo = ($hImg - $hlogo) - $margin;
                break;

            case 'DT': # canto direito superior
                $x_logo = ($wImg - $wlogo) - $margin;
                $y_logo = $margin;
                break;

            case 'DB': # canto direito inferior
                $x_logo = ($wImg - $wlogo) - $margin;
                $y_logo = ($hImg - $hlogo) - $margin;
                break;

            case 'CT': # centro superior
                $x_logo = ($wImg - $wlogo) / 2;
                $y_logo = $margin;
                break;

            case 'C': # centro
                $x_logo = ($wImg - $wlogo) / 2;
                $y_logo = ($hImg - $hlogo) / 2;
                break;

            case 'CB': # centro inferior
                $x_logo = ($wImg - $wlogo) / 2;
                $y_logo = ($hImg - $hlogo) - $margin;
                break;

            default: # centro
                $x_logo = ($wImg - $wlogo) / 2;
                $y_logo = ($hImg - $hlogo) / 2;
                break;
        }

        $this->mergeImage += [
            'xlogo' => $x_logo,
            'ylogo' => $y_logo,
            'wlogo' => $wlogo,
            'hlogo' => $hlogo,
            'alpha' => $alpha
        ];

	}

	private function setConfigMergeThumb(array $dataConfig)
	{
		# recuperando as dimensões da imagem de background
		$wImg = $this->sizeImage['wtmb'];
		$hImg = $this->sizeImage['htmb'];

		# recuperando as dimensões da imagem marca d'água
		list($wlogo, $hlogo) = getimagesize( $this->mergeImage['pathMergeThumb'] );
		
        # Seta os eixos x e y da logo;
        $position = $dataConfig['position'];
        $margin = $dataConfig['margin'];
        $alpha = $dataConfig['alpha'];

        switch ($position) {
            case 'ET': # canto esquerdo superior
                $x_logo = $margin;
                $y_logo = $margin;
                break;

            case 'EB': # canto esquerdo inferior
                $x_logo = $margin;
                $y_logo = ($hImg - $hlogo) - $margin;
                break;

            case 'DT': # canto direito superior
                $x_logo = ($wImg - $wlogo) - $margin;
                $y_logo = $margin;
                break;

            case 'DB': # canto direito inferior
                $x_logo = ($wImg - $wlogo) - $margin;
                $y_logo = ($hImg - $hlogo) - $margin;
                break;

            case 'C': # centro
                $x_logo = ($wImg - $wlogo) / 2;
                $y_logo = ($hImg - $hlogo) / 2;
                break;

            default: # centro
                $x_logo = ($wImg - $wlogo) / 2;
                $y_logo = ($hImg - $hlogo) / 2;
                break;
        }

        $this->mergeImage += [
            'Txlogo' => $x_logo,
            'Tylogo' => $y_logo,
            'Twlogo' => $wlogo,
            'Thlogo' => $hlogo,
            'Talpha' => $alpha
        ];

	}
	
	private function createMergeImage($createImage)
	{

		# Cria imagem logo, marac D'agua vinda de algum diretorio
        $createMergeImage = $this->createImages( $this->mergeImage['pathMerge'] );

        # Seta os eixos x e y da logo;
        $xlogo = $this->mergeImage['xlogo']; $ylogo = $this->mergeImage['ylogo'];
        # Seta as dimensões w e h da logo;
        $wlogo = $this->mergeImage['wlogo']; $hlogo = $this->mergeImage['hlogo'];
        # Seta % alpha cor
        $alpha = $this->mergeImage['alpha'];

        # Merge: mescla as 2 imagens
        imagecopymerge(
            $createImage,
            $createMergeImage,
            $xlogo, $ylogo, 0, 0,
            $wlogo, $hlogo, $alpha
        );

        imagedestroy($createMergeImage);

	}

	private function createMergeThumb($createImage)
	{

		# Cria imagem logo, marac D'agua vinda de algum diretorio
        $createMergeThumb=$this->createImages($this->mergeImage['pathMergeThumb']);

        # Seta os eixos x e y da logo;
        $xlogo = $this->mergeImage['Txlogo']; $ylogo = $this->mergeImage['Tylogo'];
        # Seta as dimensões w e h da logo;
        $wlogo = $this->mergeImage['Twlogo']; $hlogo = $this->mergeImage['Thlogo'];
        # Seta % alpha cor
        $alpha = $this->mergeImage['Talpha'];

        # Merge: mescla as 2 imagens
        imagecopymerge(
            $createImage,
            $createMergeThumb,
            $xlogo, $ylogo, 0, 0,
            $wlogo, $hlogo, $alpha
        );

        imagedestroy($createMergeThumb);

	}

	

}