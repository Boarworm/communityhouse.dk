<?php namespace Boarworm\Base\Classes\Markup;

use Media\Classes\MediaLibrary;
use October\Rain\Database\Attach\File as AttachFile;

/**
 * ImageDimensions resolves the intrinsic pixel size of an image.
 *
 * Supports fileupload/attachment models and media library paths, for both
 * raster images and SVG (which getimagesize() cannot read).
 */
class ImageDimensions
{
    /**
     * get returns the image size, or a single dimension when a key is given.
     *
     * @return array{width: int, height: int}|int|null
     */
    public static function get($image, ?string $key = null): array|int|null
    {
        $contents = static::readContents($image);

        if ($contents === null) {
            return null;
        }

        $size = static::readSvgSize($contents) ?? static::readRasterSize($contents);

        if ($key !== null) {
            return $size[$key] ?? null;
        }

        return $size;
    }

    /**
     * readContents loads the raw file contents from an attachment or media path.
     */
    protected static function readContents($image): ?string
    {
        try {
            if ($image instanceof AttachFile) {
                return $image->getContents();
            }

            if (is_string($image) && $image !== '') {
                return MediaLibrary::instance()->get($image);
            }
        }
        catch (\Exception $ex) {
            return null;
        }

        return null;
    }

    /**
     * readSvgSize parses the width/height attributes of an SVG, falling back
     * to the last two values of its viewBox.
     *
     * @return array{width: int, height: int}|null
     */
    protected static function readSvgSize(string $contents): ?array
    {
        if (!str_contains($contents, '<svg')) {
            return null;
        }

        $svg = @simplexml_load_string($contents);

        if (!$svg) {
            return null;
        }

        $width = (float) $svg['width'];
        $height = (float) $svg['height'];

        if ((!$width || !$height) && isset($svg['viewBox'])) {
            $box = preg_split('/[\s,]+/', trim((string) $svg['viewBox']));

            if (count($box) === 4) {
                $width = $width ?: (float) $box[2];
                $height = $height ?: (float) $box[3];
            }
        }

        if (!$width || !$height) {
            return null;
        }

        return ['width' => (int) round($width), 'height' => (int) round($height)];
    }

    /**
     * readRasterSize reads the size of a raster image.
     *
     * @return array{width: int, height: int}|null
     */
    protected static function readRasterSize(string $contents): ?array
    {
        $size = @getimagesizefromstring($contents);

        if (!$size) {
            return null;
        }

        return ['width' => (int) $size[0], 'height' => (int) $size[1]];
    }
}
