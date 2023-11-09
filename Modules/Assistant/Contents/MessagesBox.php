<?php use Api\Mt;?>
        <div id="messagesBox" class="col-md-9 col-lg-10 col-12 border-left">
            <div class="table-responsive mailbox-messages">
                <div id="messagesList"
                     class="magicDiv messagesList mt-3"
                     data-magicDiv-type="table"
                     data-magicDiv-paginate="true"
                     data-magicDiv-before="onBeforeMessagesRendered()"
                     data-magicDiv-after="onMessagesRendered()"
                     data-magicDiv-source="<?php echo Mt::$appRelDir ?>/Helpers/FetchMessages"
                     data-magicDiv-toolbar="#mdivToolbar"
                     data-magicDiv-numRows="2">
                    <table class="d-none">
                        <tbody class="magicDivTemplate">
                        <tr><td class='mailbox-check pl-2'><input type='checkbox' value='' id='mail-check{id}'></td>
                        <td class='mailbox-star'><a href='#'><i class='fas fa-star text-warning'></i></a></td>
                        <td class='mailbox-name'><a href='users/{peer}'>{peer}</a></td>
                        <td class='mailbox-subject'><b>{subject}</b> - {message}</td>
                        <td class='mailbox-attachment'></td>
                        <td class='mailbox-date'><a class='mt-msg-time' data-utc-time='{date}'>{date}</a></td>
                        </tr>
                        </tbody>
                    </table>

                    <div class="magicDivNoShow">
                        <h3 class="text-center">No messages found in this category</h3>
                    </div>
                </div>

            </div>
            </div>
