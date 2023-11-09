<?php

use Helpers\HtmlHelper;
use Api\Users\CurrentUser;

?>
<form id="newEventForm" onsubmit="return false" method="post">

            <input name="r" type="hidden" value="create"/>
            <input name="id" type="hidden" value="0"/>
            <input name="lastModifiedDate" type="hidden" value="" />
            <input name="startDate" type="hidden" value="0"/>
            <input name="endDate" type="hidden" value="0"/>
            <input name="flags" type="hidden" value="0"/>

            <div class="row">
                <div class="col-12">
                    <label>Name</label>
                    <div class="form-group input-group">
                        <input type="text" name='name' class="form-control" placeholder="Name">
                    </div>
                </div>
            </div>

            <div class='row'>
                <div class="col-12 col-sm-3">
                    <label class="form-label">Start Date</label>
                    <div class="form-group input-group">
                        <input type="date" class="form-control" id='startDateC'>
                    </div>
                </div>

                <div class="col-12 col-sm-3">
                    <label class="form-label">Start Time</label>
                    <div class="form-group input-group">
                        <input type="time" class="form-control" id='startTimeC' value="06:00">
                    </div>
                </div>

                <div class="col-12 col-sm-3">
                    <label class="form-label">End Date</label>
                    <div class="form-group input-group">
                        <input type="date" class="form-control" id='endDateC'>
                    </div>
                </div>

                <div class="col-12 col-sm-3">
                    <label>End Time</label>
                    <div class="form-group input-group">
                        <input type="text" class="form-control" id='endTimeC' value="22:00">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 col-sm-12 ">
                    <label>Visibility</label>
                    <div class="form-group input-group">
                        <select class="select2" name="visibility" multiple required>
                            <option value="*" selected>Everyone</option>
                            <option value="<?php echo CurrentUser::getUsername() ?>">Me Only</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <label>Venue</label>
                    <div class="form-group input-group">
                        <input type="text" name='venue' class="form-control" placeholder="Venue" required/>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <label>Venue Location (GPS)</label>
                    <div class="form-group input-group">
                        <input type="text" name='location' class="form-control" placeholder="Location"/>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <label>Description</label>
                    <div class="form-group input-group">
                                <textarea type="text" name='description' class="form-control"
                                          placeholder="Description"></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <label>Message</label>
                    <div class="form-group input-group">
                                <textarea type="text" id="message" name="message" class="form-control  rich-text"
                                          placeholder="Message"></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <label>Notes / Minutes</label>
                    <div class="form-group input-group">
                                <textarea type="text" id="notes" name="notes" class="form-control  rich-text"
                                          placeholder="Notes"></textarea>
                    </div></div></div></form>