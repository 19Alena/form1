<div>
    <?= $data[0]; ?>
</div>
<br>
<div>
    <form action="#destroy" method="post">
        <input type="hidden" name="destroy" value="true">
        <input type="submit" value="Destroy session" class="button save-button">
    </form>
</div>
