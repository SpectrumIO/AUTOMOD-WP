<div class="automod">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="automod--box">
                    <div class="automod--box-title">
                        <h1>Spectrum AutoMod</h1>
                    </div>
                    <div class="automod--box-content">
                        Analytics will appear here.
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="automod--box">
                    <div class="automod--box-title">
                        <h2>System Info</h2>
                    </div>
                    <div class="automod--box-content">
                        <p><i class="automod--green-pip"></i> You're all set!</p>
                        <label>Your API Key</label>
                        <input
                                type="text"
                                disabled
                                value="<?php print $api_key; ?>"
                                class="automod--api-key form-control"
                        />
                        <form action="<?php print esc_url(Automod_Admin::get_page_url()) ?>" method="POST" class="automod--home-disconnect">
                            <input type="hidden" name="action" value="disconnect">
                            <button class="btn btn-secondary">
                                Disconnect Account
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>