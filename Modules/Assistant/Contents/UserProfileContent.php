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
<div class="profile-item row"><div class="plabel col-sm-2">Name</div><div class="col-sm-12"><input readonly class="form-control" value="<?php echo $user['name'] ?>" /></div></div>
<div class="profile-item row"><div class="plabel col-sm-2">Email</div><div class="col-sm-12"><input  readonly class="form-control" value="<?php echo $user['email'] ?>" /></div></div>
<div class="profile-item row"><div class="plabel col-sm-2">Phone</div><div class="col-sm-12"><input  readonly class="form-control" value="<?php echo $isAdmin ? $user['phone'] : $classifiedInfo ?>" /></div></div>
<div class="profile-item row"><div class="plabel col-sm-2">Gender</div><div class="col-sm-12"><input  readonly class="form-control" value="<?php echo $user['gender'] == 1 ? "Male" : "Female" ?>" /></div></div>
<div class="profile-item row"><div class="plabel col-sm-2">Address</div><div class="col-sm-12"><textarea  readonly class="form-control"><?php echo $isAdmin ? $user['address'] : $classifiedInfo ?></textarea></div></div>
<div class="profile-item row"><div class="plabel col-sm-2">City</div><div class="col-sm-12"><input  readonly class="form-control" value="<?php echo $isAdmin ? $user['city'] : $classifiedInfo ?>" /></div></div>
<div class="profile-item row"><div class="plabel col-sm-2">Country</div><div class="col-sm-12"><input  readonly class="form-control" value="<?php echo $isAdmin ? $user['country'] : $classifiedInfo ?>" /></div></div>
