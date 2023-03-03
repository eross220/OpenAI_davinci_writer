<!-- JQuery-->
<script src="<?php echo e(URL::asset('plugins/jquery/jquery-3.6.0.min.js')); ?>"></script>

<!-- Bootstrap 5-->
<script src="<?php echo e(URL::asset('plugins/bootstrap-5.0.2/js/bootstrap.bundle.min.js')); ?>"></script>

<!-- Tippy JS -->
<script src="<?php echo e(URL::asset('plugins/tippy/popper.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('plugins/tippy/tippy-bundle.umd.min.js')); ?>"></script>

<?php echo $__env->yieldContent('js'); ?>

<!-- Custom-->
<script src="<?php echo e(URL::asset('js/custom.js')); ?>"></script>

<script>
    tippy('[data-tippy-content]', {
        animation: 'scale-extreme',
        theme: 'material',
    });
</script>
<?php /**PATH E:\workspace\openaidavinci-1.0\resources\views/layouts/footer-frontend.blade.php ENDPATH**/ ?>