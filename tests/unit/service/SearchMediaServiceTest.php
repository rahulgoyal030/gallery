<?php
/**
 * ownCloud - gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2015
 */

namespace OCA\Gallery\Service;

use OCP\Files\Folder;

/**
 * Class SearchMediaServiceTest
 *
 * @package OCA\Gallery\Controller
 */
class SearchMediaServiceTest extends \Test\GalleryUnitTest {

	/** @var SearchMediaService */
	protected $service;

	/**
	 * Test set up
	 */
	public function setUp() {
		parent::setUp();

		$this->service = new SearchMediaService (
			$this->appName,
			$this->environment,
			$this->logger
		);
	}

	public function testIsPreviewAvailable() {
		$file = $this->mockBadFile();

		$result = self::invokePrivate($this->service, 'isPreviewAvailable', [$file]);

		$this->assertFalse($result);
	}

	public function testGetMediaFilesWithUnavailableFolder() {
		$isReadable = false;
		$files = [];
		$topFolder = $this->mockFolder(
			'home::user', 545454, $files, $isReadable
		);
		$supportedMediaTypes = [];
		$features = [];
		$response = $this->service->getMediaFiles($topFolder, $supportedMediaTypes, $features);

		$this->assertSame([], $response);
	}

	public function providesTopFolderData() {
		$isReadable = true;
		$mounted = false;
		$mount = null;
		$query = '.nomedia';
		$queryResult = false;

		$folder1 = $this->mockFolder(
			'home::user', 545454, [
			$this->mockJpgFile(11111),
			$this->mockJpgFile(22222),
			$this->mockJpgFile(33333)
		], $isReadable, $mounted, $mount, $query, $queryResult
		);
		$folder2 = $this->mockFolder(
			'home::user', 767676, [
			$this->mockJpgFile(44444),
			$this->mockJpgFile(55555),
			$this->mockJpgFile(66666)
		], $isReadable, $mounted, $mount, $query, $queryResult
		);
		$folder3 = $this->mockFolder(
			'home::user', 101010, [$folder1], $isReadable, $mounted, $mount, $query, $queryResult
		);
		$folder4 = $this->mockFolder(
			'home::user', 101010, [$folder1, $folder2], $isReadable, $mounted, $mount, $query,
			$queryResult
		);
		$folder5 = $this->mockFolder(
			'home::user', 987234, [
			$this->mockJpgFile(998877),
			$this->mockJpgFile(998876),
			$this->mockNoMediaFile(998875)
		], $isReadable, $mounted, $mount, '.nomedia', true
		);
		$folder6 = $this->mockFolder(
			'webdav::user@domain.com/dav', 545454, [
			$this->mockJpgFile(11111)
		], $isReadable, true, $mount, $query, $queryResult
		);
		$folder7 = $this->mockFolder(
			'home::user', 545454, [
			$this->mockJpgFile(1),
			$this->mockJpgFile(2),
			$this->mockJpgFile(3),
			$this->mockJpgFile(4),
			$this->mockJpgFile(5),
		], $isReadable, $mounted, $mount, $query, $queryResult
		);

		// 2 folders and 3 files, everything is reachable
		$config1 = [
			$folder1,
			$folder2,
			$this->mockJpgFile(77777),
			$this->mockJpgFile(88888),
			$this->mockJpgFile(99999)

		];
		// 2 deepfolder and 3 files. Should return all the files
		$config2 = [
			$folder3,
			$folder3,
			$this->mockJpgFile(77777),
			$this->mockJpgFile(88888),
			$this->mockJpgFile(99999)

		];
		// 1 deepfolder (with 2 sub-folders) and 3 files. Should return the files and the content of 1 folder
		$config3 = [
			$folder4,
			$this->mockJpgFile(77777),
			$this->mockJpgFile(88888),
			$this->mockJpgFile(99999)
		];
		// 1 blacklisted folder and 3 files
		$config4 = [
			$folder5,
			$this->mockJpgFile(77777),
			$this->mockJpgFile(88888),
			$this->mockJpgFile(99999)

		];
		// 1 standard folder, 1 external share and 3 files
		$config5 = [
			$folder1,
			$folder6,
			$this->mockJpgFile(77777),
			$this->mockJpgFile(88888),
			$this->mockJpgFile(99999)

		];
		// 1 standard folder (3), 1 deep folder and 3 files
		$config6 = [
			$folder1,
			$folder7,
			$this->mockJpgFile(77777),
			$this->mockJpgFile(88888),
			$this->mockJpgFile(99999)

		];
		$topFolder1 = $this->mockFolder(
			'home::user', 909090, $config1, $isReadable, $mounted, $mount, $query, $queryResult
		);
		$topFolder2 = $this->mockFolder(
			'home::user', 909090, $config2, $isReadable, $mounted, $mount, $query, $queryResult
		);
		$topFolder3 = $this->mockFolder(
			'home::user', 909090, $config3, $isReadable, $mounted, $mount, $query, $queryResult
		);
		$topFolder4 = $this->mockFolder(
			'home::user', 909090, $config4, $isReadable, $mounted, $mount, $query, $queryResult
		);
		$topFolder5 = $this->mockFolder(
			'home::user', 909090, $config5, $isReadable, $mounted, $mount, $query, $queryResult
		);
		$topFolder6 = $this->mockFolder(
			'home::user', 909090, $config6, $isReadable, $mounted, $mount, $query, $queryResult
		);

		return [
			[$topFolder1, 9],
			[$topFolder2, 9],
			[$topFolder3, 6],
			[$topFolder4, 3],
			[$topFolder5, 6],
			[$topFolder6, 10]
		];
	}

