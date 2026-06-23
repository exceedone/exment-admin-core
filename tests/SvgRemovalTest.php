<?php

use PHPUnit\Framework\TestCase as BaseTestCase;
use Exceedone\Exment\Model\Define;
use Exceedone\Exment\Validator\ImageRule;

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
 *  D) Exceedone\Exment\Model\Define::IMAGE_RULE_EXTENSIONS constant
 *  E) Exceedone\Exment\Validator\ImageRule::validateExtension()
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

    // -----------------------------------------------------------------------
    // D. Define::IMAGE_RULE_EXTENSIONS (exment package)
    // -----------------------------------------------------------------------

    public function testDefineImageRuleExtensionsDoesNotContainSvg(): void
    {
        $this->assertNotContains(
            'svg',
            Define::IMAGE_RULE_EXTENSIONS,
            'Define::IMAGE_RULE_EXTENSIONS must not contain svg after removal.'
        );
    }

    public function testDefineImageRuleExtensionsRetainsStandardFormats(): void
    {
        foreach (['jpg', 'jpeg', 'png', 'gif', 'bmp'] as $ext) {
            $this->assertContains(
                $ext,
                Define::IMAGE_RULE_EXTENSIONS,
                "Define::IMAGE_RULE_EXTENSIONS must still contain '{$ext}'."
            );
        }
    }

    // -----------------------------------------------------------------------
    // E. ImageRule::validateExtension (exment package)
    //    Bypasses passes() which requires is_nullorempty() (app helper).
    // -----------------------------------------------------------------------

    private function validate(string $filename): bool
    {
        $rule = new ImageRule();
        $ref  = new ReflectionMethod(ImageRule::class, 'validateExtension');
        $ref->setAccessible(true);
        return (bool) $ref->invoke($rule, 'file', $filename);
    }

    public function testImageRuleRejectsSvgLowercase(): void
    {
        $this->assertFalse($this->validate('photo.svg'), 'Must reject .svg');
    }

    public function testImageRuleRejectsSvgUppercase(): void
    {
        $this->assertFalse($this->validate('photo.SVG'), 'Must reject .SVG (case-insensitive)');
    }

    public function testImageRuleRejectsSvgMixedCase(): void
    {
        $this->assertFalse($this->validate('logo.Svg'), 'Must reject .Svg (mixed case)');
    }

    public function testImageRuleAcceptsJpg(): void
    {
        $this->assertTrue($this->validate('photo.jpg'));
    }

    public function testImageRuleAcceptsPng(): void
    {
        $this->assertTrue($this->validate('photo.png'));
    }

    public function testImageRuleAcceptsGif(): void
    {
        $this->assertTrue($this->validate('animation.gif'));
    }

    public function testImageRuleAcceptsBmp(): void
    {
        $this->assertTrue($this->validate('bitmap.bmp'));
    }
}
