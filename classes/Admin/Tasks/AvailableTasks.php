<?php
namespace WordWrap\Admin\Task;
use WordWrap\Admin\TaskController;
use WordWrap\Configuration\Task;
use WordWrap\View\Anchor;
use WordWrap\View\ViewCollection;

/**
 * Created by PhpStorm.
 * User: bryce
 * Date: 9/12/15
 * Time: 1:01 PM
 */
class AvailableTasks extends TaskController {


    /**
     * @var Task[] all available taks for the current page
     */
    private $availableTask;

    /**
     * @var string the slug of this page
     */
    private $pageSlug;

    /**
     * override this to setup anything that needs to be done before
     */
    public function setup() {
        $this->availableTask = $this->adminController->currentPage->Task;
        $this->pageSlug = $this->adminController->currentPage->getSlug();
    }

    /**
     * override to render the main page
     */
    public function renderMainContent() {
        $viewCollection = new ViewCollection($this->lifeCycle, "available_task/container");

        foreach($this->availableTask as $task) {
            $anchor = new Anchor($this->lifeCycle);

            $href = "?page=$this->pageSlug&task=" . $task->getSlug();

            $anchor->setContent($task->name);
            $anchor->setHref($href);
            $anchor->addClass("block_link");

            $viewCollection->addChildView("task_link", $anchor);
        }

        return $viewCollection->export();
    }

    /**
     * override to render the main page
     */
    public function renderSidebarContent() {
        // TODO: Implement renderSidebarContent() method.
    }
}