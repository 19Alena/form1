  <H1>Fill out the form</H1>
   <?php
   $err=$err1;
 foreach($err as $value)
    {
        echo "<div class=error>".$value."</div>";
    }
    ?>
<form action=index.php method=post>
    <div class=form>

    <?php
    $arrVar=$data;
    if (is_array($arrVar)){
    foreach($arrVar as $value)
    {?>
    <div class="input-section">
    <p class="input-title">

        <?=$value;?>
        :</p> <input type=text name=<?=$value." ";
        if (substr_count($value,"NUM")!==0) { echo 'onkeypress="numberInput()"';}?> required></div>
        <?php
    }?>
    <p class="description">*Please, enter only Number in Number field and at least 3 characters in String field</p>
    <input type=submit value=submit class="button save-button"></div>
    <?php }
