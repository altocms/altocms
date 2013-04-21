<?php

class PluginDemo_BlockDemoexec extends Block {
    public function Exec() {
        $sContent = 'oldschool LS-block (exec)<br/><strong>Demoexec</strong>';
        $this->Viewer_Assign('sContent', $sContent);
    }
}
