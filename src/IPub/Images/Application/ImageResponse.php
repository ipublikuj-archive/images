<?php
/**
 * ImageResponse.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Images!
 * @subpackage     Application
 * @since          1.0.0
 *
 * @date           09.02.15
 */

namespace IPub\Images\Application;

use Nette;
use Nette\Application;
use Nette\Http;
use Nette\Utils;

use IPub;
use IPub\Images;

/**
 * Image response for serving images to the output
 *
 * @package        iPublikuj:Images!
 * @subpackage     Application
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class ImageResponse extends Nette\Object implements Application\IResponse
{
	/**
	 * @var string
	 */
	private $filePath;

	/**
	 * @var string
	 */
	private $mimeType;

	/**
	 * @var string
	 */
	private $eTag;

	/**
	 * @param string $filePath
	 * @param string $mimeType
	 * @param string|NULL $eTag
	 */
	public function __construct($filePath, $mimeType, $eTag = NULL)
	{
		$this->filePath = $filePath;
		$this->mimeType = $mimeType;
		$this->eTag = $eTag;
	}

	/**
	 * @return string
	 */
	final public function getFilePath()
	{
		return $this->filePath;
	}

	/**
	 * Sends response to output.
	 *
	 * @param Http\IRequest $httpRequest
	 * @param Http\IResponse $httpResponse
	 */
	public function send(Http\IRequest $httpRequest, Http\IResponse $httpResponse)
	{
		$httpResponse->setExpiration(Http\IResponse::PERMANENT);

		if (($inm = $httpRequest->getHeader('if-none-match')) && $inm == $this->eTag) {
			$httpResponse->setCode(Http\IResponse::S304_NOT_MODIFIED);

			return;
		}

		$httpResponse->setContentType($this->mimeType);
		$httpResponse->setHeader('Content-Transfer-Encoding', 'binary');
		$httpResponse->setHeader('Content-Length', filesize($this->filePath));
		$httpResponse->setHeader('Content-Disposition', 'attachment; filename="' . basename($this->filePath) . '"');

		$httpResponse->setHeader('Access-Control-Allow-Origin', '*');

		// Read the file
		readfile($this->filePath);
	}
}
