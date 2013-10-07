<?php

class PluginDemo_BlockDemoruleexec extends Block {
    public function Exec() {
        $sContent = 'oldschool LS-block (exec via rule)<br/><strong>DemoRuleExec</strong>';
        $this->Viewer_Assign('sContent', $sContent);
    }
}
