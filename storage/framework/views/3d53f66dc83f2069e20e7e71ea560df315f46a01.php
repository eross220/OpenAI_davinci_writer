<?php if($message = Session::get('success')): ?>
<div class="alert alert-success"> 
    <strong><i class="fa fa-check-circle"></i> <?php echo e($message); ?></strong>
</div>
<?php endif; ?>

<?php if($message = Session::get('error')): ?>
<div class="alert alert-danger">
    <strong><i class="fa fa-exclamation-triangle"></i> <?php echo e($message); ?></strong>
</div>
<?php endif; ?>

<?php if($message = Session::get('warning')): ?>
<div class="alert alert-warning">
    <strong><i class="fa fa-exclamation-circle"></i> <?php echo e($message); ?></strong>
</div>
<?php endif; ?>

<?php if($message = Session::get('info')): ?>
<div class="alert alert-info">
    <strong><i class="fa fa-info-circle"></i> <?php echo e($message); ?></strong>
</div>
<?php endif; ?>

<?php if($errors->any()): ?>
<div class="alert alert-danger">
    <?php echo e(__('There were some errors, please clear them first')); ?>

</div>
<?php endif; ?><?php /**PATH E:\workspace\openaidavinci-1.0\resources\views/layouts/flash.blade.php ENDPATH**/ ?>