<div class="automod">
    <div class="container metabox-holder">
        <div class="row">
            <div class="col-12">
                <div class="automod--body">
                    <h1>Spectrum AutoMod</h1>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <div class="postbox-container">
                    <div class="postbox">
                        <h2 class="hndle">
                            Account Info
                        </h2>
                        <div class="inside">
                            <p>Your account is currently active.</p>
                            <p>Your API Key:</p>
                            <p>
                                <input
                                        type="text"
                                        disabled
                                        value="<?php print $api_key; ?>"
                                        class="automod--api-key"
                                />
                            </p>
                            <form action="">
                                <button class="button button-secondary">
                                    Disconnect Account
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="postbox-container">
                    <div class="postbox">
                        <h2 class="hndle">
                            Subscription Info
                        </h2>
                        <div class="inside">
                            <p>You're currently on the STARTER plan.</p>
                            <a href="#" class="button button-primary">
                                Upgrade &raquo;
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>