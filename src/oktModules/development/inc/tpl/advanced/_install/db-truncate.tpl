<?xml version="1.0" encoding="UTF-8"?>
<database>

	<action id="truncate_mod_##module_id##" label="Truncate table %s" string="{{PREFIX}}mod_##module_id##">
		<test eq="eq" value="{{PREFIX}}mod_##module_id##" label="Table %s doesn't exists" type="wrn"
		string="{{PREFIX}}mod_##module_id##">SHOW TABLES LIKE '{{PREFIX}}mod_##module_id##'</test>
		TRUNCATE TABLE `{{PREFIX}}mod_##module_id##`
	</action>

</database>
