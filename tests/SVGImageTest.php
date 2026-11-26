<?php

namespace Fromholdio\SVGenius\Tests;

use Fromholdio\SVGenius\Model\SVGImage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SilverStripe\Assets\Dev\TestAssetStore;
use SilverStripe\Assets\Upload;
use SilverStripe\Assets\Upload_Validator;
use SilverStripe\Dev\SapphireTest;

class SVGImageTest extends SapphireTest
{
    protected $usesDatabase = true;

    protected function setUp(): void
    {
        parent::setUp();
        TestAssetStore::activate('SVGImageTest');
        Upload_Validator::config()->set('use_is_uploaded_file', false);
    }

    protected function tearDown(): void
    {
        TestAssetStore::reset();
        parent::tearDown();
    }

    public function testUploadAndPublishStoreOnlySanitisedSVG(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'svg');
        file_put_contents(
            $path,
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" onload="alert(1)"><script>alert(1)</script><circle cx="24" cy="24" r="20"/></svg>'
        );
        $tmpFile = [
            'name' => 'icon.svg',
            'tmp_name' => $path,
            'size' => filesize($path),
            'error' => UPLOAD_ERR_OK,
        ];

        $image = SVGImage::create();
        $this->assertTrue(Upload::create()->loadIntoFile($tmpFile, $image));
        $image->publishSingle();

        $storedFiles = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(TestAssetStore::base_path(), RecursiveDirectoryIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->getExtension() === 'svg') {
                $storedFiles[] = file_get_contents($file->getPathname());
            }
        }

        $this->assertCount(1, $storedFiles);
        $this->assertSame($image->getString(), $storedFiles[0]);
        $this->assertStringContainsString('<circle', $storedFiles[0]);
        $this->assertStringNotContainsString('script', $storedFiles[0]);
        $this->assertStringNotContainsString('onload', $storedFiles[0]);
    }
}
