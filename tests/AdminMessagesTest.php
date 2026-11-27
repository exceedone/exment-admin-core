<?php

use Illuminate\Support\HtmlString;

/**
 * The messages of admin_error() and its siblings, and of admin_toastr(), are shown as text,
 * unless they are marked as HTML (an Htmlable).
 */
class AdminMessagesTest extends TestCase
{
    private function alerts(): string
    {
        return view('admin::partials.alerts')->render();
    }

    private function toastr(): string
    {
        return view('admin::partials.toastr')->render();
    }

    private function jsonText(string $text): string
    {
        return (string) json_encode($text, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    public function testPlainMessageWithMarkupIsShownAsText(): void
    {
        admin_error('Title <i>x</i>', 'a <b>bold</b> word');

        $html = $this->alerts();

        $this->assertStringContainsString('a &lt;b&gt;bold&lt;/b&gt; word', $html);
        $this->assertStringContainsString('Title &lt;i&gt;x&lt;/i&gt;', $html);
        $this->assertStringNotContainsString('<b>bold</b>', $html);
    }

    public function testEachTypeShowsItsPlainMessageAsText(): void
    {
        admin_success('S', '<u>success</u>');
        admin_info('I', '<u>info</u>');
        admin_warning('W', '<u>warning</u>');

        $html = $this->alerts();

        foreach (['success', 'info', 'warning'] as $word) {
            $this->assertStringContainsString("&lt;u&gt;{$word}&lt;/u&gt;", $html);
            $this->assertStringNotContainsString("<u>{$word}</u>", $html);
        }
    }

    public function testHtmlableMessageIsShownAsHtml(): void
    {
        admin_error('Title', new HtmlString('a <b>bold</b> word<br><a href="/x">link</a>'));

        $html = $this->alerts();

        $this->assertStringContainsString('a <b>bold</b> word<br><a href="/x">link</a>', $html);
    }

    public function testHtmlableInAnArrayIsShownAsHtml(): void
    {
        // the form a package passes when it also supports the versions before this one
        admin_warning('Title', [new HtmlString('<b>bold</b>')]);

        $this->assertStringContainsString('<b>bold</b>', $this->alerts());
    }

    public function testHtmlableTitleIsShownAsHtml(): void
    {
        admin_success(new HtmlString('<i>done</i>'), 'text');

        $this->assertStringContainsString('<i>done</i>', $this->alerts());
    }

    public function testOnceMessages(): void
    {
        admin_error_once('E', '<b>plain</b>');
        admin_warning_once('W', new HtmlString('<b>html</b>'));

        $html = $this->alerts();

        $this->assertStringContainsString('&lt;b&gt;plain&lt;/b&gt;', $html);
        $this->assertStringContainsString('<b>html</b>', $html);
    }

    public function testToastrPlainMessageIsEscapedByToastr(): void
    {
        admin_toastr('<b>saved</b>', 'success', ['timeOut' => 1000]);

        $html = $this->toastr();

        $this->assertStringContainsString('"escapeHtml":true', $html);
        $this->assertStringContainsString('"timeOut":1000', $html);
        // written as JSON, the tags hex-escaped
        $this->assertStringContainsString($this->jsonText('<b>saved</b>'), $html);
        $this->assertStringNotContainsString('<b>saved</b>', $html);
    }

    public function testToastrHtmlableMessageIsShownAsHtml(): void
    {
        admin_toastr(new HtmlString('<b>saved</b>'), 'warning');

        $html = $this->toastr();

        $this->assertStringContainsString('"escapeHtml":false', $html);
        $this->assertStringContainsString('toastr["warning"]', $html);
        $this->assertStringContainsString($this->jsonText('<b>saved</b>'), $html);
    }

    public function testToastrHtmlableInAnArrayIsShownAsHtml(): void
    {
        admin_toastr([new HtmlString('<b>saved</b>')]);

        $this->assertStringContainsString('"escapeHtml":false', $this->toastr());
    }

    public function testToastrMarkerIsNotOverriddenByTheOptions(): void
    {
        admin_toastr('<b>saved</b>', 'success', ['escapeHtml' => false]);

        $this->assertStringContainsString('"escapeHtml":true', $this->toastr());
    }
}
