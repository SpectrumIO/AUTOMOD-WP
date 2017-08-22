<script>
    window.analyticsData = JSON.parse('<?php print json_encode($analytics_data); ?>');
</script>
<div class="container-fluid automod">
    <div class="row">
        <div class="col-md-12">
            <div class="automod--box">
                <div class="automod--box-title">
                    <h1>Spectrum Intelligent Moderation</h1>
                </div>
                <div class="automod--box-content">
                    <div id="analytics-container">
                        No analytics yet! Check back later.
                    </div>
                    <div id="legend">
                        <i></i>
                        Number of Comments Checked
                    </div>
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
                    <form action="<?php print esc_url(Sicm_Automod_Admin::get_page_url()) ?>" method="POST"
                          class="automod--home-disconnect">
                        <input type="hidden" name="action" value="disconnect">
                        <button class="btn btn-secondary">
                            Disconnect Account
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="automod--box">
                <div class="automod--box-title">
                    <h2>Actions</h2>
                </div>
                <div class="automod--box-content">
                    <p>
                        <a class="btn btn-primary" href="https://app.getspectrum.io">
                            Visit Spectrum Dashboard &raquo;
                        </a>
                    </p>
                    <p>
                        <a class="btn btn-secondary" href="https://help.getspectrum.io">
                            Visit Help Center &raquo;
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>