	/**
	 * @dataProvider providesTopFolderData
	 *
	 * @param Folder $topFolder
	 * @param int $result
	 */
	public function testGetMediaFiles($topFolder, $result) {
		$supportedMediaTypes = [
			'image/png',
			'image/jpeg',
			'image/gif'
		];
		$features = [];

		$response = $this->service->getMediaFiles($topFolder, $supportedMediaTypes, $features);

		$this->assertSame($result, sizeof($response));
	}

	public function providesFolderWithFilesData() {
		$isReadable = true;
		$mounted = false;
		$mount = null;
		$query = '.nomedia';
		$queryResult = false;

		$file1 = [
			'fileid'         => 11111,
			'storageId'      => 'home::user',
			'isReadable'     => true,
			'path'           => null,
			'etag'           => "8603c11cd6c5d739f2c156c38b8db8c4",
			'size'           => 1024,
			'sharedWithUser' => false,
			'mimetype'       => 'image/jpeg'
		];

		$file2 = [
			'fileid'         => 22222,
			'storageId'      => 'webdav::user@domain.com/dav',
			'isReadable'     => true,
			'path'           => null,
			'etag'           => "739f2c156c38b88603c11cd6c5ddb8c4",
			'size'           => 102410241024,
			'sharedWithUser' => true,
			'mimetype'       => 'image/jpeg'
		];


		$folder1 = $this->mockFolder(
			'home::user', 545454, [
			$this->mockJpgFile(
				$file1['fileid'], $file1['storageId'], $file1['isReadable'], $file1['path'],
				$file1['etag'], $file1['size'], $file1['sharedWithUser']
			),
			$this->mockJpgFile(
				$file2['fileid'], $file2['storageId'], $file2['isReadable'], $file2['path'],
				$file2['etag'], $file2['size'], $file2['sharedWithUser']
			)
		], $isReadable, $mounted, $mount, $query, $queryResult
		);

		return [
			[
				$folder1, [
				[
					'path'           => $file1['path'],
					'fileid'         => $file1['fileid'],
					'mimetype'       => $file1['mimetype'],
					'mtime'          => null,
					'etag'           => $file1['etag'],
					'size'           => $file1['size'],
					'sharedWithUser' => $file1['sharedWithUser']
				],
				[
					'path'           => $file2['path'],
					'fileid'         => $file2['fileid'],
					'mimetype'       => $file2['mimetype'],
					'mtime'          => null,
					'etag'           => $file2['etag'],
					'size'           => $file2['size'],
					'sharedWithUser' => $file2['sharedWithUser']
				]
			]
			]
		];
	}

	/**
	 * @dataProvider providesFolderWithFilesData
	 *
	 * @param Folder $topFolder
	 * @param array $result
	 */
	public function testPropertiesOfGetMediaFiles($topFolder, $result) {
		$supportedMediaTypes = [
			'image/png',
			'image/jpeg',
			'image/gif'
		];
		$features = [];

		$response = $this->service->getMediaFiles($topFolder, $supportedMediaTypes, $features);

		$this->assertSame($result, $response);
	}

	/**
	 * @expectedException \OCA\Gallery\Service\NotFoundServiceException
	 */
	public function testGetResourceFromIdWithUnreadableFile() {
		$fileId = 99999;
		$storageId = 'home::user';
		$isReadable = false;
		$file = $this->mockFile($fileId, $storageId, $isReadable);
		$this->mockGetResourceFromId($this->environment, $fileId, $file);

		$this->service->getResourceFromId($fileId);
	}

}
