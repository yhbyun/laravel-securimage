<?php

namespace Yhbyun\Securimage;

use Illuminate\Contracts\Config\Repository as Config;
use Securimage as SecurimageLib;

class Securimage extends SecurimageLib
{
    protected $config;

    protected $deleteSessionAfterValidation = true;

    public function __construct(Config $config)
    {
        $this->config = $config;

        $config = $this->config['securimage'];
        $option = [
            'code_length'     => $config['length'],
            'image_width'     => $config['width'],
            'image_height'    => $config['height'],
            'perturbation'    => $config['perturbation'],
            'case_sensitive'  => $config['case_sensitive'],
            'num_lines'       => $config['num_lines'],
            'charset'         => $config['charset'],
            'image_signature' => $config['signature'],
            'no_exit'         => true,
            'no_session'      => true, // do not use php session
        ];
        parent::__construct($option);
    }

    public function getHtml()
    {
        $options = [
            'securimage_path'  => $this->config['securimage.securimage_path'],
            'audio_icon_url'   => $this->config['securimage.audio_icon_url'],
            'loading_icon_url' => $this->config['securimage.loading_icon_url'],
            'refresh_icon_url' => $this->config['securimage.refresh_icon_url'],
            'show_image_url'   => route('securimage'),
            'audio_play_url'   => route('securimage.audio'),
            'show_text_input'  => $this->config['securimage.show_text_input'],
            'input_id'         => 'captcha',
        ];

        return parent::getCaptchaHtml($options);
    }

    public function validator($value)
    {
        return $this->check($value);
    }

    /**
     * Checks a given code against the correct value from the session and/or database.
     * Unlike check, this method doesn't delete session and/or database after validation.
     *
     * @param string $code The captcha code to check
     *
     * @return bool true if the given code was correct, false if not.
     */
    public function checkOnly($code)
    {
        $this->code_entered = $code;
        $this->deleteSessionAfterValidation = false;
        $this->validate();

        return $this->correct_code;
    }

    /**
     * Checks a given code against the correct value from the session and/or database.
     *
     * @param string $code The captcha code to check
     *
     * @return bool true if the given code was correct, false if not.
     */
    public function check($code)
    {
        $this->code_entered = $code;
        $this->deleteSessionAfterValidation = true;
        $this->validate();

        return $this->correct_code;
    }

    /**
     * Return the code from the session or database (if configured).  If none exists or was found, an empty string is returned.
     *
     * @param bool $array          true to receive an array containing the code and properties, false to receive just the code.
     * @param bool $returnExisting If true, and the class property *code* is set, it will be returned instead of getting the code from the session or database.
     *
     * @return array|string Return is an array if $array = true, otherwise a string containing the code
     */
    public function getCode($array = false, $returnExisting = false)
    {
        $code = [];

        if ($returnExisting && strlen($this->code) > 0) {
            if ($array) {
                return [
                    'code'         => $this->code,
                    'display'      => $this->code_display,
                    'code_display' => $this->code_display,
                    'time'         => 0, ];
            } else {
                return $this->code;
            }
        }

        if (session()->has('securimage_code_value.'.$this->namespace) &&
            trim(session('securimage_code_value.'.$this->namespace)) != '') {
            if ($this->isCodeExpired(
                    session('securimage_code_ctime.'.$this->namespace)) == false) {
                $code['code'] = session('securimage_code_value.'.$this->namespace);
                $code['time'] = session('securimage_code_ctime.'.$this->namespace);
                $code['display'] = session('securimage_code_disp.'.$this->namespace);
            }
        }

        if (empty($code) && $this->use_database) {
            // no code in session - may mean user has cookies turned off
            $this->openDatabase();
            $code = $this->getCodeFromDatabase();

            if (!empty($code)) {
                $code['display'] = $code['code_disp'];
                unset($code['code_disp']);
            }
        } else { /* no code stored in session or sqlite database, validation will fail */
        }

        if ($array == true) {
            return $code;
        } else {
            return $code['code'];
        }
    }

