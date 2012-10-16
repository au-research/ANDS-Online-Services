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
?>

<div id="page-footer" class="clearfix">
    Footer
</div>





    <!-- Mustache Template that should be used everywhere-->
    <div id="error-template" class="hide">
        <div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">×</button>{{.}}</div>
    </div>
  
    <!-- The javascripts Libraries
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script>
        localStorage.clear();
        var base_url = '<?php echo base_url();?>';
        var suffix = '<?php echo url_suffix();?>';
        var editor = '';
    </script>
    <?php foreach($js_lib as $lib):?>
        <?php if($lib=='core'):?>
            <script src="<?php echo base_url();?>assets/lib/jquery-1.7.2.min.js"></script>
            <script src="<?php echo base_url();?>assets/lib/less-1.3.0.min.js" type="text/javascript"></script>
            <script src="<?php echo base_url();?>assets/lib/jquery-ui-1.8.22.custom.min.js" type="text/javascript"></script>
            <script src="<?php echo base_url();?>assets/lib/dragdrop/jquery.event.drag-2.2.js"></script>
            <script src="<?php echo base_url();?>assets/lib/dragdrop/jquery.event.drag.live-2.2.js"></script>
            <script src="<?php echo base_url();?>assets/lib/dragdrop/jquery.event.drop-2.2.js"></script>
            <script src="<?php echo base_url();?>assets/lib/dragdrop/jquery.event.drop.live-2.2.js"></script>
            <script src="<?php echo base_url();?>assets/lib/mustache.js"></script>
            <script src="<?php echo base_url();?>assets/lib/chosen/chosen.jquery.js" type="text/javascript"></script>
            <script src="<?php echo base_url();?>assets/lib/jquery.ba-hashchange.js" type="text/javascript"></script>
            <script src="<?php echo base_url();?>assets/lib/bootstrap_toggle_button/jquery.toggle.buttons.js" type="text/javascript"></script>
            <script src="<?php echo base_url();?>assets/lib/qtip2/jquery.qtip.min.js" type="text/javascript"></script>


            
            
        <?php endif;?>

        <?php if($lib=='graph'):?>
            <!--[if lt IE 9]><script language="javascript" type="text/javascript" src="excanvas.js"></script><![endif]-->
            <script language="javascript" type="text/javascript" src="<?php echo base_url();?>assets/lib/jqplot/jquery.jqplot.min.js"></script>
            <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/lib/jqplot/jquery.jqplot.css" />
        <?php endif;?>

        <?php if($lib=='tinymce'):?>
            <script type="text/javascript" src="<?php echo base_url();?>assets/lib/tiny_mce/tiny_mce.js"></script>
            <script>
               var editor = 'tinymce';
            </script>
        <?php endif;?>

        <?php if($lib=='datepicker'):?>
            <script type="text/javascript" src="<?php echo base_url();?>assets/lib/bootstrap_datepicker/js/bootstrap-datepicker.js"></script>
            <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/lib/bootstrap_datepicker/css/datepicker.css" />
        <?php endif;?>

    <?php endforeach;?>


	<!-- ARMS scripts -->
    
	<script src="<?php echo base_url();?>assets/js/scripts.js"></script>

    <?php foreach($scripts as $script):?>
        <script src="<?php echo base_url();?>assets/js/<?php echo $script;?>.js"></script>
    <?php endforeach;?>

    

	<!-- Bootstrap javascripts, need to be placed after all else -->
    <script src="<?php echo base_url();?>assets/lib/twitter_bootstrap/js/bootstrap.js"></script>

  </body>
</html>