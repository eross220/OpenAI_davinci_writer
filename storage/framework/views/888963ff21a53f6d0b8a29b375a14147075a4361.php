

<?php $__env->startSection('content'); ?>
<div class="container vertical-center">
    <div class="row justify-content-md-center mb-8">
        <div class="col-md-6 col-sm-12">   
            <div class="install-path text-center mt-9 mb-5">
                <span class="btn mr-2"><i class="fa-brands fa-instalod"></i></span>
                <span class="btn mr-2"><i class="fa-solid fa-ballot-check"></i></span>
                <span class="btn mr-2 active"><i class="fa-solid fa-file-check"></i></span>	
                <span class="btn mr-2"><i class="fa fa-database"></i></span>
                <span class="btn mr-2"><i class="fa-solid fa-file-certificate"></i></span>
                <span class="btn mr-2"><i class="fa-solid fa-shield-check"></i></span>
            </div>
            <div class="card overflow-hidden border-0 special-shadow">	
                						
                <div class="card-body">                                                  

                    <h3 class="text-center font-weight-bold fs-16 mb-4 mt-3"><?php echo e(__('Persmissions')); ?></h3>

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
                    
                    <div id="install-wrapper">
                        <?php $__currentLoopData = $results['permissions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $files): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="list-group list-group-flush <?php echo e($loop->index == 0 ? 'mb-n3 mt-n3' : 'mt-3 mb-n3 pt-3'); ?>">
                                <div class="list-group-item px-0">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <span class="font-weight-medium"><?php echo e(__($type)); ?></span>
                                        </div>

                                        <div class="col-auto d-flex align-items-center">
                                        </div>
                                    </div>
                                </div>

                                <?php $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file => $writable): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="list-group-item px-0 text-muted">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <?php echo e($file); ?>

                                            </div>

                                            <div class="col-auto d-flex align-items-center">
                                                <span class="<?php echo e((__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')); ?>">775</span>

                                                <?php if($writable): ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="svg-success" viewBox="0 0 17.44 13.13"><g id="61594a51-9b02-49ff-a94c-05e8c8f16478" data-name="Layer 2"><g id="21687ac0-78b2-4ea5-8955-fcde8112835a" data-name="Layer 30"><path d="M16,0,5.72,10.28,1.44,6,0,7.44l5,5,.72.69.72-.69,11-11Z"/></g></g></svg>
                                                <?php else: ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="svg-error" viewBox="0 0 16 16"><path d="M14.8,0a1.2,1.2,0,0,1,.85,2.05L9.7,8l5.95,5.95a1.2,1.2,0,0,1-1.7,1.7L8,9.7,2.05,15.65a1.2,1.2,0,0,1-1.7-1.7L6.3,8,.35,2.05A1.2,1.2,0,1,1,2.05.35L8,6.3,13.95.35A1.2,1.2,0,0,1,14.8,0Z"/></svg>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

            </div>       

        </div> 
         
        <div class="form-group mb-0 text-center">                        
            <a href="<?php echo e(route('install.database')); ?>"  class="btn btn-primary pr-7 pl-7"><?php echo e(__('Next')); ?> <i class="fa fa-angle-double-right ml-1"></i></a>                                               
        </div>
                  
        </div>
         
    </div>

</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.auth', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\workspace\openaidavinci-1.0\resources\views/install/permissions.blade.php ENDPATH**/ ?>