    /**
     * Validate a code supplied by the user.
     *
     * Checks the entered code against the value stored in the session and/or database (if configured).  Handles case sensitivity.
     * Also removes the code from session/database if the code was entered correctly to prevent re-use attack.
     *
     * This function does not return a value.
     *
     * @see Securimage::$correct_code 'correct_code' property
     */
    protected function validate()
    {
        if (!is_string($this->code) || strlen($this->code) == 0) {
            $code = $this->getCode(true);
            // returns stored code, or an empty string if no stored code was found
            // checks the session and database if enabled
        } else {
            $code = $this->code;
        }

        if (is_array($code)) {
            if (!empty($code)) {
                $ctime = $code['time'];
                $code = $code['code'];

                $this->_timeToSolve = time() - $ctime;
            } else {
                $code = '';
            }
        }

        if ($this->case_sensitive == false && preg_match('/[A-Z]/', $code)) {
            // case sensitive was set from securimage_show.php but not in class
            // the code saved in the session has capitals so set case sensitive to true
            $this->case_sensitive = true;
        }

        $code_entered = trim((($this->case_sensitive) ? $this->code_entered
            : strtolower($this->code_entered))
        );
        $this->correct_code = false;

        if ($code != '') {
            if (strpos($code, ' ') !== false) {
                // for multi word captchas, remove more than once space from input
                $code_entered = preg_replace('/\s+/', ' ', $code_entered);
                $code_entered = strtolower($code_entered);
            }

            if ((string) $code === (string) $code_entered || $this->isTestEnvironment()) {
                $this->correct_code = true;
                if ($this->deleteSessionAfterValidation) {
                    session()->forget('securimage_code_disp.'.$this->namespace);
                    session()->forget('securimage_code_value.'.$this->namespace);
                    session()->forget('securimage_code_ctime.'.$this->namespace);
                    session()->forget('securimage_code_audio.'.$this->namespace);

                    $this->clearCodeFromDatabase();
                }
            }
        }
    }

    /**
     * Save CAPTCHA data to session and database (if configured).
     */
    protected function saveData()
    {
        if (session()->has('securimage_code_value') && is_scalar(session('securimage_code_value'))) {
            // fix for migration from v2 - v3
            session()->forget('securimage_code_value');
            session()->forget('securimage_code_ctime');
        }

        session(['securimage_code_disp.'.$this->namespace => $this->code_display]);
        session(['securimage_code_value.'.$this->namespace => $this->code]);
        session(['securimage_code_ctime.'.$this->namespace => time()]);
        session(['securimage_code_audio.'.$this->namespace => null]); // clear previous audio, if set

        if ($this->use_database) {
            $this->saveCodeToDatabase();
        }
    }

    /**
     * Save audio data to session and/or the configured database.
     *
     * @param string $data The CAPTCHA audio data
     */
    protected function saveAudioData($data)
    {
        session(['securimage_code_audio.'.$this->namespace => $data]);

        if ($this->use_database) {
            $this->saveAudioToDatabase($data);
        }
    }

    /**
     * Gets audio file contents from the session or database.
     *
     * @return string|bool Audio contents on success, or false if no audio found in session or DB
     */
    protected function getAudioData()
    {
        if (session()->has('securimage_code_audio.'.$this->namespace)) {
            return session('securimage_code_audio.'.$this->namespace);
        }

        if ($this->use_database) {
            $this->openDatabase();
            $code = $this->getCodeFromDatabase();

            if (!empty($code['audio_data'])) {
                return $code['audio_data'];
            }
        }

        return false;
    }

    /**
     * Determine if application is in test environment.
     *
     * @return bool
     */
    protected function isTestEnvironment()
    {
        return app()->isLocal() && isset($_COOKIE['selenium_request']);
    }
}
