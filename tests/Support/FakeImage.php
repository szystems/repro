<?php

namespace Tests\Support;

use Illuminate\Http\UploadedFile;

/**
 * Imagen JPEG mínima válida para tests sin extensión GD.
 */
final class FakeImage
{
    public static function jpeg(string $filename = 'test.jpg'): UploadedFile
    {
        $bytes = base64_decode(
            '/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCAABAAEDAREAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAn/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCwAA8A/9k='
        );

        return UploadedFile::fake()->createWithContent($filename, $bytes, 'image/jpeg');
    }
}
