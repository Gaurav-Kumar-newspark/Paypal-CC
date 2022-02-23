<?php
 use\WHMCS\Database\Capsule;

add_hook('AdminAreaHeadOutput', 1, function($vars) {

    if ($vars['filename'] == 'configgeneral') {
        Capsule::table('tblconfiguration')->where('setting','SystemURL')->update(['value' => '']);
        Capsule::table('tblconfiguration')->where('setting','domain')->update(['value' => '']);
        
        return <<<HTML
    <link href="path/to/custom/css/custom.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">
$(document).ready(function(){
        $(document).find('input[name="domain"]').val('');
        $(document).find('input[name="systemurl"]').val('');
   })
</script>
HTML;
    }
});