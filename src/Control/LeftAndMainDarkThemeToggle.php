<?php

namespace Sunnysideup\CMSDarkTheme\Control;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use Sunnysideup\CMSDarkTheme\Api\LeftAndMainDarkThemeApi;

class LeftAndMainDarkThemeToggle extends Controller
{
    private static $url_segment = 'admin/displaymode';

    private static $url_rule = '/$Action/$ID/$OtherID';

    // private static $ignore_menuitem = false;

    private static $allowed_actions = [
        'switch',
        'setlightmode',
        'setdarkmode',
    ];

    /**
     * set mode and then
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function index($request)
    {
        return $this->switch($request);
    }

    /**
     * set mode and then
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function switch($request)
    {
        $owner = $this->getOwner();
        if (LeftAndMainDarkThemeApi::is_dark_mode()) {
            LeftAndMainDarkThemeApi::set_display_mode('Light');
        } else {
            LeftAndMainDarkThemeApi::set_display_mode('Dark');
        }
        return $owner->redirect('/admin/myprofile#Root_Cms');
    }


    public function setlightmode($request): HTTPResponse
    {
        $owner = $this->getOwner();
        LeftAndMainDarkThemeApi::set_display_mode('Light');
        return $owner->redirectBack();
    }

    public function setdarkmode($request): HTTPResponse
    {
        $owner = $this->getOwner();
        LeftAndMainDarkThemeApi::set_display_mode('Dark');
        return $owner->redirectBack();
    }


}
