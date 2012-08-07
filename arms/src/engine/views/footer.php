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
<div class="row-fluid">
    <div class="span12" id="page-footer">
        Footer
    </div>
</div>


    <!-- Mustache Template that should be used everywhere-->
    <div id="error-template" class="hide">
        <div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">×</button>{{.}}</div>
    </div>
  
    <!-- The javascripts Libraries
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
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


	<!-- ARMS scripts -->
    <script>
        var base_url = '<?php echo base_url();?>';
        var suffix = '#!/';
    </script>
	<script src="<?php echo base_url();?>assets/js/scripts.js"></script>

    <?php foreach($scripts as $script):?>
        <script src="<?php echo base_url();?>assets/js/<?php echo $script;?>.js"></script>
    <?php endforeach;?>

    

	<!-- Bootstrap javascripts, need to be placed after all else -->
    <script src="<?php echo base_url();?>assets/lib/twitter_bootstrap/js/bootstrap.min.js"></script>

  </body>
</html>