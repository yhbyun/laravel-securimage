<?php

namespace Yhbyun\Securimage;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SecurimageController extends Controller
{
    /**
     * @var Securimage
     */
    private $securimage;

    /**
     * SecurimageController constructor.
     */
    public function __construct(Securimage $securimage)
    {
        $this->securimage = $securimage;
    }

    /**
     * Returns capcha image.
     */
    public function getCaptcha()
    {
        $this->securimage->show();
    }

    /**
     * Returns capcha audio.
     */
    public function getAudio()
    {
        $this->securimage->outputAudioFile();
    }

    /**
     * Check whether the given code is correct.
     */
    public function check(Request $request)
    {
        $result = $this->securimage->checkOnly($request->captcha);

        return ['valid' => $result];
    }
}
