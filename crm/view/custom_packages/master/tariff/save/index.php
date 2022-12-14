<?php
include "../../../../../model/model.php";
include_once('../../../../layouts/fullwidth_app_header.php');
?>
<!-- Tab panes -->
<div class="bk_tab_head bg_light">
    <ul> 
        <li>
            <a href="javascript:void(0)" id="tab1_head" class="active">
                <span class="num" title="Costing Details">1<i class="fa fa-check"></i></span><br>
                <span class="text">Costing Details</span>
            </a>
        </li>
        <li>
            <a href="javascript:void(0)" id="tab2_head">
                <span class="num" title="Offers/Coupon">2<i class="fa fa-check"></i></span><br>
                <span class="text">Offers/Coupon</span>
            </a>
        </li>
    </ul>
</div>

<div class="bk_tabs">
    <div id="tab1" class="bk_tab active">
        <?php include_once("tab1.php"); ?>
    </div>
    <div id="tab2" class="bk_tab">
        <?php include_once("tab2.php"); ?>
    </div>
</div>

<script src="<?= BASE_URL ?>js/ajaxupload.3.5.js"></script>
<script src="<?php echo BASE_URL ?>js/app/field_validation.js"></script>

<?php
include_once('../../../../layouts/fullwidth_app_footer.php');
?>
<script src="<?php echo BASE_URL ?>js/app/footer_scripts.js"></script>