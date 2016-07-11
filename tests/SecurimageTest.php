<?php

namespace Yhbyun\Securimage\Test;

class SecurimageTest extends TestCase
{
    /** @test */
    public function it_returns_captcha_html()
    {
        $_SERVER['REMOTE_PORT'] = '127.0.01';
        $html = captcha_img();

        $this->assertContains('<img style="float: left; padding-right: 5px" id="captcha_image" src="http://localhost/securimage', $html);
        $this->assertContains('<script type="text/javascript" src="/vendor/securimage/securimage.js"></script>', $html);
        $this->assertContains('http://localhost/securimage/audio?id=', $html);
        $this->assertContains('/vendor/securimage/securimage_play.swf', $html);
        $this->assertContains('/vendor/securimage/images/audio_icon.png', $html);
        $this->assertContains('/vendor/securimage/images/refresh.png', $html);
        $this->assertContains('/vendor/securimage/images/refresh.png', $html);
    }

    /** @test */
    public function it_returns_captcha_image()
    {
        ob_start();
        $this->visit('securimage');
        $image = ob_get_contents();
        ob_end_clean();

        $headers = xdebug_get_headers();
        $this->assertContains('Content-Type: image/png', $headers);
        $this->assertSessionHas('securimage_code_value.default');

        // testing code check
        $this->visit('securimage/check?captcha='.$this->app['session.store']->get('securimage_code_value.default'))
            ->see(json_encode(['valid' => true]));

        $this->visit('securimage/check?captcha=1111')
            ->see(json_encode(['valid' => false]));
    }

    /** @test */
    public function it_returns_audio()
    {
        ob_start();
        $this->visit('securimage/audio');
        $audio = ob_get_contents();
        ob_end_clean();

        $headers = xdebug_get_headers();
        $this->assertContains('Content-type: audio/wav', $headers);
        $this->assertSessionHas('securimage_code_value.default');
        $this->assertSessionHas('securimage_code_audio.default');
    }
}
