<?php

class Edge_Base_Helper_Image extends Mage_Core_Helper_Abstract
{
    public function getImage($file)
    {
        $mediaDir = Mage::getBaseDir('media');
        
        if (($file) && (0 !== strpos($file, '/', 0))) {
            $mediaDir = $mediaDir . '/';
        }
        
        $imageDir = $mediaDir . $file;        

        if (!file_exists($imageDir)){
            // If the file does not exist, create it from the database
            Mage::helper('core/file_storage_database')->saveFileToFilesystem($imageDir);
        }

        $mediaUrl = Mage::getBaseUrl('media');
        $imageUrl = $mediaUrl . $file;

        return $imageUrl;
    }

	/**
	 * Resizes an image. Use $isCrop to resize and crop without whitespace.
	 * Usage Mage::helper('edge/image')->resizeImage();
	 *
	 * @param string $file
	 * @param int $width Set image width
	 * @param int $height Set image height. Can be set to NULL for fixed width and flexible height keeping aspect ratio.
	 * @param boolean $isMediaGallery Is image a catalog/product image?
	 * @param boolean $isCat Is category Image
	 * @param boolean $isCrop Crops image to size, removing whitespace
	 * @param array $bg Set background colour RGB
	 * @return string
	 */
    public function resizeImage($file, $width = 100, $height = false, $isMediaGallery=false, $isCat=false, $keepFrame=true, $isCrop=false)
    {
		$imageDir = Mage::getBaseDir('media') . DS;
		$resizeDir = $imageDir . 'resized';

		if (!file_exists($resizeDir)){
			mkdir($resizeDir);
		}

        $resizePath = '_resized_' . $width . 'x' . $height . '_';

		if ($isMediaGallery){
			$imageDir .= 'catalog' . DS . 'product';
		}

		if ($isCat){
			$imageDir .= 'catalog' . DS . 'category'. DS;
		}

        $originalUrl = $imageDir . $file;

		// change folders (/) to underscore (_) - needed for product media gallery resize
		$file = str_replace('/', '_', $file);
		$resizeUrl = $resizeDir . DS . $resizePath . $file;

		if (!file_exists($resizeUrl)){
			try {
				$image = new Varien_Image($originalUrl);
				$image->quality(100);
				$image->constrainOnly(true);
				$image->keepFrame($keepFrame);

                $oldAspectRatio = $image->getOriginalWidth() / $image->getOriginalHeight();

				if ($isCrop) {
					$image->keepAspectRatio(false);

                    if(!$height) {
                        $height = $width;
                    }

                    $newAspectRatio = $width / $height;

					// If new image width is narrower that old image
					if ($newAspectRatio < $oldAspectRatio) {
						//resize filling container by height
						$tmpWidth = $height * $oldAspectRatio;
						$image->resize($tmpWidth, $height);

						// chop off the sides to fit container
						$cropHorz = ($tmpWidth - $width) / 2;
						$image->crop(0, $cropHorz, $cropHorz, 0);
					}
					// If new image width is narrower than original image size
					else {
						// resize filling container by width
						$tmpHeight = $width / $oldAspectRatio;
						$image->resize($width, $tmpHeight);

						// chop off top and bottom to fit container
						$cropVert = ($tmpHeight - $height) / 2;
						$image->crop($cropVert, 0, 0, $cropVert);
					}
				}
				else {
                    if(!$height){
                        $height = $width / $oldAspectRatio;
                    }

					$image->keepAspectRatio(true);
					$image->resize($width, $height);
				}

				$image->save($resizeUrl);

			}catch (Exception $e){
				Mage::log($e, null, 'ResizeImage.log');
			}
		}

        return Mage::getBaseUrl('media') . 'resized' . DS . $resizePath . $file;
    }
}
