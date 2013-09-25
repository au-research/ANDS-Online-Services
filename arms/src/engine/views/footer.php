<?php
/**
 * Core Template File (footer)
 *
 *
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @see ands/
 * @package ands/
 *
 */
// Variable defaults
$js_lib = !isset($js_lib) ? array() : $js_lib;
$base_url = str_replace('/apps','/registry',base_url());
?>
<div id="page-footer" class="clearfix">&nbsp;
</div>





    <!-- Mustache Template that should be used everywhere-->
    <div id="error-template" class="hide">
        <div class="alert alert-error">{{{.}}}</div>
    </div>

    <!-- The javascripts Libraries
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script>

      //  localStorage.clear();
        var base_url = '<?php echo $base_url;?>';
        var portal_url = '<?php echo portal_url();?>';
        var apps_url = '<?php echo apps_url();?>';
        var real_base_url = "<?php echo $this->config->item('default_base_url');?>";
        var suffix = '<?php echo url_suffix();?>';
        var editor = '';
        //urchin code
        <?php echo urchin_for($this->config->item('svc_urchin_id')); ?>
    </script>
    <?php foreach($js_lib as $lib):?>

        <?php if($lib=='core'):?>
            <script src="<?php echo$base_url;?>assets/lib/jquery-1.7.2.min.js"></script>
            <script src="<?php echo$base_url;?>assets/lib/less-1.3.0.min.js" type="text/javascript"></script>
            <script src="<?php echo$base_url;?>assets/lib/jquery-ui-1.8.22.custom.min.js" type="text/javascript"></script>
            <!--script src="<?php echo$base_url;?>assets/lib/dragdrop/jquery.event.drag-2.2.js"></script>
            <script src="<?php echo$base_url;?>assets/lib/dragdrop/jquery.event.drag.live-2.2.js"></script>
            <script src="<?php echo$base_url;?>assets/lib/dragdrop/jquery.event.drop-2.2.js"></script>
            <script src="<?php echo$base_url;?>assets/lib/dragdrop/jquery.event.drop.live-2.2.js"></script>
            <script src="<?php echo$base_url;?>assets/lib/jquery.multisortable.js"></script-->
            <script src="<?php echo$base_url;?>assets/lib/jquery.sticky.js"></script>
            <script src="<?php echo$base_url;?>assets/lib/mustache.js"></script>
            <script src="<?php echo$base_url;?>assets/lib/chosen/chosen.jquery.js" type="text/javascript"></script>
            <script src="<?php echo$base_url;?>assets/lib/jquery.ba-hashchange.js" type="text/javascript"></script>
            <script src="<?php echo$base_url;?>assets/lib/bootstrap_toggle_button/jquery.toggle.buttons.js" type="text/javascript"></script>
            <script src="<?php echo$base_url;?>assets/lib/qtip2/jquery.qtip.min.js" type="text/javascript"></script>
            <script src="<?php echo$base_url;?>assets/lib/youtubepopup/jquery.youtubepopup.min.js" type="text/javascript"></script>            
            <!--script src="<?php echo$base_url;?>assets/registry_object_search/js/rosearch_widget.js" type="text/javascript"></script-->

        <?php elseif($lib=='graph'):?>

            <!--[if lt IE 9]><script language="javascript" type="text/javascript" src="excanvas.js"></script><![endif]-->
            <script language="javascript" type="text/javascript" src="<?php echo$base_url;?>assets/lib/jqplot/jquery.jqplot.min.js"></script>
            <script language="javascript" type="text/javascript" src="<?php echo$base_url;?>assets/lib/jqplot/plugins/jqplot.dateAxisRenderer.min.js"></script>
            <script type="text/javascript" src="<?php echo$base_url;?>assets/lib/jqplot/plugins/jqplot.highlighter.min.js"></script>
            <script type="text/javascript" src="<?php echo$base_url;?>assets/lib/jqplot/plugins/jqplot.cursor.min.js"></script>
            <link rel="stylesheet" type="text/css" href="<?php echo$base_url;?>assets/lib/jqplot/jquery.jqplot.css" />


        <?php elseif($lib=='googleapi'):?>
            <script type='text/javascript' src='https://www.google.com/jsapi'></script>
            <script type="text/javascript">
                localGoogle = google;
                google.load("visualization", "1", {packages:["corechart"]});
            </script>

        <?php elseif($lib=='tinymce'):?>
            <script type="text/javascript" src="<?php echo$base_url;?>assets/lib/tiny_mce4/tinymce.min.js"></script>
            <script>
               var editor = 'tinymce';
            </script>

        <?php elseif($lib=='datepicker'):?>
            <script type="text/javascript" src="<?php echo $base_url;?>assets/lib/bootstrap_datepicker/js/bootstrap-datepicker.js"></script>
            <link rel="stylesheet" type="text/css" href="<?php echo$base_url;?>assets/lib/bootstrap_datepicker/css/datepicker.css" />

        <?php elseif($lib=='ands_datepicker'):?>
            <script type="text/javascript" src="<?php echo apps_url('assets/datepicker_tz_widget/js/ands_datetimepicker.js');?>"></script>
            <link rel="stylesheet" type="text/css" href="<?php echo apps_url('assets/datepicker_tz_widget/css/ands_datetimepicker.css');?>" />

        <?php elseif($lib=='ands_datetimepicker_widget'):?>
            <link href="<?php echo apps_url('assets/datepicker_tz_widget/css/ands_datetimepicker.css');?>" rel="stylesheet" type="text/css">
            <script src="<?php echo apps_url('assets/datepicker_tz_widget/js/ands_datetimepicker.js');?>" type="text/javascript"></script>

        <?php elseif($lib=='prettyprint'):?>
            <script type="text/javascript" src="<?php echo$base_url;?>assets/lib/prettyprint/pretty.js"></script>
            <link rel="stylesheet" type="text/css" href="<?php echo$base_url;?>assets/lib/prettyprint/pretty.css" />

        <?php elseif($lib=='dataTables'):?>
            <script type="text/javascript" src="<?php echo$base_url;?>assets/lib/dataTable/js/jquery.dataTables.js"></script>

        <?php elseif($lib=='abs_sdmx_querytool'):?>
            <script type="text/javascript" src="<?php echo$base_url;?>assets/js/abs_sdmx_querytool.js"></script>

        <?php elseif($lib=='context_menu'):?>
            <script src="<?php echo$base_url;?>assets/lib/bootstrap-contextmenu.js" type="text/javascript"></script>

        <?php elseif($lib=='vocab_widget'):?>
            <link rel="stylesheet" type="text/css" href="<?php echo apps_url('assets/vocab_widget/css/vocab_widget.css'); ?>">
            <script src="<?php echo apps_url('assets/vocab_widget/js/vocab_widget.js'); ?>"></script>

       <?php elseif($lib=='orcid_widget'):?>
            <link href="<?php echo apps_url('assets/orcid_widget/css/orcid_widget.css');?>" rel="stylesheet" type="text/css">
            <script src="<?php echo apps_url('assets/orcid_widget/js/orcid_widget.js');?>" type="text/javascript"></script>

        <?php elseif($lib=='location_capture_widget'):?>
            <link href="<?php echo apps_url('assets/location_capture_widget/css/location_capture_widget.css');?>" rel="stylesheet" type="text/css">
            <script src="<?php echo apps_url('assets/location_capture_widget/js/location_capture_widget.js');?>" type="text/javascript"></script>

        <?php elseif($lib=='rosearch_widget'):?>
            <link href="<?php echo apps_url('assets/registry_object_search/css/rosearch_widget.css');?>" rel="stylesheet" type="text/css">
            <script src="<?php echo apps_url('assets/registry_object_search/js/rosearch_widget.js');?>" type="text/javascript"></script>

         <?php elseif($lib=='statistics'):?>
            <script src="<?php echo str_replace('/apps','/applications/apps',base_url());?>statistics/assets/js/statistics.js" type="text/javascript"></script>

        <?php elseif($lib=='bootstro'):?>
            <link href="<?php echo base_url();?>assets/lib/bootstro/bootstro.min.css" rel="stylesheet" type="text/css">
            <script src="<?php echo base_url();?>assets/lib/bootstro/bootstro.min.js" type="text/javascript"></script>

        <?php elseif($lib=='google_map'):?>
            <script src="https://maps.googleapis.com/maps/api/js?libraries=drawing&amp;sensor=false" type="text/javascript"></script>

        <?php endif; ?>

    <?php endforeach;?>


	<!-- ARMS scripts -->
	<script src="<?php echo$base_url;?>assets/js/scripts.js"></script>


	<!-- Module-specific styles and scripts -->
    <?php if (isset($scripts)): foreach($scripts as $script):?>
        <script src="<?php echo asset_url('js/' . $script);?>.js" defer></script>
    <?php endforeach; endif; ?>

    <?php if (isset($styles)): foreach($styles as $style):?>
    	<link rel="stylesheet" type="text/css" href="<?php echo asset_url('css/' . $style);?>.css" />
    <?php endforeach; endif; ?>



	<!-- Bootstrap javascripts, need to be placed after all else -->
    <script src="<?php echo$base_url;?>assets/lib/twitter_bootstrap/js/bootstrap.js"></script>

  </body>
</html>
