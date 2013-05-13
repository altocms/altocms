<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Version: 0.9a
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 * Based on
 *   LiveStreet Engine Social Networking by Mzhelskiy Maxim
 *   Site: www.livestreet.ru
 *   E-mail: rus.engine@gmail.com
 *----------------------------------------------------------------------------
 */

class PluginSphinx_ActionSphinx extends ActionPlugin {

    public function Init() {

    }

    protected function RegisterEvent() {
        $this->AddEvent('config', 'EventConfig');
    }

    public function EventConfig() {
        $sFile = Plugin::GetPath(__CLASS__) . 'config/sphinx-src.conf';
        $sText = F::File_GetContents($sFile);

        $sPath = F::File_NormPath(Config::Get('plugin.sphinx.path') . '/');
        $sDescription = $this->Lang_Get(
            'plugin.sphinx.conf_description',
            array(
                 'path'   => $sPath,
                 'prefix' => Config::Get('plugin.sphinx.prefix')
            )
        );
        $sDescription = preg_replace('/\s\s+/', ' ', str_replace("\n", "\n## ", $sDescription));
        $sTitle = $this->Lang_Get('plugin.sphinx.conf_title');

        $aData = array(
            '{{title}}'        => $sTitle,
            '{{description}}'  => $sDescription,
            '{{db_type}}'      => (Config::Get('db.params.type') == 'postgresql') ? 'pgsql' : 'mysql',
            '{{db_host}}'      => Config::Get('db.params.host'),
            '{{db_user}}'      => Config::Get('db.params.user'),
            '{{db_pass}}'      => Config::Get('db.params.pass'),
            '{{db_name}}'      => Config::Get('db.params.dbname'),
            '{{db_port}}'      => Config::Get('db.params.port'),
            '{{db_prefix}}'      => Config::Get('db.table.prefix'),
            '{{db_socket}}'    => Config::Get('plugin.sphinx.db_socket'),
            '{{spinx_prefix}}' => Config::Get('plugin.sphinx.prefix'),
            '{{spinx_path}}'   => $sPath,
        );

        $sText = str_replace(array_keys($aData), array_values($aData), $sText);

        echo '<pre>';
        echo $sText;
        echo '</pre>';
        exit;
    }
}

// EOF