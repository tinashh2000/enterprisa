<?php
use Api\Mt;
?>
<style>
.profile-item div:first-child{
    font-weight: bold;
    /*text-align: right;*/
}

.profile-item div:first-child:after {
    content: ":";
}

    .profile-item div:nth-child(2) {
        color: blue;
    }

    .profile-item {
        margin-bottom: 0.3em;
    }
</style>
<?php
$classifiedInfo = "Not available";
?>
<div class="row">
<div class="col-3 col-sm-2"><img class="img-thumbnail" width="100%" src="<?php echo Mt::$appRelDir?>/people/<?php echo $person['id'] ?>/pic" /></div>
    <div class="col-9 col-sm-10">
        <div class="col-12 p-0 m-0">
            <div class="plabel">Name</div>
        <input readonly class="form-control" value="<?php echo $person['name'] ?>" /></div>
        <div class="col-12 p-0 m-0">
<div class="plabel">Email</div>
    <input  readonly class="form-control" value="<?php echo $person['email'] ?>" />
    </div>
    </div>
</div>

<div class="profile-item row"><div class="plabel col-sm-2">Phone</div><div class="col-sm-12"><input  readonly class="form-control" value="<?php echo $isAdmin ? $person['phone'] : $classifiedInfo ?>" /></div></div>
<div class="profile-item row"><div class="plabel col-sm-2">Gender</div><div class="col-sm-12"><input  readonly class="form-control" value="<?php echo $person['gender'] == 1 ? "Male" : "Female" ?>" /></div></div>
<div class="profile-item row"><div class="plabel col-sm-2">Address</div><div class="col-sm-12"><textarea  readonly class="form-control"><?php echo $isAdmin ? $person['address'] : $classifiedInfo ?></textarea></div></div>
<div class="profile-item row"><div class="plabel col-sm-2">City</div><div class="col-sm-12"><input  readonly class="form-control" value="<?php echo $isAdmin ? $person['city'] : $classifiedInfo ?>" /></div></div>
<div class="profile-item row"><div class="plabel col-sm-2">Country</div><div class="col-sm-12"><input  readonly class="form-control" value="<?php echo $isAdmin ? $person['country'] : $classifiedInfo ?>" /></div></div>
