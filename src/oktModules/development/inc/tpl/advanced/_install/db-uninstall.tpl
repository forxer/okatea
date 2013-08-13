<?xml version="1.0" encoding="utf-8"?>

<database>

	<action id="drop_mod_##module_id##" label="Drop table %s" string="{{PREFIX}}mod_##module_id##">
		<test eq="eq" value="{{PREFIX}}mod_##module_id##" label="Table %s doesn't exists" type="wrn"
		string="{{PREFIX}}mod_##module_id##">SHOW TABLES LIKE '{{PREFIX}}mod_##module_id##'</test>
		DROP TABLE IF EXISTS `{{PREFIX}}mod_##module_id##`
	</action>

</database>
