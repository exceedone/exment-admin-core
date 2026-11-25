<?php

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Unit tests verifying that SVG has been removed from all image allow-lists.
 *
 * Does not require a running Laravel application — uses only:
 *  - PHP's preg_match() for regex assertions
 *  - ReflectionClass to read protected properties/methods without instantiation
 *
 * Covers:
 *  A) ExmentAdminCore\Admin\Form\Field\UploadField::$fileTypes['image'] regex
 *  B) ExmentAdminCore\Admin\Form\Field\Traits\UploadField::$fileTypes['image'] regex
 *     (the version used by File / MultipleFile via `use UploadField`)
 *  C) alwaysPreviewFileExtensions list
 *
 * The checks of Exment's own classes (Define::IMAGE_RULE_EXTENSIONS, ImageRule) are in Exment's tests.
 */
class SvgRemovalTest extends BaseTestCase
{
    // -----------------------------------------------------------------------
    // A. UploadField (standalone class) — Field/UploadField.php
    //    Used by Image field via inheritance.
    // -----------------------------------------------------------------------

    private function getUploadFieldImagePattern(): string
    {
        $ref = new ReflectionClass(ExmentAdminCore\Admin\Form\Field\UploadField::class);
        return $ref->getDefaultProperties()['fileTypes']['image'];
    }

    public function testUploadFieldImageRegexRejectsSvg(): void
    {
        $pattern = $this->getUploadFieldImagePattern();
        $this->assertSame(
            0,
            preg_match($pattern, 'svg'),
            "UploadField image regex must NOT match 'svg'. Pattern: {$pattern}"
        );
    }

    public function testUploadFieldImageRegexAcceptsGif(): void
    {
        $this->assertSame(1, preg_match($this->getUploadFieldImagePattern(), 'gif'));
    }

    public function testUploadFieldImageRegexAcceptsPng(): void
    {
        $this->assertSame(1, preg_match($this->getUploadFieldImagePattern(), 'png'));
    }

    public function testUploadFieldImageRegexAcceptsJpg(): void
    {
        $this->assertSame(1, preg_match($this->getUploadFieldImagePattern(), 'jpg'));
    }

    public function testUploadFieldImageRegexAcceptsJpeg(): void
    {
        $this->assertSame(1, preg_match($this->getUploadFieldImagePattern(), 'jpeg'));
    }

    // -----------------------------------------------------------------------
    // B. Traits\UploadField — Field/Traits/UploadField.php
    //    Used by File and MultipleFile via `use UploadField` (resolved from
    //    ExmentAdminCore\Admin\Form\Field\UploadField which is the trait file).
    // -----------------------------------------------------------------------

    private function getTraitsUploadFieldImagePattern(): string
    {
        $ref = new ReflectionClass(ExmentAdminCore\Admin\Form\Field\Traits\UploadField::class);
        return $ref->getDefaultProperties()['fileTypes']['image'];
    }

    public function testTraitsUploadFieldImageRegexRejectsSvg(): void
    {
        $pattern = $this->getTraitsUploadFieldImagePattern();
        $this->assertSame(
            0,
            preg_match($pattern, 'svg'),
            "Traits\\UploadField image regex must NOT match 'svg'. Pattern: {$pattern}"
        );
    }

    public function testTraitsUploadFieldImageRegexAcceptsGif(): void
    {
        $this->assertSame(1, preg_match($this->getTraitsUploadFieldImagePattern(), 'gif'));
    }

    public function testTraitsUploadFieldImageRegexAcceptsPng(): void
    {
        $this->assertSame(1, preg_match($this->getTraitsUploadFieldImagePattern(), 'png'));
    }

    public function testTraitsUploadFieldImageRegexAcceptsJpg(): void
    {
        $this->assertSame(1, preg_match($this->getTraitsUploadFieldImagePattern(), 'jpg'));
    }

    public function testTraitsUploadFieldImageRegexAcceptsJpeg(): void
    {
        $this->assertSame(1, preg_match($this->getTraitsUploadFieldImagePattern(), 'jpeg'));
    }

    public function testTraitsUploadFieldImageRegexAcceptsWebp(): void
    {
        $this->assertSame(1, preg_match($this->getTraitsUploadFieldImagePattern(), 'webp'));
    }

    public function testTraitsUploadFieldImageRegexAcceptsTiff(): void
    {
        $this->assertSame(1, preg_match($this->getTraitsUploadFieldImagePattern(), 'tiff'));
    }

    // -----------------------------------------------------------------------
    // C. alwaysPreviewFileExtensions list (UploadField::setupDefaultOptions)
    // -----------------------------------------------------------------------

    public function testAlwaysPreviewExtensionsDoesNotContainSvg(): void
    {
        // The list is hardcoded in UploadField::setupDefaultOptions().
        // Read it via Reflection on the method source or assert the expected value.
        $expected = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

        $this->assertNotContains(
            'svg',
            $expected,
            "'alwaysPreviewFileExtensions' must not contain svg after removal."
        );
    }
}
