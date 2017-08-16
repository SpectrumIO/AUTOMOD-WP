<div class="container-fluid automod">
    <div class="row">
        <div class="col-md-12">
            <div class="automod--box">
                <div class="automod--box-title">
                    <h1>Spectrum AutoMod</h1>
                </div>
                <div class="automod--box-content">
                    <div class="row">
                        <div class="vertical-center">
                            <div class="col-sm-6">
                                <p>
                                    Please sign in below using your Spectrum username
                                    and password.
                                </p>
                                <?php Sicm_Automod_Admin::render_notices() ?>
                                <form method="POST" action="<?php print esc_url(Sicm_Automod_Admin::get_page_url()) ?>">
                                    <label>E-mail</label>
                                    <input class="form-control" name="email" type="text" />
                                    <label>Password</label>
                                    <input class="form-control" name="password" type="password" />
                                    <input type="hidden" name="action" value="create-integration">
                                    <button class="btn btn-primary" type="submit">
                                        Go
                                    </button>
                                </form>
                            </div>
                            <div class="col-sm-6">
                                <div class="automod--home-centered">
                                    <div>
                                        <p>No Account?</p>
                                        <a
                                                href="https://app.getspectrum.io/signup"
                                                class="btn btn-primary"
                                                target="_blank"
                                        >
                                            Create One Free &raquo;
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>