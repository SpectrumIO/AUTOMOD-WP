<div id="automod-plugin-wrapper">
    <h1>Spectrum AutoMod</h1>
    <h2>
        Please enter your username and password below.
    </h2>
    <?php Automod_Admin::render_notices() ?>
    <form method="POST" action="<?php print esc_url(Automod_Admin::get_page_url()) ?>">
        <input name="email" type="text" placeholder="email" />
        <input name="password" type="password" placeholder="password"/>
        <input type="hidden" name="action" value="create-integration">
        <button type="submit">Go</button>
    </form>
</div>