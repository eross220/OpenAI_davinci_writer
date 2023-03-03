

<?php $__env->startSection('content'); ?>
<div class="container vertical-center">
    <div class="row justify-content-md-center">
        
                <div class="col-md-6 col-sm-12">   
                    <div class="install-path text-center mt-9 mb-5">
                        <span class="btn mr-2"><i class="fa-brands fa-instalod"></i></span>
                        <span class="btn mr-2"><i class="fa-solid fa-ballot-check"></i></span>
                        <span class="btn mr-2"><i class="fa-solid fa-file-check"></i></span>	
                        <span class="btn mr-2 active"><i class="fa fa-database"></i></span>
                        <span class="btn mr-2"><i class="fa-solid fa-file-certificate"></i></span>
                        <span class="btn mr-2"><i class="fa-solid fa-shield-check"></i></span>
                    </div>
                    <div class="card overflow-hidden border-0 special-shadow">	
                                                
                        <div class="card-body install-notification">                                                  

                            <h3 class="text-center font-weight-bold fs-16 mb-4 mt-3"><?php echo e(__('Database Configuration')); ?></h3>

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
                            
                            <form action="<?php echo e(route('install.database.store')); ?>" method="POST" enctype="multipart/form-data">
                                <?php echo csrf_field(); ?>

                                <div id="install-wrapper">
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6 col-sm-12">								
                                            <div class="input-box">								
                                                <h6><?php echo e(__('Hostname')); ?></h6>
                                                <div class="form-group">							    
                                                    <input type="text" class="form-control <?php $__errorArgs = ['hostname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="hostname" name="hostname" value="<?php echo e(old('hostname')); ?>" placeholder="Enter Database Hostname" autocomplete="off" required>
                                                    <?php $__errorArgs = ['hostname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                        <p class="text-danger"><?php echo e($errors->first('hostname')); ?></p>
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div> 
                                            </div>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-sm-12">								
                                            <div class="input-box">								
                                                <h6><?php echo e(__('Port')); ?></h6>
                                                <div class="form-group">							    
                                                    <input type="text" class="form-control <?php $__errorArgs = ['port'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="port" name="port" value="<?php echo e(old('port')); ?>" placeholder="Enter Database Port" autocomplete="off" required>
                                                    <?php $__errorArgs = ['port'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                        <p class="text-danger"><?php echo e($errors->first('port')); ?></p>
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div> 
                                            </div>
                                        </div>

                                        <div class="col-lg-12 col-md-12 col-sm-12">								
                                            <div class="input-box">								
                                                <h6><?php echo e(__('Database Name')); ?></h6>
                                                <div class="form-group">							    
                                                    <input type="text" class="form-control <?php $__errorArgs = ['database'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="database" name="database" value="<?php echo e(old('database')); ?>" placeholder="Enter Database Name" autocomplete="off" required>
                                                    <?php $__errorArgs = ['database'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                        <p class="text-danger"><?php echo e($errors->first('database')); ?></p>
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div> 
                                            </div>
                                        </div>

                                        <div class="col-lg-12 col-md-12 col-sm-12">								
                                            <div class="input-box">								
                                                <h6><?php echo e(__('DB User')); ?></h6>
                                                <div class="form-group">							    
                                                    <input type="text" class="form-control <?php $__errorArgs = ['user'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="user" name="user" value="<?php echo e(old('user')); ?>" placeholder="Enter Database User" autocomplete="off" required>
                                                    <?php $__errorArgs = ['user'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                        <p class="text-danger"><?php echo e($errors->first('user')); ?></p>
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div> 
                                            </div>
                                        </div>

                                        <div class="col-lg-12 col-md-12 col-sm-12">								
                                            <div class="input-box">								
                                                <h6><?php echo e(__('DB User Password')); ?></h6>
                                                <div class="form-group">							    
                                                    <input type="text" class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="password" name="password" value="<?php echo e(old('password')); ?>" placeholder="Enter Database User Password" autocomplete="off">
                                                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                        <p class="text-danger"><?php echo e($errors->first('password')); ?></p>
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div> 
                                            </div>
                                        </div>
                                    </div>
                                </div>

                    </div>                    

                </div>  
                <div class="form-group mb-0 text-center">                        
                    <button type="submit"  class="btn btn-primary pr-7 pl-7"><?php echo e(__('Next')); ?> <i class="fa fa-angle-double-right ml-1"></i></button>                                               
                </div>
            </form>    
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


<?php echo $__env->make('layouts.auth', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\workspace\openaidavinci-1.0\resources\views/install/database.blade.php ENDPATH**/ ?>