<?php

namespace Fromholdio\SVGenius\Extensions;

use SilverStripe\Assets\File;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Extension;

class SVGReplaceFileAssetAdminExtension extends Extension
{
    protected function beforeCallActionHandler(HTTPRequest $request, $action): ?HTTPResponse
    {
        $name = $request->postVar('Upload')['name'] ?? '';
        if (strcasecmp($action, 'apiUploadFile') !== 0 || strtolower(File::get_file_extension($name)) !== 'svg') {
            return null;
        }
        $error = [
            'type' => 'error',
            'code' => 400,
            'value' => _t(
                __CLASS__ . '.REPLACE_WITH_SVG',
                'A file cannot be replaced with an SVG. Upload the SVG as a new file instead.'
            ),
        ];
        return HTTPResponse::create(json_encode(['status' => 'error', 'errors' => [$error]]), 400)
            ->addHeader('Content-Type', 'application/json');
    }
}
