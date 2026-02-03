<?php

namespace Karolina\Content;

use Respect\Validation\Validator as v;
use \Karolina\Database\Table\Image as Model;



Class AttachmentFileRepository {

	private $s3;

	public function __construct ($s3) {

		$this->s3 = $s3;

	}

	private function getUniqueName ($extension) {

		$uuid = new \Karolina\UUID();
		$randomString = bin2hex(random_bytes(16));

		$uniqueFilename = $uuid->getString().'-'.$randomString.'.'.$extension;
		return $uniqueFilename;

	}

	private function isExtensionAllowed ($extension) {

		$allowedExtensions = array('pdf');

		if (in_array($extension, $allowedExtensions)) {

			return true;

		} else {
		
			$extension = preg_replace("/[^a-z0-9.]+/i", "", $extension);
			throw new \Karolina\KarolinaException('File extension: '.$extension.' not allowed. Try something else.', 400, null, 'invalid_file');
		}

	}

	private function isNotExecutable ($file) {

		if (!v::executable()->validate($file)) {

			return true;

		} else {

			throw new \Karolina\KarolinaException('File appears to be an executable, and therefore not allowed', 400, null, 'invalid_file');
		}

	}

	private function isDestionationFolderAllowed ($destionationFolder) {

		if (v::alnum('-_./')->noWhitespace()->validate($destionationFolder)) {

			return true;

		} else {

			throw new \Karolina\KarolinaException('Destination folder incorrect', 500, null, 'error');

		}


	}

	private function isAllowedSize ($file) {

		$size = filesize($file);
		$allowedInMegabytes = 61;
		$sizeInMegabytes = $size / (1024 * 1024);

		if ($sizeInMegabytes >= $allowedInMegabytes) {

			throw new \Karolina\KarolinaException('File too big. Must be under 60 MB.', 400, null, 'invalid_file');

		} else {

			return true;

		}

	}

	public function storeAttachmentFile ($file, $destinationFolder) {

		$clientProvidedExtension = strtolower($file->getClientOriginalExtension());
		$uploadedFilePath = $file->getRealPath();
		$newFilename = $this->getUniqueName($clientProvidedExtension);

		if (
			$this->isAllowedSize($file)
			and $this->isExtensionAllowed($clientProvidedExtension) 
			and $this->isNotExecutable($file)
			and $this->isDestionationFolderAllowed($destinationFolder)
			) {
	
			// Upload an object to Amazon S3
			$result = $this->s3->putObject(array(
			    'Bucket' => 'attachments-bucket',
			    'Key'    => $destinationFolder.'/'.$newFilename,
			    'SourceFile' => $uploadedFilePath

			));

			return $newFilename;

		}


	}

	public function getSignedUrlToAttachmentFile ($filename, $destinationFolder) {

		$cmd = $this->s3->getCommand('GetObject', [
		    'Bucket' => 'attachments-bucket',
		    'Key'    => $destinationFolder.'/'.$filename
		]);

		$request = $this->s3->createPresignedRequest($cmd, '+20 minutes');

		// Get the actual presigned-url
		$presignedUrl = (string) $request->getUri();

		return $presignedUrl;

	}


}