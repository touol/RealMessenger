[[+internalKey:isDoctors:is=`1`:then=`Врач`:else=`Пациент`]] [[+fullname]] оставил Вам сообщение!
<pre>[[+text]]</pre>
<br/><br/>
<a href="[[~[[+internalKey:isDoctors:is=`1`:then=`500`:else=`502`]]?scheme=`full`&[[+internalKey:isDoctors:is=`1`:then=`doctor_id=[[+internalKey]]`:else=`user_id=[[+internalKey]]`]] ]]">
[[%ticket_email_view]]</a>