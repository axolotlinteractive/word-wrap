<?php
/**
 * Created by PhpStorm.
 * User: bryce
 * Date: 9/13/15
 * Time: 2:58 AM
 */

namespace WordWrap\Assets\View;


use WordWrap\LifeCycle;

class Editor extends View {

    /**
     * @var string the id of this editor
     */
    private $editorId;

    /**
     * @var string the content to fill this editor with
     */
    private $content;

    /**
     * @var string the title of this editor
     */
    private $title;

    /**
     * @var null|int for the total height of the editor in pixels
     */
    private $height = null;

    /**
     * @var bool whether or not to show the media buttons defaults to true
     */
    private $mediaButtons = true;

    /**
     * @param LifeCycle $lifeCycle
     * @param null|string $editorId
     * @param string $content
     * @param $title
     */
    public function __construct(LifeCycle $lifeCycle, $editorId, $content, $title) {
        parent::__construct($lifeCycle, "admin_editor", "admin_html");

        $this->editorId = $editorId;
        $this->content = $content;
        $this->title = $title;

        $this->setTemplateVar("editor_id", $this->editorId);
        $this->setTemplateVar("title", $this->title);

    }

    /**
     * turns off media buttons
     */
    public function disableMedia() {
        $this->mediaButtons = false;
    }

    /**
     * @param $height int the height in pixels
     */
    public function setHeight($height) {
        $this->height = $height;
    }

    public function export() {

        $settings = [
            "media_buttons" => $this->mediaButtons
        ];

        if($this->height)
            $settings["editor_height"] = $this->height;

        ob_start();
        wp_editor($this->content, $this->editorId, $settings);
        $this->setTemplateVar("editor", ob_get_clean());

        return parent::export();
    }
}