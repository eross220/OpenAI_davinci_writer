

<?php $__env->startSection('content'); ?>
<div class="container vertical-center">
    <div class="row justify-content-md-center">
        <div class="col-md-6 col-sm-12">   
            <div class="install-path text-center mt-9 mb-5">
                <span class="btn mr-2"><i class="fa-brands fa-instalod"></i></span>
                <span class="btn mr-2"><i class="fa-solid fa-ballot-check"></i></span>
                <span class="btn mr-2"><i class="fa-solid fa-file-check"></i></span>	
                <span class="btn mr-2"><i class="fa fa-database"></i></span>
                <span class="btn mr-2"><i class="fa-solid fa-file-certificate"></i></span>
                <span class="btn mr-2 active"><i class="fa-solid fa-shield-check"></i></span>
            </div>
            <div class="card overflow-hidden border-0 special-shadow mt-5 mb-5">	
                						
                <div class="card-body mt-7">                                                  

                    <h3 class="text-center font-weight-bold mb-8"><?php echo e(__('Installation Complete')); ?></h3>

                    <?php if($message = Session::get('success')): ?>
                        <div class="alert alert-login alert-success"> 
                            <button type="button" class="close" data-dismiss="alert">×</button>
                            <strong><i class="fa fa-check-circle"></i> <?php echo e($message); ?></strong>
                        </div>
                        <?php endif; ?>

                        <?php if($message = Session::get('error')): ?>
                        <div class="alert alert-login alert-danger">
                            <button type="button" class="close" data-dismiss="alert">×</button>
                            <strong><i class="fa fa-exclamation-triangle"></i> <?php echo e($message); ?></strong>
                        </div>
                    <?php endif; ?>
                    
                    <div id="install-wrapper" class="text-center pb-7">
                        <span><i class="fa-solid fa-shield-check"></i></span>
                        <?php if($activated): ?>
                            <p class="text-success"><?php echo e(__('Application Successfully Activated')); ?>!</p>
                        <?php else: ?> 
                            <p class="text-danger"><?php echo e(__('Application was NOT Activated')); ?>!</p>
                        <?php endif; ?>

                        <?php if($createDefaultAdmin): ?>
                            <p><?php echo e(__('You can Login with following default admin credentials')); ?>:</p>
                            <ul>
                                <li><?php echo e(__('Username')); ?>: <strong>admin@example.com</strong></li>
                                <li><?php echo e(__('Password')); ?>: <strong>admin12345</strong></li>
                            </ul>
                            <br>
                        <?php endif; ?>
                        
                    </div>

       
            </div>

            

        </div>  
        <div class="form-group mb-0 text-center">                        
            <a href="<?php echo e(url('/')); ?>"  class="btn btn-primary pr-5 pl-5"><?php echo e(__('Finish Installation')); ?></a>                                               
        </div>
                  
        </div>
         
    </div>
    <footer class="footer" id="install-footer">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-12 col-sm-12 fs-10 text-muted text-center">
                    <?php echo e(__('Copyright')); ?> © <?php echo e(date("Y")); ?> <a href="https://codecanyon.net/user/berkine/portfolio" target="_blank"><?php echo e(config('app.name')); ?></a>. <?php echo e(__('All rights reserved')); ?>

                </div>
            </div>
        </div>
    </footer> 
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.auth', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\workspace\openaidavinci-1.0\resources\views/install/complete.blade.php ENDPATH**/ ?>