<?php
class TestEvent
{
    public function exec() {
//        $project = new ProjectBase();
//        $rs = $project->getProjectInfoByID(1);
        echo "self queue";
        return "self queue";
    }
}
